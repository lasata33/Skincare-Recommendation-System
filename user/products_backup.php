<?php
require_once "../classes/Database.php";
require_once "../classes/User.php";
require_once "../classes/Product.php";

$db = Database::connect();
$userObj = new User($db);
$productObj = new Product($db);

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "<p>Please log in to view products.</p>";
    exit;
}

$user_skintype = $userObj->getSkinType($user_id);
if (!$user_skintype) {
    echo "<p>Skin type not found. Please take the quiz first.</p>";
    exit;
}

$products = $productObj->getBySkinType($user_skintype);
?>



<div class="products-container">
    <h1>🛍 Recommended Products for <?php echo htmlspecialchars($user_skintype); ?> Skin</h1>
    
    <?php if ($products->num_rows > 0): ?>
        <div class="product-grid">
            <?php while ($row = $products->fetch_assoc()): ?>
                <div class="product-card">
                    <img src="../uploads/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <p><?php echo htmlspecialchars($row['description']); ?></p>
                    <p class="price">Rs <?php echo number_format($row['price'], 2); ?></p>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No products available for your skin type yet.</p>
    <?php endif; ?>
</div>
