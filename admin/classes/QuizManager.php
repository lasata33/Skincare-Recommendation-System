<?php
class QuizManager {
    private $db;

    public function __construct($conn) {
        $this->db = $conn; // MySQLi connection
    }

    // Add a new question
    public function addQuestion($data) {
        $answer_mapping = json_encode([
            'A' => $data['map_a'],
            'B' => $data['map_b'],
            'C' => $data['map_c'],
            'D' => $data['map_d']
        ]);

        $stmt = $this->db->prepare("
            INSERT INTO quiz_questions 
            (question, option_a, option_b, option_c, option_d, answer_mapping) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            "ssssss",
            $data['question'],
            $data['option_a'],
            $data['option_b'],
            $data['option_c'],
            $data['option_d'],
            $answer_mapping
        );

        $success = $stmt->execute();
        $error = $stmt->error;
        $stmt->close();

        return $success ? true : $error;
    }

    // Get all questions
    public function getAllQuestions() {
        $stmt = $this->db->prepare("SELECT * FROM quiz_questions ORDER BY id ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        $questions = [];
        while ($row = $result->fetch_assoc()) {
            $questions[] = $row;
        }
        $stmt->close();
        return $questions;
    }

    // Get a single question by ID
    public function getQuestionById($id) {
        $stmt = $this->db->prepare("SELECT * FROM quiz_questions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // Update a question
    public function updateQuestion($id, $data) {
        $answer_mapping = json_encode([
            'A' => $data['map_a'],
            'B' => $data['map_b'],
            'C' => $data['map_c'],
            'D' => $data['map_d']
        ]);

        $stmt = $this->db->prepare("
            UPDATE quiz_questions 
            SET question=?, option_a=?, option_b=?, option_c=?, option_d=?, answer_mapping=? 
            WHERE id=?
        ");
        $stmt->bind_param(
            "ssssssi",
            $data['question'],
            $data['option_a'],
            $data['option_b'],
            $data['option_c'],
            $data['option_d'],
            $answer_mapping,
            $id
        );

        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    // Delete a question
    public function deleteQuestion($id) {
        $stmt = $this->db->prepare("DELETE FROM quiz_questions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    // ✅ Check if a question already exists
    public function questionExists($question) {
        $stmt = $this->db->prepare("SELECT COUNT(*) AS cnt FROM quiz_questions WHERE question = ?");
        $stmt->bind_param("s", $question);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['cnt'] > 0;
    }
}
?>
