<?php
session_start();

$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];
$successMsg = $_SESSION['register_success'] ?? '';

$activeForm = $_SESSION['active_form'] ?? '';

// Clear session errors and success so they do not persist after reload
unset($_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['register_success'], $_SESSION['active_form']);


function showError($error){
    return !empty($error) ? "<div class='reg-error' style='color:#c0392b; background-color:#fadbd8; padding:12px; border-radius:5px; margin-bottom:15px; border-left:4px solid #c0392b; line-height:1.6;'>$error</div>" : '';
}

function isActiveForm($formName, $activeForm) {
    return $activeForm === $formName ? 'active' : '';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Skincare Recommendation System</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>" />
    <script src="script.js?v=<?php echo time(); ?>" defer></script>
    <style>
        .result-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }
        .result-modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            max-width: 500px;
            width: 90%;
            position: relative;
        }
        .result-modal h2 {
            color: #2c3e50;
            margin-bottom: 1rem;
        }
        .result-modal p {
            color: #34495e;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        .continue-btn {
            background-color: #2c3e50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }
        .continue-btn:hover {
            background-color: #34495e;
        }
        .skin-type-result {
            font-size: 1.2rem;
            font-weight: bold;
            color: #2c3e50;
            margin: 1rem 0;
        }
    </style>
</head>

<body>
    <!-- Quiz Result Modal -->
    <div id="result-modal" class="result-modal">
        <div class="result-modal-content">
            <h2>🎉 Quiz Complete!</h2>
            <p>Based on your answers, your skin type is:</p>
            <div class="skin-type-result" id="skin-type-result"></div>
            <p>Great! Now let's complete your registration to get personalized skincare recommendations!</p>
            <button class="continue-btn" id="continue-to-register">Continue to Register</button>
        </div>
    </div>
<header>
    <div class="nav-container">
        <div class="logo">Skinsync</div>
        <nav>
            <a href="#home">Home</a>
            <a href="#about">About</a>
            <a href="#contact">Contact</a>
            <a href="#login" id="nav-login-btn">Login</a>
        </nav>
    </div>
</header>

<main>
    <section class="hero" id="home">
        <div class="hero-image-box">
            <img class="hero-image" src="routine.jpg" alt="Glowing skin" />
            <div class="hero-overlay">
                <h1>Welcome to Skinsync</h1>
                <p>Your smart skincare companion. Discover personalized routines, analyze products, and get tips for glowing skin!</p>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="features-header">
            <h2>Why Choose Skinsync?</h2>
            <p>Discover powerful tools designed for your skin health</p>
        </div>
        
        <div class="features-cards">
            <div class="feature-card" onclick="toggleBenefits(event, this)">
                <div class="feature-emoji">📋</div>
                <img src="skincare.png" alt="Skincare Quiz" />
                <h2>Skincare Quiz</h2>
                <p class="feature-description">Take our quiz during registration to discover your skin type and get tailored product recommendations.</p>
                <div class="feature-benefits hidden">
                    <ul>
                        <li>✓ AI-powered skin type detection</li>
                        <li>✓ Personalized recommendations</li>
                        <li>✓ Takes only 2 minutes</li>
                        <li>✓ Science-based algorithm</li>
                    </ul>
                </div>
                <div class="feature-click-hint">Click to explore →</div>
            </div>
            
            <div class="feature-card" onclick="toggleBenefits(event, this)">
                <div class="feature-emoji">🔬</div>
                <img src="skin-cell.png" alt="OCR Ingredient Analysis" />
                <h2>Ingredient Analyzer (NLP)</h2>
                <p class="feature-description">Scan product ingredients and get instant feedback on their suitability for your skin.</p>
                <div class="feature-benefits hidden">
                    <ul>
                        <li>✓ Real-time ingredient scanning</li>
                        <li>✓ NLP-powered analysis</li>
                        <li>✓ Safety ratings for your skin</li>
                        <li>✓ Instant recommendations</li>
                    </ul>
                </div>
                <div class="feature-click-hint">Click to explore →</div>
            </div>
            
            <div class="feature-card" onclick="toggleBenefits(event, this)">
                <div class="feature-emoji">☀️</div>
                <img src="meteorology.png" alt="Smart Skincare Tips" />
                <h2>Smart Skincare Tips</h2>
                <p class="feature-description">Get daily skincare tips based on your local weather conditions.</p>
                <div class="feature-benefits hidden">
                    <ul>
                        <li>✓ Weather-based advice</li>
                        <li>✓ Real-time recommendations</li>
                        <li>✓ Skin-type specific tips</li>
                        <li>✓ Daily notifications</li>
                    </ul>
                </div>
                <div class="feature-click-hint">Click to explore →</div>
            </div>
            
            <div class="feature-card" onclick="toggleBenefits(event, this)">
                <div class="feature-emoji">💧</div>
                <img src="glass-of-water.png" alt="Water Tracker" />
                <h2>Water Tracker</h2>
                <p class="feature-description">Track your daily water intake and stay hydrated for healthy skin.</p>
                <div class="feature-benefits hidden">
                    <ul>
                        <li>✓ Smart hydration tracking</li>
                        <li>✓ Personalized daily goals</li>
                        <li>✓ Health reminders</li>
                        <li>✓ Progress analytics</li>
                    </ul>
                </div>
                <div class="feature-click-hint">Click to explore →</div>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about" style="padding: 50px 20px; background: #fff6fa; text-align: center;">
        <h2 style="color: #d868a9; font-family: 'Segoe Script', cursive;">About Skinsync</h2>
        <p style="max-width: 800px; margin: 20px auto; color: #a14c7e; font-size: 1.1em; line-height: 1.6;">
            Skinsync is your AI-powered skincare companion, designed to help you understand your skin and take better care of it.
            From personalized skincare quizzes to ingredient analysis and weather-based tips, we bring you the tools you need
            to achieve your healthiest skin ever all in one place.
        </p>
    </section>
</main>

<!-- Footer with Contact Info -->
<footer style="background: #d868a9; color: #fffbe7; padding: 30px 20px; text-align: center;">
    <div class="footer-content" style="max-width: 1000px; margin: auto;">
        <!-- <p style="margin-bottom: 10px;">&copy; 2025 Skinsync. All rights reserved.</p> -->
        <p style="margin: 5px 0;">📍 Kathmandu, Nepal</p>
        <p style="margin: 5px 0;">📧 <a href="mailto:skinsync11@gmail.com" style="color: #fffbe7; text-decoration: underline;">support@skinsync.com</a></p>
        <p style="margin: 5px 0;">📞 +977-9800000000</p>
    </div>
</footer>


<!-- Register Modal -->
<div id="register-modal" class="modal-bg" style="display: <?= $activeForm === 'register' ? 'flex' : 'none' ?>;" tabindex="-1">
    <div class="modal-content <?= isActiveForm('register', $activeForm); ?>" id="register-modal-content">
        <button id="close-modal" class="close-modal" type="button">&times;</button>
       
        <div id="quiz-section" style="display: <?= !empty($errors['register']) ? 'none' : 'block' ?>;">
            <form id="quiz-form" class="quiz-form" style="overflow-y: auto; max-height: 60vh; min-height: 200px; padding-right: 8px; margin-bottom: 0; background: transparent; scrollbar-width: thin;">
                <div id="quiz-questions">
                <p>1. How does your skin feel a few hours after washing?</p>
                <label><input type="radio" name="q1" value="Dry" required> Tight or flaky</label><br/>
                <label><input type="radio" name="q1" value="Oily" required> Shiny or greasy</label><br/>
                <label><input type="radio" name="q1" value="Normal" required> Normal, no tightness or shine</label><br/>
                <label><input type="radio" name="q1" value="Combination" required> Oily in some areas, dry in others</label><br/>

                <p>2. How often do you experience breakouts?</p>
                <label><input type="radio" name="q2" value="Oily" required> Often</label><br/>
                <label><input type="radio" name="q2" value="Combination" required> Occasionally</label><br/>
                <label><input type="radio" name="q2" value="Dry" required> Rarely or never</label><br/>

                <p>3. How visible are your pores?</p>
                <label><input type="radio" name="q3" value="Oily" required> Large and visible on most of the face</label><br/>
                <label><input type="radio" name="q3" value="Combination" required> Large and visible only on the nose and cheeks</label><br/>
                <label><input type="radio" name="q3" value="Dry" required> Small or invisible</label><br/>

                <p>4. How does your skin react to new skincare products?</p>
                <label><input type="radio" name="q4" value="Sensitive" required> Easily irritated, red, or itchy</label><br/>
                <label><input type="radio" name="q4" value="Normal" required> No reaction</label><br/>

                <p>5. How often do you need to moisturize your skin?</p>
                <label><input type="radio" name="q5" value="Dry" required> Multiple times a day</label><br/>
                <label><input type="radio" name="q5" value="Normal" required> Once a day or less</label><br/>
                <label><input type="radio" name="q5" value="Oily" required> Rarely</label><br/>

                <p>6. How does your skin look by midday?</p>
                <label><input type="radio" name="q6" value="Oily" required> Shiny or greasy all over</label><br/>
                <label><input type="radio" name="q6" value="Combination" required> Shiny only in the T-zone</label><br/>
                <label><input type="radio" name="q6" value="Normal" required> Matte and balanced</label><br/>

                <p>7. How often do you experience redness or flushing?</p>
                <label><input type="radio" name="q7" value="Sensitive" required> Frequently</label><br/>
                <label><input type="radio" name="q7" value="Combination" required> Occasionally</label><br/>
                <label><input type="radio" name="q7" value="Normal" required> Rarely or never</label><br/>

                <p>8. How does your skin feel after applying sunscreen or makeup?</p>
                <label><input type="radio" name="q8" value="Oily" required> Greasy or heavy</label><br/>
                <label><input type="radio" name="q8" value="Dry" required   > Tight or flaky</label><br/>
                <label><input type="radio" name="q8" value="Normal" required > Comfortable and smooth</label><br/>  

                <p>9. How does your skin feel in cold weather?</p>
                <label><input type="radio" name="q9" value="Dry" required> Tight, flaky, dry</label><br/>
                <label><input type="radio" name="q9" value="Sensitive" required> Red or irritated</label><br/>
                <label><input type="radio" name="q9" value="Normal" required> No change</label><br/>

                <p>10. How does your skin feel after sweating or exercising?</p>
                <label><input type="radio" name="q10" value="Oily" required> Oily and shiny</label><br/>
                <label><input type="radio" name="q10" value="Dry" required> Dry or tight</label><br/>
                <label><input type="radio" name="q10" value="Normal" required> No significant change</label><br/>
                </div>
                <button type="submit" class="register-btn modal-submit">Submit Quiz & Continue to Register</button>
            </form>
        </div>
        <div id="registration-section" style="display: <?= !empty($errors['register']) ? 'block' : 'none' ?>;">
            <h2 class="modal-title">Complete Registration</h2>
            <?= showError($errors['register']); ?>
            <form action="login_register.php" method="post" id="register-form" autocomplete="off">
    <label for="username">Username</label>
    <input type="text" id="username" name="username" required minlength="3" placeholder="Enter username" />

    <label for="email">Email</label>
    <input type="email" id="email" name="email" required placeholder="Enter email" />

    <label for="dob">Date of Birth</label>
    <input type="date" name="dob" id="dob" required>

    <label for="password">Password</label>
    <input type="password" id="password" name="password" required minlength="6" placeholder="Enter password" />

    <!-- Hidden inputs populated from quiz session -->
    <input type="hidden" id="skin-type-input" name="skintype" value="<?= $_SESSION['quiz_skintype'] ?? '' ?>" />
    <input type="hidden" id="concern-input" name="concern" value="<?= $_SESSION['quiz_concern'] ?? '' ?>" />
    <input type="hidden" id="tags-input" name="preferred_tags" value="<?= $_SESSION['quiz_tags'] ?? '' ?>" />

    <button type="submit" name="register" class="register-btn modal-submit">Register</button>
</form>

        </div>
        <div class="modal-switch">Already have an account? <a href="#" id="show-login">Login</a></div>
    </div>
</div>

<!-- Login Modal -->
<div id="login-modal" class="modal-bg" style="display: <?= $activeForm === 'login' ? 'flex' : 'none' ?>;" tabindex="-1">
    <div class="modal-content <?= isActiveForm('login', $activeForm); ?>">
        <button id="close-login-modal" class="close-modal" type="button">&times;</button>
        <h2 class="modal-title">Login</h2>
        <?php if ($successMsg): ?>
            <div class="success-message" style="color: green; margin-bottom: 10px;">
                <?= htmlspecialchars($successMsg) ?>
            </div>
        <?php endif; ?>
        <?= showError($errors['login']); ?>
        <form action="login_register.php" method="post" id="login-form" autocomplete="off">
            <label for="login-email">Email</label>
            <input type="email" id="login-email" name="email" required placeholder="Enter email" />
            <label for="login-password">Password</label>
            <input type="password" id="login-password" name="password" required placeholder="Enter password" />
            <button type="submit" name="login" class="register-btn modal-submit">Login</button>
        </form>
        <div class="modal-switch">Don't have an account? <a href="#" id="show-register">Register</a></div>
        <!-- <div class="modal-switch" style="margin-top: 10px;">
            <a href="#" id="show-forgot-password">Forgot Password?</a>
     -->
</div>



<!-- Modal behavior handled in external script.js -->
</body>
</html>