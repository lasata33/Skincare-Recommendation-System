<?php
class WaterTracker {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    public function getGoalForSkinType($skinType) {
        $goals = [
            'Dry' => 2500,
            'Oily' => 2000,
            'Combination' => 2200,
            'Sensitive' => 2300,
            'Normal' => 2000
        ];
        return $goals[$skinType] ?? 2000;
    }

    public function getTip($skinType, $goal) {
        $tips = [
            'Dry' => "💧 Tip: Your dry skin needs extra hydration! Aim for {$goal}ml daily.",
            'Oily' => "💧 Tip: Balance your skin with {$goal}ml of water daily.",
            'Combination' => "💧 Tip: Keep your skin balanced with {$goal}ml of water daily.",
            'Sensitive' => "💧 Tip: Keep your sensitive skin healthy with {$goal}ml of water daily.",
            'Normal' => "💧 Tip: Maintain your healthy skin with {$goal}ml of water daily."
        ];
        return $tips[$skinType] ?? '';
    }

    public function getTodayTotal($user_id) {
        $today = date("Y-m-d");
        $stmt = $this->db->prepare("SELECT SUM(amount) as total FROM water_intake WHERE user_id = ? AND date = ?");
        $stmt->bind_param("is", $user_id, $today);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['total'] ?? 0;
    }

    public function getWeeklyData($user_id) {
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date("Y-m-d", strtotime("-$i days"));
            $weeklyData[$date] = 0;
        }

        $stmt = $this->db->prepare("
            SELECT date, SUM(amount) as total 
            FROM water_intake 
            WHERE user_id = ? AND date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) 
            GROUP BY date
        ");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        while ($row = $result->fetch_assoc()) {
            $weeklyData[$row['date']] = $row['total'];
        }

        return $weeklyData;
    }

    public function addWater($user_id, $amount) {
    $today = date("Y-m-d");

    $stmt = $this->db->prepare("INSERT INTO water_intake (user_id, amount, date) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE amount = amount + VALUES(amount)");
    if ($stmt && $stmt->bind_param("iis", $user_id, $amount, $today) && $stmt->execute()) {
        $stmt->close();
        return true;
    }

    // fallback update
    $stmt = $this->db->prepare("UPDATE water_intake SET amount = amount + ? WHERE user_id = ? AND date = ?");
    if ($stmt && $stmt->bind_param("iis", $amount, $user_id, $today) && $stmt->execute()) {
        $stmt->close();
        return true;
    }

    // fallback insert
    $stmt = $this->db->prepare("INSERT INTO water_intake (user_id, amount, date) VALUES (?, ?, ?)");
    if ($stmt && $stmt->bind_param("iis", $user_id, $amount, $today) && $stmt->execute()) {
        $stmt->close();
        return true;
    }

    return false;
}


}
?>
