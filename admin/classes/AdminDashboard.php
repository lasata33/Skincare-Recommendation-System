<?php
class AdminDashboard {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    public function getStats() {
        $stats = [];

        $stats['total_users'] = $this->db->query("SELECT COUNT(*) AS count FROM users_db")->fetch_assoc()['count'];
        $stats['quiz_taken'] = $this->db->query("SELECT COUNT(*) AS count FROM users_db WHERE skintype IS NOT NULL AND skintype != ''")->fetch_assoc()['count'];
        $stats['total_products'] = $this->db->query("SELECT COUNT(*) AS count FROM products")->fetch_assoc()['count'];

        return $stats;
    }
}
?>
