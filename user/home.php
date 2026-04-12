<?php
require_once '../classes/Database.php';
require_once '../classes/User.php';
require_once '../classes/WaterTracker.php';
require_once '../classes/Helper.php';
require_once '../classes/Product.php';

$db = Database::connect();
$userObj = new User($db);
$waterObj = new WaterTracker($db);

$user_id = $_SESSION['user_id'];
$currentUser = $userObj->getById($user_id);

$user_name = $currentUser['username'] ?? 'User';
$skin_type = $currentUser['skintype'] ?? 'Not determined yet';

$dailyGoal = $waterObj->getGoalForSkinType($skin_type);
$water_today = $waterObj->getTodayTotal($user_id);
$percent = min(100, round(($water_today / $dailyGoal) * 100));

$greeting = Helper::getGreeting();
$skin_tip = Helper::getSkinTip($skin_type);
$featured = Product::getFeaturedBySkinType($skin_type);
?>

<div class="home-container">
    <h1><?php echo $greeting; ?>, <?php echo htmlspecialchars($user_name); ?>! 🌼</h1>

    <div class="skin-info">
        <h2>Your Skin Type: <span><?php echo htmlspecialchars($skin_type); ?></span></h2>
        <p><?php echo htmlspecialchars($skin_tip); ?></p>
    </div>
<div class="quote-of-day" id="quoteOfDay">
    <h3>💬 Quote of the Day</h3>
    <p>Loading inspiration... ✨</p>
</div>

    <div class="hydration-summary">
    <h3>💧 Hydration Summary</h3>
    <p>Water Goal: <strong><?php echo $dailyGoal; ?> ml</strong></p>
    <p>Water Intake Today: <strong><?php echo $water_today; ?> ml</strong></p>
    <p>Progress: <strong><?php echo $percent; ?>%</strong></p>
</div>


    <div class="quick-actions">
        <h3>Quick Actions</h3>
        <a href="?section=recommends" class="quick-btn">📝 Take Quiz</a>
        <a href="?section=ingredient" class="quick-btn">🔍 Ingredient Analyzer</a>
        <a href="?section=water" class="quick-btn">💧 Water Tracker</a>
        <a href="?section=recommends" class="quick-btn">🛍 Products List</a>
    </div>

    <div class="weather-tip">
        <h3>Today's Weather Skincare Tip ☀</h3>
        <div class="gentle-reminder" id="gentleReminder">
            <p>☁ Fetching today’s skincare reminder...</p>
        </div>
    </div>

   

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // 🌤 Weather tip fetch
    fetch("weather.php?mode=json&skin=<?php echo urlencode($skin_type); ?>")
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.json();
        })
        .then(data => {
            console.log("Weather response:", data); // ✅ Debug log
            if (!data || !data.tip) {
                document.getElementById("gentleReminder").innerHTML =
                    "<p>⚠️ Couldn’t load today’s weather — stay hydrated and wear SPF!</p>";
                return;
            }
            document.getElementById("gentleReminder").innerHTML = `<p>${data.tip}</p>`;
        })
        .catch(err => {
            console.error("Weather fetch error:", err); // ✅ Debug log
            document.getElementById("gentleReminder").innerHTML =
                "<p>⚠️ Unable to load weather reminder. Stay glowing anyway! 💖</p>";
        });

    // 💬 Quote of the Day fetch
    fetch("https://api.allorigins.win/get?url=" + encodeURIComponent("https://zenquotes.io/api/today"))
        .then(res => res.json())
        .then(data => {
            const parsed = JSON.parse(data.contents);
            if (Array.isArray(parsed) && parsed[0]) {
                const quote = parsed[0].q;
                const author = parsed[0].a;
                document.getElementById("quoteOfDay").innerHTML = `
                    <h3>💬 Quote of the Day</h3>
                    <p>"${quote}"<br><em>— ${author}</em></p>
                `;
                document.getElementById("quoteOfDay").classList.add("loaded");
            } else {
                document.getElementById("quoteOfDay").innerHTML =
                    "<p>⚠️ Couldn’t load quote. Stay inspired anyway! 🌟</p>";
            }
        })
        .catch(err => {
            document.getElementById("quoteOfDay").innerHTML =
                "<p>⚠️ Unable to load quote. You’re still amazing! 💖</p>";
        });
});
</script>



<style>
.home-container {
    padding: 30px;
    background: #fffafc;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(255,182,193,0.2);
    color: #5b4b57;
    font-family: "Poppins", sans-serif;
}

.home-container h1 {
    font-size: 1.8em;
    color: #d868a9;
    margin-bottom: 10px;
}

.skin-info, .hydration-streak, .featured-product, .quote-of-day {
    margin-bottom: 20px;
}

.skin-info span {
    font-weight: bold;
    color: #d46aa0;
}

.quick-actions {
    margin: 20px 0;
}

.quick-btn {
    display: inline-block;
    margin: 8px 10px 8px 0;
    padding: 10px 18px;
    background-color: #ffd7e9;
    color: #d868a9;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.quick-btn:hover {
    background-color: #d868a9;
    color: #fff;
}

.gentle-reminder {
    background: #fff9e6;
    border-left: 5px solid #ffd36b;
    padding: 15px 20px;
    border-radius: 10px;
    font-size: 16px;
    color: #444;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
}

.quote-of-day p, .featured-product p {
    font-style: italic;
    color: #6a4c5f;
}

.hydration-summary {
    background: #e0f7fa;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    color: #006064;
}
.hydration-summary h3 {
    margin-top: 0;
    color: #00796b;
}

#quoteOfDay {
    opacity: 0;
    transition: opacity 1s ease;
}
#quoteOfDay.loaded {
    opacity: 1;
}

.quote-of-day {
    background: #f3f0ff;
    border-left: 5px solid #b39ddb;
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 20px;
    box-shadow: 0 3px 8px rgba(0,0,0,0.05);
    color: #4a3f63;
}
.quote-of-day p {
    font-style: italic;
    margin: 0;
}
.quote-of-day em {
    font-weight: 500;
    color: #6a4c5f;
}

</style>