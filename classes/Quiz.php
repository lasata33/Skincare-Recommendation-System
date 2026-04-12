<?php
class Quiz {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    // ✅ Get user's current skin type
    public function getUserSkinType($user_id) {
        $stmt = $this->db->prepare("SELECT skintype FROM users_db WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['skintype'] ?? '';
    }

    // ✅ Set user's skin type explicitly
    public function setUserSkinType($user_id, $skin_type) {
        $stmt = $this->db->prepare("UPDATE users_db SET skintype = ? WHERE id = ?");
        $stmt->bind_param("si", $skin_type, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    // ✅ Submit answers and calculate dominant skin type using map_a...map_d
    public function submitAnswers($user_id, $answers) {
        $skin_type_counts = [
            'dry' => 0,
            'oily' => 0,
            'sensitive' => 0,
            'combination' => 0,
            'normal' => 0
        ];

        foreach ($answers as $qid => $opt) {
            $stmt = $this->db->prepare("SELECT map_a, map_b, map_c, map_d FROM quiz_questions WHERE id = ?");
            $stmt->bind_param("i", $qid);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($row) {
                $mapColumn = 'map_' . strtolower($opt); // e.g. "map_a"
                $skin = $row[$mapColumn] ?? null;
                if ($skin && isset($skin_type_counts[$skin])) {
                    $skin_type_counts[$skin]++;
                }
            }
        }

        if (max($skin_type_counts) === 0) return null;

        arsort($skin_type_counts);
        $new_skin_type = array_key_first($skin_type_counts);

        // Update user if changed
        $current = $this->getUserSkinType($user_id);
        if ($new_skin_type && $new_skin_type !== $current) {
            $this->setUserSkinType($user_id, $new_skin_type);
        }

        return $new_skin_type;
    }

    // ✅ Get questions by level
    public function getQuestionsByLevel($level) {
        $stmt = $this->db->prepare("SELECT * FROM quiz_questions WHERE level = ? ORDER BY id ASC");
        $stmt->bind_param("s", $level);
        $stmt->execute();
        return $stmt->get_result();
    }

    // ✅ Quiz progress
    public function getQuizProgress($user_id) {
        $stmt = $this->db->prepare("SELECT quiz_progress FROM users_db WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['quiz_progress'] ?? '{}';
    }

    public function updateQuizProgress($user_id, $json) {
        $stmt = $this->db->prepare("UPDATE users_db SET quiz_progress = ? WHERE id = ?");
        $stmt->bind_param("si", $json, $user_id);
        $stmt->execute();
        $stmt->close();
    }

    public function countQuestionsByLevel($level) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM quiz_questions WHERE level = ?");
        $stmt->bind_param("s", $level);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['total'] ?? 0;
    }
}
?>
