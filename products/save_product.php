<?php
require_once "../classes/Database.php";
require_once "../admin/classes/AdminAuth.php";
require_once "classes/ProductManager.php";

AdminAuth::requireAdmin();

$db = Database::connect();
$productManager = new ProductManager($db);

// Collect form data
$data = [
    'name'        => $_POST['name'] ?? '',
    'description' => $_POST['description'] ?? '',
    'price'       => $_POST['price'] ?? 0,
    'skintype'    => $_POST['skintype'] ?? '',
    'concern'     => $_POST['concern'] ?? '',
    'category'    => $_POST['category'] ?? '',
    'brand'       => $_POST['brand'] ?? '',
    'tags'        => isset($_POST['tags']) ? implode(',', $_POST['tags']) : ''
];

// Validation: Check product name and brand for invalid characters
$errors = [];

if (!preg_match('/^[a-zA-Z0-9\s\-]+$/', $data['name'])) {
    $errors[] = "Product name can only contain letters, numbers, spaces, and hyphens.";
}

if (!preg_match('/^[a-zA-Z0-9\s\-]+$/', $data['brand'])) {
    $errors[] = "Brand name can only contain letters, numbers, spaces, and hyphens.";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: add_products.php?msg=" . urlencode(implode(". ", $errors)));
    exit;
}

// Handle image upload
$imageName = $_FILES['image']['name'] ?? '';
$imageTmp  = $_FILES['image']['tmp_name'] ?? '';
$imagePath = '';

if (!empty($imageName) && !empty($imageTmp)) {
    $imagePath = basename($imageName);
    move_uploaded_file($imageTmp, "../uploads/" . $imagePath);
}

// Save product
$result = $productManager->addProduct($data, $imagePath);

if ($result === true) {
    header("Location: view_products.php?msg=Product added successfully");
} elseif ($result === "Product already exists") {
    header("Location: add_products.php?msg=Product already exists");
} else {
    header("Location: add_products.php?msg=Error adding product");
}
exit;
