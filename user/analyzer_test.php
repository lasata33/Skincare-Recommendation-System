<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ingredient Analyzer Test</title>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: #fffafc;
            padding: 30px;
            color: #5b4b57;
        }
        .upload-section {
            background: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(216,104,169,0.1);
            margin-bottom: 30px;
        }
        input[type="file"] {
            margin-top: 10px;
        }
        button {
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #ffd7e9;
            color: #d868a9;
            border: none;
            border-radius: 20px;
            font-weight: 500;
            cursor: pointer;
        }
        button:hover {
            background-color: #d868a9;
            color: #fff;
        }
        .result {
            margin-top: 20px;
            background: #fff6f9;
            padding: 15px;
            border-radius: 12px;
            color: #4a3d45;
        }
    </style>
</head>
<body>

<h2>🧪 Ingredient Analyzer Test</h2>

<div class="upload-section">
    <form method="POST" enctype="multipart/form-data">
        <label for="ingredientImage">📷 Upload Ingredient Label:</label><br>
        <input type="file" name="ingredientImage" accept="image/*"><br>
        <button type="submit">Run OCR</button>
    </form>
</div>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
}
?>


<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['ingredientImage']) || $_FILES['ingredientImage']['error'] !== UPLOAD_ERR_OK) {
        echo "<div class='result'>⚠️ No valid file uploaded. Please try again.</div>";
    } else {
        $imagePath = $_FILES['ingredientImage']['tmp_name'];
        $ocrText = shell_exec("tesseract " . escapeshellarg($imagePath) . " stdout");

        echo "<div class='result'>";
        echo "<h3>🧾 OCR Result:</h3>";
        echo "<pre>" . htmlspecialchars($ocrText) . "</pre>";
        echo "</div>";
    }
}
?>

</body>
</html>
