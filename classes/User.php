<?php
class User {
    private $db;

    public function __construct($conn) {
        $this->db = $conn;
    }

    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users_db WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();
        return $exists;
    }

   public function register($username, $email, $password, $skintype, $dob, $role = 'user') {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $this->db->prepare("INSERT INTO users_db (username, email, password, skintype, dob, role) VALUES (?, ?, ?, ?, ?, ?)");
    if (!$stmt) return false;
    $stmt->bind_param("ssssss", $username, $email, $hashed, $skintype, $dob, $role);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

    public function getById($id) {
    $stmt = $this->db->prepare("SELECT * FROM users_db WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}

    public function updateProfile($id, $email, $phone, $photo, $city) {
    $stmt = $this->db->prepare("UPDATE users_db SET email = ?, phone = ?, profile_photo = ?, city = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $email, $phone, $photo, $city, $id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

public function getSkinType($id) {
    $stmt = $this->db->prepare("SELECT skintype FROM users_db WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['skintype'] ?? 'normal';
}

public function getCity($id) {
    $stmt = $this->db->prepare("SELECT city FROM users_db WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['city'] ?? 'Kathmandu';
}

public function updateCity($id, $city) {
    $stmt = $this->db->prepare("UPDATE users_db SET city = ? WHERE id = ?");
    $stmt->bind_param("si", $city, $id);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

public function getAllNonAdminUsers() {
    $stmt = $this->db->prepare("SELECT email, username FROM users_db WHERE email NOT LIKE 'admin%@%'");
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
    return $users;
}

public function getUserById($id) {
    $stmt = $this->db->prepare("SELECT * FROM users_db WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

public function getQuizLevel($user_id) {
    $stmt = $this->db->prepare("SELECT quiz_level_completed FROM users_db WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result['quiz_level_completed'] ?? null;
}

public function updateQuizLevel($user_id, $level) {
    $stmt = $this->db->prepare("UPDATE users_db SET quiz_level_completed = ? WHERE id = ?");
    $stmt->bind_param("si", $level, $user_id);
    $stmt->execute();
    $stmt->close();
}

public function updateEmail($user_id, $new_email) {
    $stmt = $this->db->prepare("UPDATE users_db SET email = ? WHERE id = ?");
    return $stmt->execute([$new_email, $user_id]);
}

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

function calculateAge($dob) {
    $birthDate = new DateTime($dob);
    $today = new DateTime();
    return $today->diff($birthDate)->y; // returns age in years
}

public function updateUserProfile($user_id, $skintype, $concern, $tags) {
    $stmt = $this->db->prepare("UPDATE users_db SET skintype=?, concern=?, preferred_tags=? WHERE id=?");
    $stmt->bind_param("sssi", $skintype, $concern, $tags, $user_id);
    return $stmt->execute();
}

public function updatePasswordByEmail($email, $password) {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $this->db->prepare("UPDATE users_db SET password = ? WHERE email = ?");
    if (!$stmt) return false;
    $stmt->bind_param("ss", $hashed, $email);
    $success = $stmt->execute();
    $stmt->close();
    return $success;
}

public function recordSkinTypeChange($user_id, $oldSkinType, $newSkinType) {
    if ($oldSkinType !== $newSkinType) {
        $stmt = $this->db->prepare("INSERT INTO skintype_history (user_id, old_skintype, new_skintype, changed_at) VALUES (?, ?, ?, NOW())");
        if (!$stmt) return false;
        $stmt->bind_param("iss", $user_id, $oldSkinType, $newSkinType);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }
    return true;
}

public function getSkinTypeHistory($user_id, $limit = 10) {
    $stmt = $this->db->prepare("SELECT old_skintype, new_skintype, changed_at FROM skintype_history WHERE user_id = ? ORDER BY changed_at DESC LIMIT ?");
    if (!$stmt) return [];
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    $stmt->close();
    return $history;
}

public function updateSkinType($user_id, $newSkinType) {
    $oldSkinType = $this->getSkinType($user_id);
    
    $stmt = $this->db->prepare("UPDATE users_db SET skintype = ? WHERE id = ?");
    if (!$stmt) return false;
    $stmt->bind_param("si", $newSkinType, $user_id);
    $success = $stmt->execute();
    $stmt->close();
    
    if ($success && $oldSkinType !== $newSkinType) {
        $this->recordSkinTypeChange($user_id, $oldSkinType, $newSkinType);
    }
    
    return $success;
}

}
?>
