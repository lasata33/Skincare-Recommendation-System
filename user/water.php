<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once "../classes/Database.php";
require_once "../classes/User.php";
require_once "../classes/WaterTracker.php";

$db = Database::connect();
$userObj = new User($db);
$waterObj = new WaterTracker($db);

if (!isset($_SESSION['user_id'])) {
    die("Not logged in");
}

$user_id = $_SESSION['user_id'];
$skinType = ucfirst(strtolower($userObj->getSkinType($user_id)));

$dailyGoal = $waterObj->getGoalForSkinType($skinType); // e.g. 2000ml
$waterTip = $waterObj->getTip($skinType, $dailyGoal);
$todayTotal = $waterObj->getTodayTotal($user_id);
$weeklyData = $waterObj->getWeeklyData($user_id);
$percent = round(($todayTotal / $dailyGoal) * 100);
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="water.css">
    <style>
        .water-info-header { margin-bottom: 20px; text-align: center; }
        .water-tip {
            color: #d868a9; margin-top: 10px; font-size: 1.1em;
            background: #fff6fa; padding: 10px; border-radius: 8px;
            box-shadow: 0 2px 8px rgba(216,104,169,0.1);
        }
        .water-fill { width: 100%; border-radius: 0 0 10px 10px; transition: height 0.4s ease, background-color 0.4s ease; }
        .goal-message {
            background-color: #d4edda; color: #155724; border: 2px solid #c3e6cb;
            padding: 12px 16px; border-radius: 8px; margin: 20px auto; width: fit-content;
            font-weight: bold; font-size: 1.1em; box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            animation: pop 0.4s ease;
        }
        @keyframes pop { 0% { transform: scale(0.8); opacity: 0; } 100% { transform: scale(1); opacity: 1; } }
        .fill-red { background-color: #e74c3c; }
        .fill-orange { background-color: #e67e22; }
        .fill-yellow { background-color: #f1c40f; }
        .fill-green { background-color: #4FC3F7; }
        .disabled-btn { background-color: #ccc !important; cursor: not-allowed; }
    </style>
</head>
<body>
<div class="water-tracker">
    <h2>💧 Water Tracker</h2>
    <div class="water-info-header">
        <p>Daily Goal: <strong><?= $dailyGoal ?> ml</strong></p>
        <p class="water-tip"><?= $waterTip ?></p>
    </div>

    <div class="bottle">
        <div id="waterFill" class="water-fill <?= $percent <= 30 ? 'fill-red' : ($percent <= 50 ? 'fill-orange' : ($percent <= 75 ? 'fill-yellow' : 'fill-green')) ?>" style="height: <?= min($percent, 100) ?>%;"></div>
        <div class="bottle-markers"><span>100%</span><span>75%</span><span>50%</span><span>25%</span></div>
    </div>

    <div class="water-info">
        <h3 id="todayTotal"><?= $todayTotal ?> ml</h3>
        <p>Today (<span id="todayPercent"><?= $percent ?>%</span>)</p>
    </div>

    <form id="waterForm">
        <input type="number" id="waterAmount" placeholder="Add ml (e.g. 250)" max="5000" required>
        <button type="submit" id="addBtn">Add</button>
    </form>

    <!-- Goal celebration (2000ml) -->
    <div id="goalMessage" class="goal-message" style="<?= $todayTotal >= $dailyGoal && $todayTotal < 5000 ? '' : 'display:none;' ?>">
        🎉 YAYYYYY! You just hit your water goal today! Stay glowing 💧💙
    </div>

    <!-- Flood warning (5000ml cap) -->
    <div id="limitMessage" class="goal-message" style="<?= $todayTotal >= 5000 ? '' : 'display:none;' ?>">
        🚫 Stay hydrated, not flooded 💧
    </div>

    <canvas id="weeklyChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = <?= json_encode(array_map(fn($d) => date("M d", strtotime($d)), array_keys($weeklyData))) ?>;
const weeklyData = <?= json_encode(array_values($weeklyData)) ?>;
const dailyGoal = <?= $dailyGoal ?>;
const todayTotal = <?= $todayTotal ?>;

const ctx = document.getElementById('weeklyChart').getContext('2d');
const weeklyChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [
            { label: 'Water (ml)', data: weeklyData, backgroundColor: '#4FC3F7' },
            { label: 'Goal', data: Array(labels.length).fill(dailyGoal), type: 'line', borderColor: '#FF4081', borderWidth: 2, fill: false }
        ]
    },
    options: { responsive: true }
});

function getFillColorClass(percent) {
    if (percent <= 30) return 'fill-red';
    if (percent <= 50) return 'fill-orange';
    if (percent <= 75) return 'fill-yellow';
    return 'fill-green';
}

const addBtn = document.getElementById('addBtn');
if (todayTotal >= 5000) {
    addBtn.disabled = true;
    addBtn.classList.add('disabled-btn');
}

document.getElementById('waterForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const input = document.getElementById('waterAmount');
    const amount = parseInt(input.value);

    if (!amount || amount <= 0) return;
    if (amount > 5000) {
        alert("🚫 You can't enter more than 5000ml at once.");
        input.value = '';
        return;
    }

    fetch('water_add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'amount=' + encodeURIComponent(amount)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const percent = Math.round((data.todayTotal / data.dailyGoal) * 100);
            const fill = document.getElementById('waterFill');

            fill.style.height = Math.min(percent, 100) + '%';
            fill.className = 'water-fill ' + getFillColorClass(percent);

            document.getElementById('todayTotal').innerText = data.todayTotal + ' ml';
            document.getElementById('todayPercent').innerText = percent + '%';

            if (data.todayTotal >= data.dailyGoal && data.todayTotal < 5000) {
                document.getElementById('goalMessage').style.display = 'block';
            }

            if (data.todayTotal >= 5000) {
                document.getElementById('limitMessage').style.display = 'block';
                addBtn.disabled = true;
                addBtn.classList.add('disabled-btn');
            }

            const todayLabel = new Date().toLocaleString('en-US', { month: 'short', day: '2-digit' });
            const index = labels.indexOf(todayLabel);
            if (index !== -1) {
                weeklyChart.data.datasets[0].data[index] = data.todayTotal;
                weeklyChart.update();
            }

            input.value = '';
        } else {
            alert(data.error); // 🚫 show flood warning if >5000
        }
    })
    .catch(err => console.error(err));
});
</script>
</body>
</html>
