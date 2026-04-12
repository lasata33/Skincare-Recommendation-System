<?php
class Auth {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT id, username, email, password, skintype, role, status FROM users_db WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            // Check if user is blocked
            if ($user['status'] === 'blocked') {
                return 'blocked'; // Return 'blocked' to indicate user is blocked
            }
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }
}
?>
