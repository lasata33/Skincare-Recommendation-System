<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../classes/IngredientAnalyzer.php";
require_once "../classes/FileHandler.php";

$db = Database::connect();
$analyzer = new IngredientAnalyzer($db);

$analysisResult = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    [$success, $result] = FileHandler::saveUploadedImage($_FILES['ingredientImage'], "C:\\xampp\\htdocs\\SP\\user\\uploads\\");

    if (!$success) {
        $analysisResult = "<p style='color:#d868a9;'>$result</p>";
    } else {
        $output = $analyzer->runPythonScript($result);
        $analysisResult = $analyzer->formatPythonOutput($output);
    }
}
?>

<h2>🔍 Ingredient Analyzer</h2>
<p>Upload a product label to check if the ingredients suit your skin type.</p>

<div class="upload-section">
    <form method="POST" enctype="multipart/form-data">
        <label for="ingredientImage">📷 Upload Ingredient Label:</label><br>
        <input type="file" name="ingredientImage" accept="image/*"><br>
        <button type="submit" class="ingredient-btn">Analyze Ingredients</button>
    </form>
</div>

<?php echo $analysisResult; ?>
</html>
