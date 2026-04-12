<?php
session_start();
require_once '../classes/Database.php';
require_once '../classes/Product.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

$db = Database::connect();
$productObj = new Product($db);

$id = intval($_GET['id']);
$product = $db->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = $_POST['name'];
    $description = $_POST['description'];
    $price       = floatval($_POST['price']);
    $skintype    = $_POST['skintype'];
    $concern     = $_POST['concern'];
    $category    = $_POST['category'];
    $brand       = $_POST['brand'];
    $tags        = isset($_POST['tags']) ? implode(',', $_POST['tags']) : '';
    $image       = $product['image'];

    // Validation: Check product name and brand for invalid characters
    $errors = [];

    if (!preg_match('/^[a-zA-Z0-9\s\-]+$/', $name)) {
        $errors[] = "Product name can only contain letters, numbers, spaces, and hyphens.";
    }

    if (!preg_match('/^[a-zA-Z0-9\s\-]+$/', $brand)) {
        $errors[] = "Brand name can only contain letters, numbers, spaces, and hyphens.";
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: edit_product.php?id=$id");
        exit();
    }

    // 🔍 Check for duplicate product name (excluding current product)
    $checkStmt = $db->prepare("SELECT id FROM products WHERE name = ? AND id != ?");
    $checkStmt->bind_param("si", $name, $id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows > 0) {
        $_SESSION['error'] = "A product with this name already exists!";
        header("Location: edit_product.php?id=$id");
        exit();
    }
    $checkStmt->close();

    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "../uploads/";
        $new_image = basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $new_image;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $new_image;
        }
    }

    $stmt = $db->prepare("UPDATE products 
        SET name=?, description=?, price=?, skintype=?, concern=?, tags=?, category=?, brand=?, image=? 
        WHERE id=?");
    $stmt->bind_param("ssdssssssi", $name, $description, $price, $skintype, $concern, $tags, $category, $brand, $image, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: view_products.php?msg=Product updated successfully");
    exit();
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <style>
        body { font-family: "Poppins", sans-serif; background-color: #f0f6ff; padding: 40px; color: #374151; }
        h1 { color: #3b82f6; margin-bottom: 20px; text-align: center; }
        .back-link { text-align: right; margin-bottom: 20px; }
        .back-link a { font-weight: 600; color: #3b82f6; text-decoration: none; font-size: 1rem; }
        .back-link a:hover { text-decoration: underline; color: #2563eb; }
        .error-msg { background-color: #fee; color: #c00; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c00; }
        form { background-color: #eaf4ff; padding: 30px; border-radius: 20px; box-shadow: 0 4px 15px rgba(100, 149, 237, 0.1); display: grid; gap: 15px; max-width: 600px; margin: auto; }
        form label { font-weight: 500; color: #374151; }
        form input, form textarea, form select { padding: 10px; border: 1.5px solid #cfe9ff; border-radius: 8px; font-size: 0.95rem; background-color: #fff; color: #374151; }
        form input:focus, form textarea:focus, form select:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 3px rgba(100, 149, 237, 0.1); }
        form button { background-color: #3b82f6; color: #fff; border: none; padding: 12px 24px; border-radius: 25px; font-size: 1rem; cursor: pointer; font-weight: bold; transition: all 0.3s ease; justify-self: start; }
        form button:hover { background-color: #2563eb; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(100, 149, 237, 0.3); }
        .preview-img { max-width: 100px; border-radius: 8px; box-shadow: 0 2px 6px rgba(100, 149, 237, 0.1); }
    </style>
</head>
<body>

<h1>Edit Product</h1>
<div class="back-link">
    <a href="view_products.php">← Back to Products</a>
</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error-msg">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <label>Product Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" pattern="[a-zA-Z0-9\s\-]+" title="Only letters, numbers, spaces, and hyphens allowed" required>

    <label>Description</label>
    <textarea name="description" rows="4" required><?= htmlspecialchars($product['description']) ?></textarea>

    <label>Price</label>
    <input type="text" name="price" value="<?= htmlspecialchars($product['price']) ?>" required>

    <label>Skin Type</label>
    <select name="skintype" required>
        <option value="oily" <?= $product['skintype'] === 'oily' ? 'selected' : '' ?>>Oily</option>
        <option value="dry" <?= $product['skintype'] === 'dry' ? 'selected' : '' ?>>Dry</option>
        <option value="combination" <?= $product['skintype'] === 'combination' ? 'selected' : '' ?>>Combination</option>
        <option value="sensitive" <?= $product['skintype'] === 'sensitive' ? 'selected' : '' ?>>Sensitive</option>
        <option value="normal" <?= $product['skintype'] === 'normal' ? 'selected' : '' ?>>Normal</option>
    </select>

    <label>Concern</label>
    <select name="concern" required>
        <option value="acne" <?= $product['concern'] === 'acne' ? 'selected' : '' ?>>Acne</option>
        <option value="aging" <?= $product['concern'] === 'aging' ? 'selected' : '' ?>>Aging</option>
        <option value="pigmentation" <?= $product['concern'] === 'pigmentation' ? 'selected' : '' ?>>Pigmentation</option>
        <option value="sensitivity" <?= $product['concern'] === 'sensitivity' ? 'selected' : '' ?>>Sensitivity</option>
    </select>

    <label>Tags</label>
    <?php $tags = explode(',', $product['tags']); ?>
    <label><input type="checkbox" name="tags[]" value="SPF" <?= in_array('SPF',$tags)?'checked':'' ?>> SPF</label>
    <label><input type="checkbox" name="tags[]" value="Retinol" <?= in_array('Retinol',$tags)?'checked':'' ?>> Retinol</label>
    <label><input type="checkbox" name="tags[]" value="Vegan" <?= in_array('Vegan',$tags)?'checked':'' ?>> Vegan</label>
    <label><input type="checkbox" name="tags[]" value="Fragrance-Free" <?= in_array('Fragrance-Free',$tags)?'checked':'' ?>> Fragrance-Free</label>

    <label>Category</label>
    <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" required>

    <label>Brand</label>
    <input type="text" name="brand" value="<?= htmlspecialchars($product['brand']) ?>" pattern="[a-zA-Z0-9\s\-]+" title="Only letters, numbers, spaces, and hyphens allowed" required>

    <label>Current Image</label>
    <img src="../uploads/<?= $product['image'] ?>" class="preview-img">

    <label>Upload New Image</label>
    <input type="file" name="image">

    <button type="submit">Update Product</button>
</form>

</body>
</html>
