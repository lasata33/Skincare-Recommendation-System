<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../classes/Database.php";
require_once "../classes/User.php";
require_once "../classes/IngredientAnalyzer.php";

$db = Database::connect();
$userObj = new User($db);
$analyzer = new IngredientAnalyzer($db);

$user_id = $_SESSION['user_id'] ?? null;
$userSkinType = $user_id ? strtolower(trim($userObj->getSkinType($user_id))) : 'normal';

$matched = [];
$ocrText = '';
$feasibilityPercent = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['ingredientImage']) && $_FILES['ingredientImage']['error'] === UPLOAD_ERR_OK) {
    $imagePath = $_FILES['ingredientImage']['tmp_name'];
    [$ocrText, $ingredients] = $analyzer->extractIngredients($imagePath);
    $matched = $analyzer->analyze($ingredients, $userSkinType);
    $feasibilityPercent = $analyzer->calculateFeasibility($matched);

    if ($user_id && $feasibilityPercent !== null) {
        $analyzer->saveResult($user_id, $ocrText, $userSkinType, $feasibilityPercent);
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Ingredient Analyzer</title>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: #fffafc;
            padding: 30px;
            color: #5b4b57;
        }
        h2 {
            color: #d868a9;
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
        .results-section h3 {
            color: #d46aa0;
        }
    </style>
</head>
<body>

<h2>🔍 Ingredient Analyzer</h2>
<p>Upload a product label to check if the ingredients suit your skin type (<strong><?php echo ucfirst($userSkinType); ?></strong>).</p>

<div class="upload-section">
    <form method="POST" enctype="multipart/form-data">
        <label for="ingredientImage">📷 Upload Ingredient Label:</label><br>
        <input type="file" name="ingredientImage" accept="image/*" onchange="previewImage(event)"><br>
        <img id="preview" style="max-width:100%; margin-top:10px; display:none;" />

        <div id="loadingMessage" style="display:none; font-weight:bold; color:#d868a9; margin-top:10px;">
          🧠 Scanning your product label... extracting ingredients...
        </div>

        <button type="submit">Analyze Ingredients</button>
    </form>
</div>

<?php if (!empty($matched)): ?>
<div class="results-section">
    <h3>🧾 Analysis Results</h3>

    <?php
    if ($feasibilityPercent !== null) {
        $emoji = $feasibilityPercent >= 90 ? "🎉" : ($feasibilityPercent >= 70 ? "😊" : ($feasibilityPercent >= 50 ? "😐" : "⚠️"));
        echo "<p style='font-weight:bold;color:#d868a9;font-size:18px;'>$emoji {$feasibilityPercent}% of the ingredients are suitable for your skin type (" . ucfirst($userSkinType) . ").</p>";
    }
    ?>

    <div style="margin-top:20px;">
      <label style="font-weight:bold;">Suitability Score:</label>
      <div style="background:#eee;border-radius:20px;overflow:hidden;height:20px;width:100%;">
        <div style="height:100%;width:<?php echo $feasibilityPercent; ?>%;background:#d868a9;"></div>
      </div>
    </div>

    <div style="display:flex;flex-wrap:wrap;gap:20px;margin-top:20px;">
    <?php foreach ($matched as $m): ?>
      <div style="flex:1 1 250px;background:#fff;border-radius:12px;padding:15px;box-shadow:0 4px 10px rgba(216,104,169,0.1);">
        <h4 style="color:#d868a9;"><?php echo htmlspecialchars($m['ingredient']); ?></h4>
        <p><strong>Type:</strong> <?php echo htmlspecialchars($m['type']); ?></p>
        <p><strong>Purpose:</strong> <?php echo htmlspecialchars($m['purpose']); ?></p>
        <p><strong>Suitability:</strong>
          <?php echo $m['suitable']
            ? ($m['isAll'] ? '✅ General match' : '✅ Tailored match')
            : '⚠️ Not ideal'; ?>
        </p>
      </div>
    <?php endforeach; ?>
    </div>
</div>
<?php elseif ($ocrText): ?>
<p>😢 No matching ingredients found in the dataset.</p>
<?php endif; ?>

<script>
function previewImage(event) {
  const reader = new FileReader();
  reader.onload = function() {
    const preview = document.getElementById('preview');
    preview.src = reader.result;
    preview.style.display = 'block';
  };
  reader.readAsDataURL(event.target.files[0]);
}

const form = document.querySelector("form");
const loadingMessage = document.getElementById("loadingMessage");

form.addEventListener("submit", () => {
  loadingMessage.style.display = "block";
});
</script>

</body>
</html>