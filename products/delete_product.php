<?php
require_once "../classes/Database.php";
require_once "../admin/classes/AdminAuth.php";
require_once "classes/ProductManager.php";

AdminAuth::requireAdmin();

$db = Database::connect();
$productManager = new ProductManager($db);

$id = intval($_GET['id']);
$productManager->deleteProduct($id);

header("Location: view_products.php?msg=Product deleted successfully");
exit();
