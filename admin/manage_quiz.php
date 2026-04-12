<?php
session_start();
require_once "../classes/Database.php";
require_once "classes/AdminAuth.php";
require_once "classes/QuizManager.php";

AdminAuth::requireAdmin();

$db = Database::connect();
$quizManager = new QuizManager($db);

$error = $success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'question' => $_POST['question'] ?? '',
        'option_a' => $_POST['option_a'] ?? '',
        'option_b' => $_POST['option_b'] ?? '',
        'option_c' => $_POST['option_c'] ?? '',
        'option_d' => $_POST['option_d'] ?? '',
        'map_a' => $_POST['map_a'] ?? '',
        'map_b' => $_POST['map_b'] ?? '',
        'map_c' => $_POST['map_c'] ?? '',
        'map_d' => $_POST['map_d'] ?? '',
        'level' => $_POST['level'] ?? 'basic'
    ];

    // Validation: required fields
    if (in_array('', [$data['question'], $data['option_a'], $data['option_b'], $data['option_c'], $data['option_d']])) {
        $error = "All fields are required.";
    } else {
        // Validation: check for special characters in question and options
        $fieldsToCheck = ['question' => $data['question'], 'option_a' => $data['option_a'], 'option_b' => $data['option_b'], 'option_c' => $data['option_c'], 'option_d' => $data['option_d']];
        
        foreach ($fieldsToCheck as $fieldName => $fieldValue) {
            if (!preg_match('/^[a-zA-Z0-9\s\?\!\,\.\-\']+$/', $fieldValue)) {
                $error = ucfirst(str_replace('_', ' ', $fieldName)) . " can only contain letters, numbers, spaces, and basic punctuation (?, !, ,, ., -, ').";
                break;
            }
            // Validation: must contain at least one letter
            if (!preg_match('/[a-zA-Z]/', $fieldValue)) {
                $error = ucfirst(str_replace('_', ' ', $fieldName)) . " must contain at least one letter.";
                break;
            }
        }
        
        // Validation: check mapping fields (skin types - letters only)
        if (!$error) {
            $mapFields = ['map_a' => $data['map_a'], 'map_b' => $data['map_b'], 'map_c' => $data['map_c'], 'map_d' => $data['map_d']];
            foreach ($mapFields as $mapName => $mapValue) {
                if (!empty($mapValue) && !preg_match('/^[a-zA-Z\s]+$/', $mapValue)) {
                    $error = "Skin type mappings can only contain letters and spaces.";
                    break;
                }
            }
        }
        
        if (!$error) {
            // Validation: duplicate question check
            if ($quizManager->questionExists($data['question'])) {
                $error = "Quiz question already exists.";
            } else {
                $result = $quizManager->addQuestion($data);
                if ($result === true) {
                    $success = "Question added successfully!";
                } else {
                    $error = "Database error: " . $result;
                }
            }
        }
    }
}

$questions = $quizManager->getAllQuestions();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Quiz</title>
    <link rel="stylesheet" href="manage_quiz.css">
</head>
<body>
<h1>Manage Quiz</h1>

<form method="POST" action="upload_quiz_batch.php?level=basic">
    <button type="submit" class="add-btn">➕ Add 5 Basic Questions</button>
</form>
<form method="POST" action="upload_quiz_batch.php?level=intermediate">
    <button type="submit" class="add-btn">➕ Add 5 Intermediate Questions</button>
</form>
<form method="POST" action="upload_quiz_batch.php?level=advanced">
    <button type="submit" class="add-btn">➕ Add 5 Advanced Questions</button>
</form>

<div style="text-align: right; margin-bottom: 20px;">
    <a href="admindashboard.php" style="
        font-family: 'Poppins', sans-serif;
        font-weight: 600;
        color: #2563eb;
        text-decoration: none;
        font-size: 1rem;
        padding: 8px 16px;
        border-radius: 8px;
        background-color: transparent;
        transition: all 0.3s ease;
    ">← Back to Dashboard</a>
</div>

<?php if (!empty($error)) : ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if (!empty($success)) : ?>
    <p style="color:green;"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<!-- Add Question Form -->
<form method="POST">
    <label>Question</label>
    <input type="text" name="question" required>

    <label>Option A</label>
    <input type="text" name="option_a" required>

    <label>Option B</label>
    <input type="text" name="option_b" required>

    <label>Option C</label>
    <input type="text" name="option_c" required>

    <label>Option D</label>
    <input type="text" name="option_d" required>

    <label>Map Option A to Skin Type</label>
    <input type="text" name="map_a">

    <label>Map Option B to Skin Type</label>
    <input type="text" name="map_b">

    <label>Map Option C to Skin Type</label>
    <input type="text" name="map_c">

    <label>Map Option D to Skin Type</label>
    <input type="text" name="map_d">

    <label>Question Level</label>
    <select name="level" required>
        <option value="basic">Basic</option>
        <option value="intermediate">Intermediate</option>
        <option value="advanced">Advanced</option>
    </select>

    <button type="submit">Add Question</button>
</form>

<hr>

<!-- Existing Questions Table -->
<h2>Existing Questions</h2>
<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Question</th>
        <th>Option A</th>
        <th>Option B</th>
        <th>Option C</th>
        <th>Option D</th>
        <th>Actions</th>
    </tr>
    <?php foreach ($questions as $row): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['question']) ?></td>
            <td><?= htmlspecialchars($row['option_a']) ?></td>
            <td><?= htmlspecialchars($row['option_b']) ?></td>
            <td><?= htmlspecialchars($row['option_c']) ?></td>
            <td><?= htmlspecialchars($row['option_d']) ?></td>
            <td>
                <a href="edit_quiz.php?id=<?= $row['id'] ?>">Edit</a> |
                <a href="delete_quiz.php?id=<?= $row['id'] ?>" onclick="return confirm('Delete this question?')">Delete</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
