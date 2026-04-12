<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../classes/Database.php";
require_once "../classes/Quiz.php";

$db = Database::connect();
$quiz = new Quiz($db);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$current_skintype = $quiz->getUserSkinType($user_id);
$retake = isset($_GET['retake']) && $_GET['retake'] == 1;
$level = $_GET['level'] ?? 'basic'; // default to basic

$questions_result = $quiz->getQuestionsByLevel($level); // NEW method
?>

<div class="quiz-container">
    <?php if (!$retake && $current_skintype): ?>
        <h1>📝 Your Skin Type</h1>
        <p>You have <strong><?= htmlspecialchars($current_skintype) ?></strong> skin type.</p>
        <a href="quiz.php?retake=1" class="retake-btn">Retake Quiz</a>

    <?php elseif ($questions_result && $questions_result->num_rows > 0): ?>
        <h1>📝 <?= ucfirst($level) ?> Level Quiz</h1>
        <form method="POST" action="submit_quiz.php" class="quiz-form">
            <input type="hidden" name="level" value="<?= $level ?>">
            <?php while($row = $questions_result->fetch_assoc()): ?>
                <div class="quiz-question">
                    <p><strong>Q<?= $row['id'] ?>:</strong> <?= htmlspecialchars($row['question']) ?></p>
                    <label><input type="radio" name="answer[<?= $row['id'] ?>]" value="A" required> <?= htmlspecialchars($row['option_a']) ?></label><br>
                    <label><input type="radio" name="answer[<?= $row['id'] ?>]" value="B"> <?= htmlspecialchars($row['option_b']) ?></label><br>
                    <label><input type="radio" name="answer[<?= $row['id'] ?>]" value="C"> <?= htmlspecialchars($row['option_c']) ?></label><br>
                    <label><input type="radio" name="answer[<?= $row['id'] ?>]" value="D"> <?= htmlspecialchars($row['option_d']) ?></label>
                </div>
                <hr>
            <?php endwhile; ?>
            <button type="submit">Submit Quiz</button>
        </form>

    <?php else: ?>
        <p>No <?= $level ?> level quiz questions available at the moment.</p>
    <?php endif; ?>

    <?php if ($level === 'basic' && !$current_skintype): ?>
        <div style="text-align:center; margin-top:30px;">
            <p>🎉 Done with basic level? Want to try intermediate?</p>
            <a href="quiz.php?level=intermediate" class="retake-btn">Try Intermediate</a>
        </div>
    <?php elseif ($level === 'intermediate'): ?>
        <div style="text-align:center; margin-top:30px;">
            <p>💪 Done with intermediate? Ready for advanced?</p>
            <a href="quiz.php?level=advanced" class="retake-btn">Go Advanced</a>
        </div>
    <?php endif; ?>
</div>


<style>
.quiz-container { 
    padding: 30px;
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 0 15px rgba(0,0,0,0.1);
    max-width: 800px;
    margin: 20px auto;
}

.quiz-question { 
    margin-bottom: 25px;
    padding: 15px;
    background: #fff6fa;
    border-radius: 8px;
}

.quiz-question p {
    color: #333;
    font-size: 1.1em;
    margin-bottom: 15px;
}

.quiz-question label {
    display: block;
    padding: 10px;
    margin: 5px 0;
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 5px;
}

.quiz-question label:hover {
    background: #ffe6f3;
}

.quiz-question input[type="radio"] {
    margin-right: 10px;
}

.retake-btn {
    display: inline-block;
    background: #d868a9;
    color: white;
    padding: 12px 25px;
    border-radius: 25px;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: bold;
    box-shadow: 0 2px 5px rgba(216,104,169,0.2);
}

.retake-btn:hover { 
    background: #c7579a;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(216,104,169,0.3);
}

button[type="submit"] {
    background: #d868a9;
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 25px;
    font-size: 1.1em;
    cursor: pointer;
    transition: all 0.3s ease;
    display: block;
    margin: 30px auto 10px;
    font-weight: bold;
    box-shadow: 0 2px 5px rgba(216,104,169,0.2);
}

button[type="submit"]:hover {
    background: #c7579a;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(216,104,169,0.3);
}

hr {
    border: none;
    border-top: 2px solid #ffe6f3;
    margin: 20px 0;
}

h1 {
    color: #d868a9;
    text-align: center;
    margin-bottom: 30px;
}

strong {
    color: #d868a9;
}
</style>
