<?php
session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Auth.php';

$db = Database::connect();
$user = new User($db);
$auth = new Auth($db);
// REGISTER
if (isset($_POST['register'])) {

    // 🚫 NEVER allow register to inherit login state
    unset($_SESSION['user_id'], $_SESSION['role']);

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $skintype = trim($_POST['skintype'] ?? '');
    $dob      = trim($_POST['dob'] ?? '');

    // AGE CHECK
    function calculateAge($dob) {
        try {
            return (new DateTime())->diff(new DateTime($dob))->y;
        } catch (Exception $e) {
            return 0;
        }
    }

    $age = calculateAge($dob);

    /* ---------- COMPREHENSIVE VALIDATION ---------- */
    
    $errors = [];

    // Check individual fields for more specific error messages
    if (empty($username)) {
        $errors[] = "Username is required.";
    } else {
        // USERNAME VALIDATION
        if (strlen($username) < 3) {
            $errors[] = "Username must be at least 3 characters long.";
        } elseif (!preg_match('/^[a-zA-Z\s]+$/', $username)) {
            $errors[] = "Username can only contain letters (a-z, A-Z) and spaces. No numbers or special characters.";
        }
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } else {
        // EMAIL VALIDATION
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address (e.g., user@example.com).";
        }
        // EMAIL EXISTS VALIDATION
        if (filter_var($email, FILTER_VALIDATE_EMAIL) && $user->emailExists($email)) {
            $errors[] = "This email is already registered.";
        }
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } else {
        // PASSWORD VALIDATION
        if (strlen($password) < 6) {
            $errors[] = "Password must be at least 6 characters long.";
        } else {
            $passwordErrors = [];
            
            if (!preg_match('/[A-Z]/', $password)) {
                $passwordErrors[] = "uppercase letter";
            }
            if (!preg_match('/[a-z]/', $password)) {
                $passwordErrors[] = "lowercase letter";
            }
            if (!preg_match('/[0-9]/', $password)) {
                $passwordErrors[] = "number";
            }
            if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>?\/\\|`~]/', $password)) {
                $passwordErrors[] = "special character";
            }

            if (!empty($passwordErrors)) {
                $errors[] = "Password must contain at least one " . implode(", one ", $passwordErrors) . ".";
            }
        }
    }

    if (empty($dob)) {
        $errors[] = "Date of birth is required.";
    } else {
        // DOB & AGE VALIDATION
        if ($age < 13) {
            $errors[] = "You must be at least 13 years old to register.";
        } elseif ($age > 60) {
            $errors[] = "Please enter a valid date of birth. Age cannot exceed 60 years.";
        }
    }

    if (empty($skintype)) {
        $errors[] = "Please complete the quiz first to determine your skin type.";
    }

    // If there are validation errors, redirect back
    if (!empty($errors)) {
        $_SESSION['register_error'] = implode("<br>", $errors);
        $_SESSION['active_form'] = 'register';
        // Preserve quiz data so user doesn't have to retake the quiz
        $_SESSION['quiz_skintype'] = $skintype;
        $_SESSION['quiz_concern'] = $_POST['concern'] ?? '';
        $_SESSION['quiz_tags'] = $_POST['preferred_tags'] ?? '';
        header("Location: index.php");
        exit();
    }

    /* ---------- INSERT ---------- */

    $registered = $user->register(
        $username,
        $email,
        $password,
        $skintype,
        $dob
    );

    if (!$registered) {
        $_SESSION['register_error'] = "Registration failed. Please try again.";
        $_SESSION['active_form'] = 'register';
        header("Location: index.php");
        exit();
    }

    /* ---------- SUCCESS (LIKE YOUR FRIEND) ---------- */

    // 🧹 CLEAR QUIZ STATE SO IT DOES NOT REOPEN
    unset(
        $_SESSION['quiz_skintype'],
        $_SESSION['quiz_concern'],
        $_SESSION['quiz_tags']
    );

    $_SESSION['register_success'] = "🎉 Account created successfully! Please login.";
    $_SESSION['active_form'] = 'login';

    header("Location: index.php");
    exit();
}


// Login
if (isset($_POST['login'])) {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "Please enter email and password.";
    } else {
        $loggedInUser = $auth->login($email, $password);
        if ($loggedInUser === 'blocked') {
            $_SESSION['login_error'] = "Your account has been blocked by an administrator. Please contact support.";
        } elseif ($loggedInUser) {
            $_SESSION['user_id']   = $loggedInUser['id'];
            $_SESSION['username']  = $loggedInUser['username'];
            $_SESSION['email']     = $loggedInUser['email'];
            $_SESSION['skintype']  = $loggedInUser['skintype'];
            $_SESSION['role']      = $loggedInUser['role'];
            $_SESSION['dob']       = $loggedInUser['dob']; // ✅ store DOB in session

            // ✅ Clear OTP session for this user
            unset($_SESSION['otp']); 

            header("Location: " . ($loggedInUser['role'] === 'admin' ? "admin/admindashboard.php" : "user/userdashboard.php"));
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid email or password.";
        }
    }

    $_SESSION['active_form'] = 'login';
    header("Location: index.php");
    exit();
}
?>
