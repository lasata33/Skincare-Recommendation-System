<?php
session_start();
require_once "../classes/Database.php";
require_once "../classes/Quiz.php";

$db = Database::connect();
$quiz = new Quiz($db);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$block = $_GET['block'] ?? '1'; // default to Block 1

// Map block to level
$levelMap = [
    '1' => 'basic',
    '2' => 'intermediate',
    '3' => 'advanced'
];

$level = $levelMap[$block] ?? 'basic';
?>

<div class="quiz-container">
    <h1>🧱 Block <?= $block ?> Quiz (<?= ucfirst($level) ?> Level)</h1>

    <?php
    $questions_result = $quiz->getQuestionsByLevel($level);
    if ($questions_result && $questions_result->num_rows > 0):
        $qNo = 1; // start numbering from 1
    ?>
        <form method="POST" action="submit_quiz_block.php">
            <input type="hidden" name="level" value="<?= $level ?>">
            <input type="hidden" name="block" value="<?= $block ?>">
            
            <?php while($row = $questions_result->fetch_assoc()): ?>
                <div class="quiz-question">
                    <p><strong>Q<?= $qNo ?>:</strong> <?= htmlspecialchars($row['question']) ?></p>

                    <?php
                    // 👉 Condition for different question types
                    if (stripos($row['question'], 'biggest skin concern') !== false) {
                        // Concern question → single choice (radio)
                        foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                            <label class="option">
                                <input type="radio" name="answer[<?= $row['id'] ?>]" value="<?= $opt ?>" required>
                                <?= htmlspecialchars($row['option_' . strtolower($opt)]) ?>
                            </label>
                        <?php endforeach;

                    } elseif (stripos($row['question'], 'product features') !== false) {
                        // Tags question → multi choice (checkbox)
                        foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                            <label class="option">
                                <input type="checkbox" name="answer[<?= $row['id'] ?>][]" value="<?= $opt ?>">
                                <?= htmlspecialchars($row['option_' . strtolower($opt)]) ?>
                            </label>
                        <?php endforeach;

                    } else {
                        // Normal skin type questions → single choice (radio)
                        foreach (['A', 'B', 'C', 'D'] as $opt): ?>
                            <label class="option">
                                <input type="radio" name="answer[<?= $row['id'] ?>]" value="<?= $opt ?>" required>
                                <?= htmlspecialchars($row['option_' . strtolower($opt)]) ?>
                            </label>
                        <?php endforeach;
                    }
                    ?>
                </div>
                <hr>
                <?php $qNo++; ?>
            <?php endwhile; ?>

            <button type="submit" class="submit-btn">Submit Block</button>
        </form>
    <?php else: ?>
        <p>No questions available for this block.</p>
    <?php endif; ?>
</div>

<!-- 🎨 Styles -->
<style>
.quiz-container {
    background: #fff0f5;
    padding: 20px;
    border-radius: 12px;
    max-width: 700px;
    margin: 30px auto;
    font-family: 'Poppins', sans-serif;
    color: #d868a9;
    box-shadow: 0 4px 10px rgba(216, 104, 169, 0.2);
}
.quiz-container h1 {
    text-align: center;
    margin-bottom: 20px;
    color: #c7579a;
}
.quiz-question {
    margin-bottom: 20px;
}
.quiz-question strong {
    color: #c7579a;
}
.option {
    display: block;
    margin: 6px 0;
    padding: 8px;
    background: #fbe4f0;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
}
.option:hover {
    background: #f2cde0;
}
.submit-btn {
    display: block;
    margin: 20px auto;
    background: #d868a9;
    color: #fff;
    padding: 12px 24px;
    border: none;
    border-radius: 20px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s ease;
}
.submit-btn:hover {
    background: #c7579a;
}
</style>
