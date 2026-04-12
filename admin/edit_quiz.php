<?php
require_once "../classes/Database.php";
require_once "classes/AdminAuth.php";
require_once "classes/QuizManager.php";

AdminAuth::requireAdmin();

$db = Database::connect();
$quizManager = new QuizManager($db);

$id = intval($_GET['id']);
$q = $quizManager->getQuestionById($id);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'question' => $_POST['question'],
        'option_a' => $_POST['option_a'],
        'option_b' => $_POST['option_b'],
        'option_c' => $_POST['option_c'],
        'option_d' => $_POST['option_d'],
        'map_a' => $_POST['map_a'],
        'map_b' => $_POST['map_b'],
        'map_c' => $_POST['map_c'],
        'map_d' => $_POST['map_d']
    ];

    // Validation: check for special characters in question and options
    $fieldsToCheck = ['question' => $data['question'], 'option_a' => $data['option_a'], 'option_b' => $data['option_b'], 'option_c' => $data['option_c'], 'option_d' => $data['option_d']];
    
    $error = null;
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

    if ($error) {
        $_SESSION['error'] = $error;
        header("Location: edit_quiz.php?id=$id");
        exit();
    }

    $quizManager->updateQuestion($id, $data);
    header("Location: manage_quiz.php?msg=Question updated successfully");
    exit();
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Edit Quiz Question</title>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background-color: #fffafc;
            padding: 40px;
            color: #5b4b57;
        }

        h1 {
            color: #d868a9;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            background-color: #ffeef6;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(216, 104, 169, 0.1);
            display: grid;
            gap: 15px;
            max-width: 600px;
            margin: auto;
        }

        form label {
            font-weight: 500;
            color: #5b4b57;
        }

        form input[type="text"] {
            padding: 10px;
            border: 1.5px solid #ffd7e9;
            border-radius: 8px;
            font-size: 0.95rem;
            background-color: #fff;
            color: #5b4b57;
        }

        form input:focus {
            border-color: #d868a9;
            outline: none;
            box-shadow: 0 0 0 3px rgba(216, 104, 169, 0.1);
        }

        form button {
            background-color: #d868a9;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 1rem;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
            justify-self: start;
        }

        form button:hover {
            background-color: #c7579a;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(216, 104, 169, 0.3);
        }
    </style>
</head>
<body>

<h1>Edit Quiz Question</h1>

<?php if (isset($_SESSION['error'])): ?>
    <div style="background-color: #fee; color: #c00; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #c00; max-width: 600px; margin-left: auto; margin-right: auto;">
        <?= htmlspecialchars($_SESSION['error']) ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<form method="POST">
    <label>Question</label>
    <input type="text" name="question" value="<?= htmlspecialchars($q['question']) ?>" required>

    <label>Option A</label>
    <input type="text" name="option_a" value="<?= htmlspecialchars($q['option_a']) ?>" required>

    <label>Option B</label>
    <input type="text" name="option_b" value="<?= htmlspecialchars($q['option_b']) ?>" required>

    <label>Option C</label>
    <input type="text" name="option_c" value="<?= htmlspecialchars($q['option_c']) ?>" required>

    <label>Option D</label>
    <input type="text" name="option_d" value="<?= htmlspecialchars($q['option_d']) ?>" required>

    <label>Map A</label>
    <input type="text" name="map_a" value="<?= htmlspecialchars($q['map_a']) ?>" required>

    <label>Map B</label>
    <input type="text" name="map_b" value="<?= htmlspecialchars($q['map_b']) ?>" required>

    <label>Map C</label>
    <input type="text" name="map_c" value="<?= htmlspecialchars($q['map_c']) ?>" required>

    <label>Map D</label>
    <input type="text" name="map_d" value="<?= htmlspecialchars($q['map_d']) ?>" required>

    <button type="submit">Update Question</button>
</form>

</body>
</html>
