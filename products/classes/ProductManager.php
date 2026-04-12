<?php
class ProductManager {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    public function getFilteredProducts($search = '', $skintype = '', $concern = '', $tag = '') {
    $sql = "SELECT * FROM products WHERE 1";
    
    if ($search !== '') {
        $search = $this->db->real_escape_string($search);
        $sql .= " AND name LIKE '%$search%'";
    }

    if ($skintype !== '') {
        $skintype = $this->db->real_escape_string($skintype);
        $sql .= " AND skintype = '$skintype'";
    }

    if ($concern !== '') {
        $concern = $this->db->real_escape_string($concern);
        $sql .= " AND concern = '$concern'";
    }

    if ($tag !== '') {
        $tag = $this->db->real_escape_string($tag);
        $sql .= " AND tags LIKE '%$tag%'";
    }

    $sql .= " ORDER BY id DESC";

    $result = $this->db->query($sql);
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    return $products;
}


    public function addProduct($data, $imagePath) {
        // 🔍 Check for duplicate product name
        $stmt = $this->db->prepare("SELECT id FROM products WHERE name = ?");
        $stmt->bind_param("s", $data['name']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $stmt->close();
            return "Product already exists";
        }
        $stmt->close();

        // ✅ Insert new product with all fields
        $stmt = $this->db->prepare("
            INSERT INTO products 
            (name, description, price, skintype, concern, tags, category, brand, image) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssdssssss",
            $data['name'],
            $data['description'],
            $data['price'],
            $data['skintype'],
            $data['concern'],
            $data['tags'],
            $data['category'],
            $data['brand'],
            $imagePath
        );
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public function deleteProduct($id) {
        // 🧼 Get image name
        $stmt = $this->db->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result) {
            $imagePath = "../uploads/" . $result['image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            // ❌ Delete product
            $stmt = $this->db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
        }
    }
}
?>
