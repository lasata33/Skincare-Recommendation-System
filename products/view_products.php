<?php
require_once "../classes/Database.php";
require_once "../admin/classes/AdminAuth.php";
require_once "classes/ProductManager.php";

AdminAuth::requireAdmin();

$db = Database::connect();
$productManager = new ProductManager($db);

$search   = $_GET['search']   ?? '';
$skintype = $_GET['skintype'] ?? '';
$concern  = $_GET['concern']  ?? '';
$tag      = $_GET['tag']      ?? '';

$products = $productManager->getFilteredProducts($search, $skintype, $concern, $tag); 
// 👉 If you want to extend ProductManager, you can also pass concern/tag filters
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Products</title>
    <link rel="stylesheet" href="view_products.css">
</head>
<body>
    
<h1>All Products</h1>

<!-- Filter form -->
<form method="GET" style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
    <!-- Search -->
    <input type="text" name="search" placeholder="Search by name..." 
           value="<?= htmlspecialchars($search) ?>" 
           style="padding: 10px; border-radius: 8px; border: 1.5px solid #cfe9ff; font-size: 0.95rem; background-color: #fff; color: #374151;">

    <!-- Skin type -->
    <select name="skintype" style="padding: 10px; border-radius: 8px; border: 1.5px solid #cfe9ff; font-size: 0.95rem; background-color: #fff; color: #374151;">
        <option value="">All Skin Types</option>
        <option value="dry" <?= $skintype==='dry'?'selected':'' ?>>Dry</option>
        <option value="oily" <?= $skintype==='oily'?'selected':'' ?>>Oily</option>
        <option value="combination" <?= $skintype==='combination'?'selected':'' ?>>Combination</option>
        <option value="sensitive" <?= $skintype==='sensitive'?'selected':'' ?>>Sensitive</option>
        <option value="normal" <?= $skintype==='normal'?'selected':'' ?>>Normal</option>
    </select>

    <!-- Concern -->
    <select name="concern" style="padding: 10px; border-radius: 8px; border: 1.5px solid #cfe9ff; font-size: 0.95rem; background-color: #fff; color: #374151;">
        <option value="">All Concerns</option>
        <option value="acne" <?= $concern==='acne'?'selected':'' ?>>Acne</option>
        <option value="aging" <?= $concern==='aging'?'selected':'' ?>>Aging</option>
        <option value="pigmentation" <?= $concern==='pigmentation'?'selected':'' ?>>Pigmentation</option>
        <option value="sensitivity" <?= $concern==='sensitivity'?'selected':'' ?>>Sensitivity</option>
    </select>

    <!-- Tags -->
    <select name="tag" style="padding: 10px; border-radius: 8px; border: 1.5px solid #cfe9ff; font-size: 0.95rem; background-color: #fff; color: #374151;">
        <option value="">All Tags</option>
        <option value="SPF" <?= $tag==='SPF'?'selected':'' ?>>SPF</option>
        <option value="Retinol" <?= $tag==='Retinol'?'selected':'' ?>>Retinol</option>
        <option value="Vegan" <?= $tag==='Vegan'?'selected':'' ?>>Vegan</option>
        <option value="Fragrance-Free" <?= $tag==='Fragrance-Free'?'selected':'' ?>>Fragrance-Free</option>
    </select>

    <button type="submit" style="background-color: #3b82f6; color: #fff; border: none; padding: 10px 20px; border-radius: 25px; font-size: 0.95rem; cursor: pointer; font-weight: bold; transition: all 0.3s ease;">Filter</button>
</form>

<!-- Back link -->
<div style="text-align: right; margin-bottom: 20px;">
    <a href="../admin/admindashboard.php" style="font-family: 'Poppins', sans-serif; font-weight: 600; color: #3b82f6; text-decoration: none; font-size: 1rem; padding: 8px 16px; border-radius: 8px; background-color: transparent; transition: all 0.3s ease;">← Back to Dashboard</a>
</div>

<a href="add_products.php">+ Add New Product</a>

<!-- Products table -->
<?php if (!empty($products)): ?>
<table>
    <tr>
        <th>ID</th>
        <th>Image</th>
        <th>Name</th>
        <th>Description</th>
        <th>Price</th>
        <th>Skin Type</th>
        <th>Concern</th>
        <th>Tags</th>
        <th>Category</th>
        <th>Brand</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($products as $row): ?>
    <tr>
        <td><?= htmlspecialchars($row['id']) ?></td>
        <td>
            <?php if (!empty($row['image'])): ?>
                <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" width="80">
            <?php else: ?>
                <span>No image</span>
            <?php endif; ?>
        </td>
        <td><?= htmlspecialchars($row['name']) ?></td>
        <td><?= htmlspecialchars($row['description']) ?></td>
        <td>Rs.<?= number_format($row['price'], 2) ?></td>
        <td><?= htmlspecialchars($row['skintype']) ?></td>
        <td><?= htmlspecialchars($row['concern']) ?></td>
        <td><?= htmlspecialchars($row['tags']) ?></td>
        <td><?= htmlspecialchars($row['category']) ?></td>
        <td><?= htmlspecialchars($row['brand']) ?></td>
        <td class="action-links">
            <a href="edit_product.php?id=<?= $row['id'] ?>">Edit</a>
            <a href="delete_product.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this product?')">Delete</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php else: ?>
    <p>No products found.</p>
<?php endif; ?>

</body>
</html>
