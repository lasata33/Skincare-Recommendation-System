<?php
class Product {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    // 🔹 Featured product for home.php
    public static function getFeaturedBySkinType($skin_type) {
        $products = [
            'Dry' => 'CeraVe Moisturizing Cream 🧴',
            'Oily' => 'Neutrogena Oil-Free Cleanser 🧼',
            'Combination' => 'COSRX Balancing Toner 🌿',
            'Sensitive' => 'La Roche-Posay Toleriane 💧',
            'Normal' => 'Simple Hydrating Gel ✨'
        ];
        return $products[$skin_type] ?? 'Take the quiz to unlock product suggestions!';
    }

    // 🔹 Product list for products.php
    public function getBySkinType($skinType) {
        $stmt = $this->db->prepare("SELECT id, name, description, price, image FROM products WHERE skintype = ?");
        $stmt->bind_param("s", $skinType);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getAllProducts() {
        $stmt = $this->db->query("SELECT * FROM products ORDER BY id DESC");
        return $stmt->fetch_all(MYSQLI_ASSOC);
    }

    public function getFilteredProducts($userData) {
        $skintype = $userData['skintype'] ?? null;
        $stmt = $this->db->prepare("SELECT * FROM products WHERE skintype = ? ORDER BY id DESC");
        $stmt->bind_param("s", $skintype);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // 🔹 Smart Recommendations with scoring
    public function getRecommendations($skintype, $concern, $tags) {
        $result = $this->db->query("SELECT * FROM products ORDER BY id DESC");
        $products = [];
        $userTags = array_map('trim', explode(',', $tags));

        while ($row = $result->fetch_assoc()) {
            $score = 0;

            // Match skin type
            if (!empty($skintype) && stripos($row['skintype'], $skintype) !== false) {
                $score++;
            }

            // Match concern
            if (!empty($concern) && stripos($row['concern'], $concern) !== false) {
                $score++;
            }

            // Match tags (multi-check)
            foreach ($userTags as $tag) {
                if (!empty($tag) && stripos($row['tags'], $tag) !== false) {
                    $score++;
                }
            }

            $row['score'] = $score;
            $products[] = $row;
        }

        // Sort products by score (highest first)
        usort($products, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        return $products;
    }
}
?>
