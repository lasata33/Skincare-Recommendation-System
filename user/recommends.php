<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../classes/Database.php";
require_once "../classes/User.php";
require_once "../classes/Quiz.php";
require_once "../classes/Product.php";

$db = Database::connect();
$userObj = new User($db);
$quizObj = new Quiz($db);
$productObj = new Product($db);

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "<p>Please log in to view this section.</p>";
    exit;
}

$userData = $userObj->getUserById($user_id);
$quizLevel = $userData['quiz_level_completed'] ?? null;
$user_skintype = $quizObj->getUserSkinType($user_id);
$user_concern  = $userData['concern'] ?? '';
$user_tags     = $userData['tags'] ?? '';

$progress = json_decode($userObj->getQuizProgress($user_id), true) ?? [];
$levelOrder = ['basic' => 1, 'intermediate' => 2, 'advanced' => 3];
$currentLevel = $quizLevel ?? 'basic';

// 🧠 Check for new questions in completed levels
$newQuizzes = [];
foreach ($levelOrder as $level => $blockNum) {
    $levelCompletedIndex = array_search($quizLevel, array_keys($levelOrder));
    $thisLevelIndex = array_search($level, array_keys($levelOrder));

    if ($thisLevelIndex !== false && $levelCompletedIndex !== false && $thisLevelIndex <= $levelCompletedIndex) {
        $answered = $progress[$level] ?? 0;
        $total = $quizObj->countQuestionsByLevel($level);
        if ($answered < $total) {
            $newQuizzes[] = [
                'level' => $level,
                'reblock' => $blockNum,
                'new' => $total - $answered
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Recommendations</title>
    <!-- ✨ STYLES -->
    <style>
    .quiz-banner {
        background: #fff0f5;
        padding: 12px 20px;
        margin: 20px auto;
        border-radius: 10px;
        font-weight: bold;
        color: #d868a9;
        max-width: 500px;
        text-align: center;
    }
    .retake-btn {
        display: inline-block;
        margin: 12px 8px;
        padding: 10px 20px;
        background-color: #d868a9;
        color: white;
        border-radius: 20px;
        text-decoration: none;
        font-weight: bold;
        transition: 0.3s ease;
    }
    .retake-btn:hover {
        background-color: #c7579a;
    }
    .quiz-retakes {
        margin-top: 20px;
        text-align: center;
    }
    .product-card {
        background: #fff;
        border-radius: 12px;
        padding: 15px;
        margin: 10px;
        box-shadow: 0 4px 10px rgba(100,149,237,0.1);
        text-align: center;
    }
    .product-card img {
        max-width: 120px;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    .match-score {
        font-weight: bold;
        color: #3b82f6;
    }
    .top-pick {
        border: 2px solid #d868a9;
        background: #fff5fa;
    }

    .retake-btn.locked {
        background-color: #e5e7eb; /* light grey */
        color: #9ca3af; /* darker grey text */
        cursor: not-allowed;
        text-decoration: none;
        border-radius: 20px;
        padding: 10px 20px;
        font-weight: bold;
        display: inline-block;
        margin: 12px 8px;
    }

    .routine-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.routine-step {
    flex: 1 1 200px;
    background: #fdf6f9;
    border: 2px dashed #d868a9;
}
.routine-step h3 {
    color: #d868a9;
    margin-bottom: 8px;
}

    </style>
</head>
<body>

<!-- 🧪 QUIZ SECTION -->
<div class="quiz-container">
    <?php if (!empty($newQuizzes)): ?>
        <?php foreach ($newQuizzes as $quiz): ?>
            <div class="quiz-banner">
                🆕 <?= $quiz['new'] ?> new questions added to the <strong><?= ucfirst($quiz['level']) ?></strong> quiz! 
                <a href="quiz.php?block=<?= $quiz['reblock'] ?>" class="retake-btn">Take them now</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if ($user_skintype): ?>
        <h1>📝 You have <?= htmlspecialchars($user_skintype) ?> skin type</h1>

        <?php if ($quizLevel === null): ?>
            <p>👋 Welcome! Take the quiz to discover your skin type 💖</p>
            <a href="quiz.php?block=1" class="retake-btn">Start Basic Quiz</a>
        <?php elseif ($quizLevel === 'basic'): ?>
            <p>✅ You’ve completed the <strong>Basic</strong> quiz block!</p>
            <a href="quiz.php?block=2" class="retake-btn">Take Intermediate Quiz Block</a>
        <?php elseif ($quizLevel === 'intermediate'): ?>
            <p>✅ You’ve completed the <strong>Intermediate</strong> quiz block!</p>
            <a href="quiz.php?block=3" class="retake-btn">Take Advanced Quiz Block</a>
        <?php elseif ($quizLevel === 'advanced'): ?>
            <p>🎉 You’ve completed all quiz blocks! Check your recommendations below 💖</p>
        <?php endif; ?>

        <!-- 🔁 Retake options -->
        <div class="quiz-retakes">
            <h2>🔁 Retake a Quiz Block</h2>

            <?php if ($quizLevel === 'basic' || $quizLevel === 'intermediate' || $quizLevel === 'advanced'): ?>
                <a href="quiz.php?block=1" class="retake-btn">Retake Basic Quiz</a>
            <?php else: ?>
                <span class="retake-btn locked">Retake Basic Quiz 🔒</span>
            <?php endif; ?>

            <?php if ($quizLevel === 'intermediate' || $quizLevel === 'advanced'): ?>
                <a href="quiz.php?block=2" class="retake-btn">Retake Intermediate Quiz</a>
            <?php else: ?>
                <span class="retake-btn locked">Retake Intermediate Quiz 🔒</span>
            <?php endif; ?>

            <?php if ($quizLevel === 'advanced'): ?>
                <a href="quiz.php?block=3" class="retake-btn">Retake Advanced Quiz</a>
            <?php else: ?>
                <span class="retake-btn locked">Retake Advanced Quiz 🔒</span>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <h1>👋 Welcome!</h1>
        <p>Take the quiz to discover your skin type 💖</p>
        <a href="quiz.php?block=1" class="retake-btn">Start Basic Quiz</a>
    <?php endif; ?>
</div>

<!-- 🛍 PRODUCT SECTION -->
<div class="products-container">
    <?php if ($quizLevel === null): ?>
        <h1>🧴 Explore All Products</h1>
        <p>👋 Take the quiz to unlock personalized recommendations 💖</p>
        <?php $products = $productObj->getAllProducts(); ?>
        <div class="product-grid">
            <?php foreach ($products as $row): ?>
                <div class="product-card">
                    <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p><?= htmlspecialchars($row['description']) ?></p>
                    <p class="price">Rs <?= number_format($row['price'], 2) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <h1>✨ Top Picks for You</h1>
        <?php 
        $products = $productObj->getRecommendations($user_skintype, $user_concern, $user_tags);
        $topPicks = array_slice($products, 0, 3); 
        ?>
        <div class="product-grid">
            <?php foreach ($topPicks as $row): ?>
                <div class="product-card top-pick">
                    <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                    <h3><?= htmlspecialchars($row['name']) ?></h3>
                    <p><?= htmlspecialchars($row['description']) ?></p>
                    <p class="price">Rs <?= number_format($row['price'], 2) ?></p>
                    <?php if (isset($row['score'])): ?>
                        <!-- <p class="match-score">🌟 Match Score: <?= htmlspecialchars($row['score']) ?></p> -->
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- 🧖 Personalized Routine -->
<h1>🧖 Your Personalized Skincare Routine</h1>
<p>Based on your quiz results, here’s a simple routine tailored for your <?= htmlspecialchars($user_skintype) ?> skin 💖</p>

<div class="routine-grid">
    <?php
    // Categories we want in the routine
    $routineCategories = ['cleanser', 'toner', 'serum', 'moisturizer', 'sunscreen'];

    foreach ($routineCategories as $cat) {
        $product = null;
        foreach ($products as $row) {
            if (strtolower($row['category']) === $cat) {
                $product = $row;
                break;
            }
        }

        if ($product): ?>
            <div class="product-card routine-step">
                <h3>✨ <?= ucfirst($cat) ?></h3>
                <img src="../uploads/<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                <p><strong><?= htmlspecialchars($product['name']) ?></strong></p>
                <p><?= htmlspecialchars($product['description']) ?></p>
                <p class="price">Rs <?= number_format($product['price'], 2) ?></p>
                <?php if (isset($product['score'])): ?>
                    <!-- <p class="match-score">🌟 Match Score: <?= htmlspecialchars($product['score']) ?></p> -->
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="product-card routine-step">
                <h3>✨ <?= ucfirst($cat) ?></h3>
                <p>No recommendation available yet 💭</p>
            </div>
        <?php endif;
    }
    ?>
</div>


<h1>🧴 More Products</h1>
<?php
// Collect routine product IDs so they don't repeat
$routineCategories = ['cleanser', 'toner', 'serum', 'moisturizer', 'sunscreen'];
$routineIds = [];

foreach ($routineCategories as $cat) {
    foreach ($products as $row) {
        if (strtolower($row['category']) === $cat) {
            $routineIds[] = $row['id']; // assumes 'id' exists
            break;
        }
    }
}

// Filter out routine products
$moreProducts = array_filter($products, function($row) use ($routineIds) {
    return !in_array($row['id'], $routineIds);
});

// Split into initial 4 + extra
$initialProducts = array_slice($moreProducts, 0, 4);
$extraProducts   = array_slice($moreProducts, 4);
?>

<div class="product-grid">
    <?php foreach ($initialProducts as $row): ?>
        <div class="product-card">
            <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
            <h3><?= htmlspecialchars($row['name']) ?></h3>
            <p><?= htmlspecialchars($row['description']) ?></p>
            <p class="price">Rs <?= number_format($row['price'], 2) ?></p>
        </div>
    <?php endforeach; ?>

  <!-- Hidden extra products -->
<div id="extra-products" style="display:none;">
    <?php foreach ($extraProducts as $row): ?>
        <div class="product-card">
            <img src="../uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
            <h3><?= htmlspecialchars($row['name']) ?></h3>
            <p><?= htmlspecialchars($row['description']) ?></p>
            <p class="price">Rs <?= number_format($row['price'], 2) ?></p>
        </div>
    <?php endforeach; ?>
</div>



<?php if (!empty($extraProducts)): ?>
    <div style="text-align:center; margin-top:15px;">
        <button id="see-more-btn" class="retake-btn">See More</button>
    </div>
<?php endif; ?>

<?php if (empty($moreProducts)): ?>
    <p>No personalized recommendations yet. Try retaking the quiz 💖</p>
<?php endif; ?>
    <?php endif; ?>
</div>

<!-- Keep the script inside <body> -->
<script>
document.getElementById('see-more-btn')?.addEventListener('click', function() {
    const extra = document.getElementById('extra-products');
    if (extra) {
        extra.style.display = 'contents'; // keeps same grid layout
        this.style.display = 'none';
    }
});
</script>



</body>
</html>