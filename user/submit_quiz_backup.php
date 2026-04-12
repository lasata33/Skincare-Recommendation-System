<?php
session_start();
require_once "../classes/Database.php";
require_once "../classes/Quiz.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if (!isset($_POST['answer']) || empty($_POST['answer'])) {
    header("Location: userdashboard.php?section=quiz");
    exit();
}

$db = Database::connect();
$quiz = new Quiz($db);

$quiz->submitAnswers($user_id, $_POST['answer']);

header("Location: userdashboard.php?section=quiz");
exit();
?>
