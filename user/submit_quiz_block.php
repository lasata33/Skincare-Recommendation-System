<?php
session_start();
require_once "../classes/Database.php";
require_once "../classes/Quiz.php";
require_once "../classes/User.php";

$db = Database::connect();
$quiz = new Quiz($db);
$user = new User($db);

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: ../index.php");
    exit();
}

$answers = $_POST['answer'] ?? [];
$level = $_POST['level'] ?? 'basic';

// 🧠 Prepare tracking variables
$skinTypeVotes = [
    'dry' => 0,
    'oily' => 0,
    'normal' => 0,
    'combination' => 0,
    'sensitive' => 0
];
$concern = '';
$tags = [];

// 🧠 Loop through answers
foreach ($answers as $questionId => $selectedOptions) {
    // Normalize to array (checkboxes return array, radios return string)
    if (!is_array($selectedOptions)) {
        $selectedOptions = [$selectedOptions];
    }

    // Fetch mapping from DB
    $stmt = $db->prepare("SELECT question, map_a, map_b, map_c, map_d FROM quiz_questions WHERE id = ?");
    $stmt->bind_param("i", $questionId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    foreach ($selectedOptions as $opt) {
        $mapKey = 'map_' . strtolower($opt); // e.g. map_a
        $mappedValue = $row[$mapKey] ?? '';

        // Concern question
        if (stripos($row['question'], 'biggest skin concern') !== false) {
            $concern = $mappedValue;
        }
        // Tags question
        elseif (stripos($row['question'], 'product features') !== false) {
            if (!empty($mappedValue)) {
                $tags[] = $mappedValue;
            }
        }
        // Skin type questions
        elseif (array_key_exists($mappedValue, $skinTypeVotes)) {
            $skinTypeVotes[$mappedValue]++;
        }
    }
}

// 🧠 Calculate skin type by majority vote
$new_skin_type = array_keys($skinTypeVotes, max($skinTypeVotes))[0];

// ✅ Update user profile in DB
$user->updateUserProfile($user_id, $new_skin_type, $concern, implode(',', $tags));

// ✅ Track progress
$answeredCount = count($answers);
if ($answeredCount > 0) {
    $progress = json_decode($user->getQuizProgress($user_id), true) ?? [];
    $progress[$level] = $answeredCount; // overwrite with latest count
    $user->updateQuizProgress($user_id, json_encode($progress));
}

// ✅ Update quiz level if higher
$level_order = ['basic' => 1, 'intermediate' => 2, 'advanced' => 3];
$current_level = $user->getQuizLevel($user_id);
if ($level_order[$level] > $level_order[$current_level ?? '']) {
    $user->updateQuizLevel($user_id, $level);
}

// 🎉 If skin type changed, show result card
$current_skin_type = $quiz->getUserSkinType($user_id);
if ($new_skin_type && $new_skin_type !== $current_skin_type) {
    $_SESSION['skin_type_changed'] = [
        'old' => $current_skin_type,
        'new' => $new_skin_type
    ];
    header("Location: quiz_result.php");
    exit();
}

// Otherwise go back to recommends
header("Location: userdashboard.php?section=recommends&banner=1");
exit();
?>
