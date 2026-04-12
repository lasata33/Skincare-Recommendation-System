<?php
require_once "../classes/Database.php";
require_once "../admin/classes/AdminAuth.php";

AdminAuth::requireAdmin();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
    <link rel="stylesheet" href="add_products.css">
</head>
<body>

<form action="save_product.php" method="POST" enctype="multipart/form-data">
    <h1>Add New Product</h1>
    <?php if (isset($_GET['msg'])): ?>
        <div class="alert"><?= htmlspecialchars($_GET['msg']) ?></div>
    <?php endif; ?>

    <div style="text-align: right; margin-bottom: 20px;">
        <a href="../admin/admindashboard.php" style="
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            color: #3b82f6;
            text-decoration: none;
            font-size: 1rem;
            padding: 8px 16px;
            border-radius: 8px;
            background-color: transparent;
            transition: all 0.3s ease;
        ">← Back to Dashboard</a>
    </div>

    <!-- Basic product info -->
    <label>Product Name</label>
    <input type="text" name="name" pattern="[a-zA-Z0-9\s\-]+" title="Only letters, numbers, spaces, and hyphens allowed" required>

    <label>Description</label>
    <textarea name="description" required></textarea>

    <label>Price</label>
    <input type="number" name="price" step="0.01" required>

    <!-- Personalization fields -->
    <label>Skin Type</label>
    <select name="skintype" required>
        <option value="dry">Dry</option>
        <option value="oily">Oily</option>
        <option value="combination">Combination</option>
        <option value="sensitive">Sensitive</option>
        <option value="normal">Normal</option>
    </select>

    <label>Concern</label>
    <select name="concern" required>
        <option value="acne">Acne</option>
        <option value="aging">Aging</option>
        <option value="pigmentation">Pigmentation</option>
        <option value="sensitivity">Sensitivity</option>
    </select>

    <label>Tags (select multiple)</label>
    <div class="tags-box">
        <label><input type="checkbox" name="tags[]" value="SPF"> SPF</label>
        <label><input type="checkbox" name="tags[]" value="Retinol"> Retinol</label>
        <label><input type="checkbox" name="tags[]" value="Vegan"> Vegan</label>
        <label><input type="checkbox" name="tags[]" value="Fragrance-Free"> Fragrance-Free</label>
    </div>

    <label>Category</label>
    <select name="category" required>
        <option value="cleanser">Cleanser</option>
        <option value="serum">Serum</option>
        <option value="moisturizer">Moisturizer</option>
        <option value="sunscreen">Sunscreen</option>
        <option value="toner">Toner</option>
    </select>

    <label>Brand</label>
    <input type="text" name="brand" pattern="[a-zA-Z0-9\s\-]+" title="Only letters, numbers, spaces, and hyphens allowed" required>

    <!-- Image upload -->
    <label>Image</label>
    <input type="file" name="image" accept="image/*" required>

    <button type="submit">Add Product</button>
</form>

<script>
document.querySelector("form").addEventListener("submit", function(e) {
    const name = document.querySelector("input[name='name']").value.trim();
    const description = document.querySelector("textarea[name='description']").value.trim();
    const price = parseFloat(document.querySelector("input[name='price']").value);
    const image = document.querySelector("input[name='image']").files[0];

    if (name.length < 3) {
        alert("Product name must be at least 3 characters.");
        e.preventDefault();
        return;
    }

    if (description.length < 10) {
        alert("Description must be at least 10 characters.");
        e.preventDefault();
        return;
    }

    if (isNaN(price) || price <= 0) {
        alert("Please enter a valid price greater than 0.");
        e.preventDefault();
        return;
    }

    if (!image) {
        alert("Please upload an image.");
        e.preventDefault();
        return;
    }

    const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
    if (!allowedTypes.includes(image.type)) {
        alert("Only JPG, PNG, or WEBP images are allowed.");
        e.preventDefault();
        return;
    }

    if (image.size > 2 * 1024 * 1024) {
        alert("Image size must be under 2MB.");
        e.preventDefault();
        return;
    }
});
</script>

</body>
</html>
