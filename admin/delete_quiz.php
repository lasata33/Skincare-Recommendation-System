<?php
require_once "../classes/Database.php";
require_once "classes/AdminAuth.php";
require_once "classes/QuizManager.php";

AdminAuth::requireAdmin();

$db = Database::connect();
$quizManager = new QuizManager($db);

$id = intval($_GET['id']);
$quizManager->deleteQuestion($id);

header("Location: manage_quiz.php?msg=Question deleted");
exit();
