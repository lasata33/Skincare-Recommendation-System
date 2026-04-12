<?php
class AdminUserManager {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    // 🔹 Get all users (excluding super admins if needed)
    public function getAllUsers() {
        $stmt = $this->db->prepare("SELECT id, username, email, role, skintype, status FROM users_db ORDER BY id ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        $stmt->close();
        return $users;
    }

    // 🔹 Get a single user by ID
    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users_db WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    // 🔹 Update user details
    public function updateUser($id, $username, $email, $role, $skintype) {
        $stmt = $this->db->prepare("UPDATE users_db SET username=?, email=?, role=?, skintype=? WHERE id=?");
        $stmt->bind_param("ssssi", $username, $email, $role, $skintype, $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    // 🔹 Delete a user
    public function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users_db WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    // 🔹 Block a user
    public function blockUser($id) {
        $status = 'blocked';
        $stmt = $this->db->prepare("UPDATE users_db SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    // 🔹 Unblock a user
    public function unblockUser($id) {
        $status = 'active';
        $stmt = $this->db->prepare("UPDATE users_db SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
}
?>
