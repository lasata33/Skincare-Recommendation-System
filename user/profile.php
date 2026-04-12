<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../classes/Database.php";
require_once "../classes/User.php";

$db = Database::connect();
$userObj = new User($db);

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);
$error_message = '';
$edit_mode = isset($_GET['edit']);

$user = $userObj->getById($user_id);

$username = $user['username'];
$email = $user['email'];
$phone = $user['phone'] ?? '';
$profile_photo = $user['profile_photo'] ?? '';
$skintype = $user['skintype'] ?? '';
$city = $user['city'] ?? '';
$dob = $user['dob'] ?? ''; // 👈 fetch dob from users_db

// ✅ Calculate age if dob exists
$age = '';
if (!empty($dob)) {
    try {
        $dobDate = new DateTime($dob);
        $today = new DateTime();
        $age = $today->diff($dobDate)->y;
    } catch (Exception $e) {
        $age = '';
    }
}


// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = $_POST['email'] ?? $email;
    $new_phone = $_POST['phone'] ?? $phone;
    $new_city = $_POST['city'] ?? $city;
    $new_profile_photo = $profile_photo;

    if (!empty($_FILES['profile_photo']['name'])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_type = $_FILES['profile_photo']['type'];

        if (in_array($file_type, $allowed_types)) {
            $file_name = time() . "_" . basename($_FILES["profile_photo"]["name"]);
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
                if (!empty($profile_photo) && file_exists($profile_photo)) {
                    unlink($profile_photo);
                }
                $new_profile_photo = $target_file;
            } else {
                $error_message = "Failed to upload profile photo.";
            }
        } else {
            $error_message = "Only JPG, JPEG & PNG files are allowed.";
        }
    }

    if (empty($error_message)) {
        $updated = $userObj->updateProfile($user_id, $new_email, $new_phone, $new_profile_photo, $new_city);
        if ($updated) {
            $_SESSION['success_message'] = "Profile updated successfully!";
            header("Location: userdashboard.php?section=profile");
            exit();
        } else {
            $error_message = "Failed to update profile.";
        }
    }
}
?>

<?php if ($section === 'profile'): ?>
    <link rel="stylesheet" href="profile.css">
<?php endif; ?>

<div class="profile-container">
    <?php if ($success_message): ?>
        <div class="alert success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="alert error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <div class="profile-header">
        <div class="profile-photo">
            <?php if ($profile_photo): ?>
                <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Photo">
            <?php else: ?>
                <div class="profile-photo-placeholder">
                    <span><?php echo strtoupper(substr($username, 0, 1)); ?></span>
                </div>
            <?php endif; ?>
        </div>
        <div class="profile-title">
            <h1>👤 <?php echo htmlspecialchars($username); ?></h1>
            <?php if ($skintype): ?>
                <span class="skin-type-badge">✨ <?php echo htmlspecialchars(ucfirst($skintype)); ?> Skin</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($edit_mode): ?>
        <form method="POST" enctype="multipart/form-data" class="profile-form">
            <div class="form-group">
                <label for="profile_photo">Profile Photo</label>
                <input type="file" id="profile_photo" name="profile_photo" accept="image/jpeg,image/png,image/jpg">
                <small>Allowed formats: JPG, JPEG, PNG</small>
            </div>

            <div class="form-group">
                <p><strong>Email:</strong> <?= htmlspecialchars($email) ?></p>
<a href="change_email.php" class="secure-edit-btn">🔐 Change Email</a>

            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" 
       value="<?php echo htmlspecialchars($phone); ?>" 
       pattern="^(97|98)[0-9]{8}$" 
       title="Phone number must start with 97 or 98 and be 10 digits">
<small>Format: 10 digits starting with 97 or 98</small>

            </div>

            <div class="form-group">
                <label for="city">Your City</label>
                <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($city); ?>" required>
            </div>

            <button type="submit" class="save-btn">Save Changes</button>
        </form>
    <?php else: ?>
        <div class="profile-info">
            <p><span><strong>Email:</strong> <?= htmlspecialchars($email) ?></span> <a href="userdashboard.php?section=profile&edit=1" class="edit-btn">✏️ Edit</a></p>
            <p><span><strong>Phone:</strong> <?= htmlspecialchars($phone) ?></span> <a href="userdashboard.php?section=profile&edit=1" class="edit-btn">✏️ Edit</a></p>
            <p><span><strong>City:</strong> <?= htmlspecialchars($city) ?></span> <a href="userdashboard.php?section=profile&edit=1" class="edit-btn">✏️ Edit</a></p>
            <p><span><strong>Profile Photo:</strong> <?= $profile_photo ? 'Uploaded ✅' : 'Not uploaded' ?></span> <a href="userdashboard.php?section=profile&edit=1" class="edit-btn">✏️ Edit</a></p>
            <p><span><strong>Age:</strong> <?= $age ? $age . ' years' : 'Not set' ?></span> 
   <a href="userdashboard.php?section=profile&edit=1" class="edit-btn">✏️ Edit</a>
</p>

        </div>
    <?php endif; ?>
</div>
