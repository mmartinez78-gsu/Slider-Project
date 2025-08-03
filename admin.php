<?php
session_start();

$settingsJSON = file_get_contents(__DIR__ . "/databaseSettings_mysql.json");
$settings = json_decode($settingsJSON);
$conn = new mysqli("localhost", $settings->username, $settings->password, $settings->dbName);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

$stmt = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($role);
$stmt->fetch();
$stmt->close();
if ($role !== 'admin') {
    header("Location: home.php");
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(isset($_POST['user_changes'])){
        $bans = json_decode($_POST['banList'], true);
        foreach($bans as $ban){
            $upd = $conn->prepare("UPDATE users SET banned = 1 WHERE user_id = ?");
            $upd->bind_param("i", $ban);
            $upd->execute();
            $upd->close();
        }
        $unbans = json_decode($_POST['unbanList'], true);
        foreach($unbans as $unban){
            $upd = $conn->prepare("UPDATE users SET banned = 0 WHERE user_id = ?");
            $upd->bind_param("i", $unban);
            $upd->execute();
            $upd->close();
        }
        $admins = json_decode($_POST['adminList'], true);
        foreach($admins as $admin){
            $upd = $conn->prepare("UPDATE users SET role = 'admin' WHERE user_id = ?");
            $upd->bind_param("i", $admin);
            $upd->execute();
            $upd->close();
        }
        $deadmins = json_decode($_POST['deadminList'], true);
        foreach($deadmins as $deadmin){
            $upd = $conn->prepare("UPDATE users SET role = 'player' WHERE user_id = ?");
            $upd->bind_param("i", $deadmin);
            $upd->execute();
            $upd->close();
        }
    }
}

$usersSql = "SELECT user_id, username, role, banned FROM users";
$usersResult = $conn->query($usersSql);

$analyticsSql = "
    SELECT puzzle_size,
           COUNT(*) AS games_played,
           ROUND(AVG(time_taken_seconds),2) AS avg_time,
           ROUND(AVG(moves_count),2) AS avg_moves
    FROM game_stats
    GROUP BY puzzle_size
";
$analyticsResult = $conn->query($analyticsSql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard &ndash; Manage Site</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background-color: #f4f4f4; }
        .section { margin-bottom: 40px; }
    </style>
</head>
<body>
    <main class="stdLayout">
        <a href="home.php" class="returnBtn"><button>Return Home</button></a>
        <h1>Admin Dashboard</h1>

        <div class="section">
            <h2>Account Management</h2>
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($usersResult && $usersResult->num_rows > 0) {
                        while ($u = $usersResult->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td class="user_ids">' . htmlspecialchars($u['user_id']) . '</td>';
                            echo '<td>' . htmlspecialchars($u['username']) . '</td>';
                            echo '<td id="role_' . htmlspecialchars($u['user_id']) . '" class="tileHover">' . htmlspecialchars($u['role']) . '</td>';
                            $status = $u['banned'] ? 'Banned' : 'Active';
                            echo '<td id="status_' . htmlspecialchars($u['user_id']) . '" class="tileHover">' . $status . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="5">No user accounts found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
            <button id="applyUserChanges">Apply Changes</button>
        </div>

        <div class="section">
            <h2>Game Analytics</h2>
            <table class="analytics">
                <thead>
                    <tr>
                        <th>Puzzle Size</th>
                        <th>Games Played</th>
                        <th>Avg Time (s)</th>
                        <th>Avg Moves</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($analyticsResult && $analyticsResult->num_rows > 0) {
                        while ($row = $analyticsResult->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['puzzle_size']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['games_played']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['avg_time']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['avg_moves']) . '</td>';
                            echo '</tr>';
                        }
                    } else {
                        echo '<tr><td colspan="4">No game statistics available.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
    <div id="validators">
        <a href="http://validator.w3.org/check/referer"><img src="valid-xhtml11.png"></a><br>
        <a href="http://jigsaw.w3.org/css-validator/check/referer"><img src="valid-css.png"></a>
    </div>
    <script src="admin.js"></script>
</body>
</html>
<?php $conn->close(); ?>