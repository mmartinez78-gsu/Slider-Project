<?php
session_start();

// Database connection
$settingsJSON = file_get_contents(__DIR__ . "/databaseSettings_mysql.json");
$settings = json_decode($settingsJSON);
$conn = new mysqli("localhost", $settings->username, $settings->password, $settings->dbName);
if($conn->connect_error) {
    die("Error: " . $conn->connect_error);
}

function handleLogin($username, $password, $conn) {
    $stmt = $conn->prepare("SELECT user_id, password_hash FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $password_hash);
        $stmt->fetch();
        
        if (password_verify($password, $password_hash)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            return true;
        } else {
            return 'Invalid username or password.';
        }
    } else {
        return 'Invalid username or password.';
    }
}

function handleSignup($username, $password, $conn) {
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        return 'Username already taken. Please choose another one.';
    }

    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, 'player')");
    $stmt->bind_param("ss", $username, $password_hash);
    
    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['username'] = $username;
        return true;
    } else {
        return 'Error during sign-up. Please try again later.';
    }
}

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: home.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $loginResponse = handleLogin($username, $password, $conn);
    } elseif (isset($_POST['signup'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $signupResponse = handleSignup($username, $password, $conn);
    } elseif (isset($_POST['finish'])) {
        if(isset($_SESSION['user_id'])) {
            $stmt = $conn->prepare("INSERT INTO game_stats (user_id, puzzle_size, time_taken_seconds, moves_count, game_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $_SESSION['user_id'], $_POST['size'], $_POST['time'], $_POST['moves'], date("Y-m-d H:i:s"));
            $stmt->execute();
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <title>Fifteen Puzzle</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <main class="stdLayout">
            <h1>Fifteen Puzzle</h1>
            <div class="flavorText">The goal of the fifteen puzzle is to un-jumble its squares by repeatedly making moves that slide squares into the empty space. How quickly can you solve it?</div>

            <table class="menuSplit">
                <tr>
                    <td>
                        <div id="account-container">
                            <?php
                                if (isset($_SESSION['username'])) {
                                    echo "<h3>Welcome back, " . $_SESSION['username'] . "!</h3>";

                                    $pb3time = -1;
                                    $pb3moves = -1;
                                    $stmt = $conn->prepare("SELECT MIN(gs.time_taken_seconds) AS best_time, MIN(gs.moves_count) AS best_moves FROM game_stats gs WHERE gs.user_id = ? AND gs.puzzle_size = '3' GROUP BY gs.user_id;");
                                    $stmt->bind_param("s", $_SESSION["user_id"]);
                                    $stmt->execute();
                                    $stmt->store_result();
                                    if ($stmt->num_rows > 0) {
                                        $stmt->bind_result($pb3moves, $pb3time);
                                        $stmt->fetch();
                                    }

                                    $pb4time = -1;
                                    $pb4moves = -1;
                                    $stmt = $conn->prepare("SELECT MIN(gs.time_taken_seconds) AS best_time, MIN(gs.moves_count) AS best_moves FROM game_stats gs WHERE gs.user_id = ? AND gs.puzzle_size = '4' GROUP BY gs.user_id;");
                                    $stmt->bind_param("s", $_SESSION["user_id"]);
                                    $stmt->execute();
                                    $stmt->store_result();
                                    if ($stmt->num_rows > 0) {
                                        $stmt->bind_result($pb4moves, $pb4time);
                                        $stmt->fetch();
                                    }

                                    $pb5time = -1;
                                    $pb5moves = -1;
                                    $stmt = $conn->prepare("SELECT MIN(gs.time_taken_seconds) AS best_time, MIN(gs.moves_count) AS best_moves FROM game_stats gs WHERE gs.user_id = ? AND gs.puzzle_size = '5' GROUP BY gs.user_id;");
                                    $stmt->bind_param("s", $_SESSION["user_id"]);
                                    $stmt->execute();
                                    $stmt->store_result();
                                    if ($stmt->num_rows > 0) {
                                        $stmt->bind_result($pb5moves, $pb5time);
                                        $stmt->fetch();
                                    }

                                    ?>
                                    <table class="profileStats">
                                        <tr>
                                            <td>PB</td>
                                            <td>Moves</td>
                                            <td>Time</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <img src="3tile.svg" width="75" height="75">
                                            </td>
                                            <td>
                                                <?php print $pb3time >= 0 ? $pb3time : "---" ?>
                                            </td>
                                            <td>
                                                <?php print $pb3moves >= 0 ? $pb3moves : "---" ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <img src="4tile.svg" width="75" height="75">
                                            </td>
                                            <td>
                                                <?php print $pb4time >= 0 ? $pb4time : "---" ?>
                                            </td>
                                            <td>
                                                <?php print $pb4moves >= 0 ? $pb4moves : "---" ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <img src="5tile.svg" width="75" height="75">
                                            </td>
                                            <td>
                                                <?php print $pb5time >= 0 ? $pb5time : "---" ?>
                                            </td>
                                            <td>
                                                <?php print $pb5moves >= 0 ? $pb5moves : "---" ?>
                                            </td>
                                        </tr>
                                    </table>
                                    <a href="?logout=true" class="miniBtn"><button>Log Out</button></a>
                                    <?php

                                    $stmt = $conn->prepare("SELECT role, banned FROM users WHERE user_id = ?");
                                    $stmt->bind_param("s", $_SESSION["user_id"]);
                                    $stmt->execute();
                                    $stmt->store_result();
                                    $stmt->bind_result($role, $banned);
                                    $stmt->fetch();

                                    if($banned){
                                        echo "<h3 style='color:red;'>You're account has been suspended from leaderboards!</h3>";
                                    }

                                    if($role === "admin"){
                                        echo "<a href='admin.php' class=\"miniBtn\"><button>Admin Dashboard</button></a>";
                                    }

                                } else {
                                    ?>
                                    <h2>Login</h2>
                                    <form method="POST" id="loginForm">
                                        <input type="text" name="username" placeholder="Username" required>
                                        <input type="password" name="password" placeholder="Password" required>
                                        <button type="submit" name="login">Log In</button>

                                        <?php
                                        if (isset($loginResponse) && $loginResponse !== true) {
                                            echo "<p style='color:red;'>$loginResponse</p>";
                                        }
                                        ?>

                                        <h3>Don't have an account? <button type="submit" name="signup">Sign Up</button></h3>
                                    </form>

                                    <?php
                                    if (isset($signupResponse) && $signupResponse !== true) {
                                        echo "<p style='color:red;'>$signupResponse</p>";
                                    }
                                }
                            ?>
                        </div>
                    </td>
                    <td>
                        <div id="leaderboard-container">
                            <h2>Leaderboard</h2>
                            <div class="modalGap">
                                <span>
                                    <input type="radio" id="leaderboard3x3" name="tab">
                                    <label for="leaderboard3x3">3 x 3</label>
                                </span>
                                <span>
                                    <input type="radio" id="leaderboard4x4" name="tab" checked>
                                    <label for="leaderboard4x4">4 x 4</label>
                                </span>
                                <span>
                                    <input type="radio" id="leaderboard5x5" name="tab">
                                    <label for="leaderboard5x5">5 x 5</label>
                                </span>
                            </div>
                            <?php
                                $i = 1;
                                $time3x3 = "<table id=\"time3x3\"><tr><th>Rank</th><th>User</th><th id=\"moveToggle3x3\" class=\"tileHover\">Moves</th><th>Time</th></tr>";
                                $results = $conn->query("SELECT u.username, MIN(gs.time_taken_seconds) AS best_time, MIN(gs.moves_count) AS best_moves FROM users u JOIN game_stats gs ON u.user_id = gs.user_id WHERE u.banned = FALSE AND gs.puzzle_size = '3' GROUP BY u.user_id ORDER BY best_time ASC, best_moves ASC;");
                                if($results->num_rows > 0){
                                    while($row = $results->fetch_assoc()) {
                                        $time3x3 = $time3x3 . "<tr><td>$i</td><td>{$row['username']}</td><td>{$row['best_moves']}</td><td>{$row['best_time']}</td></tr>";
                                        $i++;
                                    }
                                } else {
                                    $time3x3 = $time3x3 . "<tr><td>---</td><td>---</td><td>---</td><td>---</td></tr>";
                                }
                                $time3x3 = $time3x3 . "</table>";
                                print $time3x3;

                                $i = 1;
                                $move3x3 = "<table id=\"move3x3\" class=\"hide\"><tr><th>Rank</th><th>User</th><th>Moves</th><th id=\"timeToggle3x3\" class=\"tileHover\">Time</th></tr>";
                                $results = $conn->query("SELECT u.username, MIN(gs.time_taken_seconds) AS best_time, MIN(gs.moves_count) AS best_moves FROM users u JOIN game_stats gs ON u.user_id = gs.user_id WHERE u.banned = FALSE AND gs.puzzle_size = '3' GROUP BY u.user_id ORDER BY best_moves ASC, best_time ASC;");
                                if($results->num_rows > 0){
                                    while($row = $results->fetch_assoc()) {
                                        $move3x3 = $move3x3 . "<tr><td>$i</td><td>{$row['username']}</td><td>{$row['best_moves']}</td><td>{$row['best_time']}</td></tr>";
                                        $i++;
                                    }
                                } else {
                                    $move3x3 = $move3x3 . "<tr><td>---</td><td>---</td><td>---</td><td>---</td></tr>";
                                }
                                $move3x3 = $move3x3 . "</table>";
                                print $move3x3;

                                $i = 1;
                                $time4x4 = "<table id=\"time4x4\"><tr><th>Rank</th><th>User</th><th id=\"moveToggle4x4\" class=\"tileHover\">Moves</th><th>Time</th></tr>";
                                $results = $conn->query("SELECT u.username, MIN(gs.time_taken_seconds) AS best_time, MIN(gs.moves_count) AS best_moves FROM users u JOIN game_stats gs ON u.user_id = gs.user_id WHERE u.banned = FALSE AND gs.puzzle_size = '4' GROUP BY u.user_id ORDER BY best_time ASC, best_moves ASC;");
                                if($results->num_rows > 0){
                                    while($row = $results->fetch_assoc()) {
                                        $time4x4 = $time4x4 . "<tr><td>$i</td><td>{$row['username']}</td><td>{$row['best_moves']}</td><td>{$row['best_time']}</td></tr>";
                                        $i++;
                                    }
                                } else {
                                    $time4x4 = $time4x4 . "<tr><td>---</td><td>---</td><td>---</td><td>---</td></tr>";
                                }
                                $time4x4 = $time4x4 . "</table>";
                                print $time4x4;

                                $i = 1;
                                $move4x4 = "<table id=\"move4x4\" class=\"hide\"><tr><th>Rank</th><th>User</th><th>Moves</th><th id=\"timeToggle4x4\" class=\"tileHover\">Time</th></tr>";
                                $results = $conn->query("SELECT u.username, MIN(gs.time_taken_seconds) AS best_time, MIN(gs.moves_count) AS best_moves FROM users u JOIN game_stats gs ON u.user_id = gs.user_id WHERE u.banned = FALSE AND gs.puzzle_size = '4' GROUP BY u.user_id ORDER BY best_moves ASC, best_time ASC;");
                                if($results->num_rows > 0){
                                    while($row = $results->fetch_assoc()) {
                                        $move4x4 = $move4x4 . "<tr><td>$i</td><td>{$row['username']}</td><td>{$row['best_moves']}</td><td>{$row['best_time']}</td></tr>";
                                        $i++;
                                    }
                                } else {
                                    $move4x4 = $move4x4 . "<tr><td>---</td><td>---</td><td>---</td><td>---</td></tr>";
                                }
                                $move4x4 = $move4x4 . "</table>";
                                print $move4x4;

                                $i = 1;
                                $time5x5 = "<table id=\"time5x5\"><tr><th>Rank</th><th>User</th><th id=\"moveToggle5x5\" class=\"tileHover\">Moves</th><th>Time</th></tr>";
                                $results = $conn->query("SELECT u.username, MIN(gs.time_taken_seconds) AS best_time, MIN(gs.moves_count) AS best_moves FROM users u JOIN game_stats gs ON u.user_id = gs.user_id WHERE u.banned = FALSE AND gs.puzzle_size = '5' GROUP BY u.user_id ORDER BY best_time ASC, best_moves ASC;");
                                if($results->num_rows > 0){
                                    while($row = $results->fetch_assoc()) {
                                        $time5x5 = $time5x5 . "<tr><td>$i</td><td>{$row['username']}</td><td>{$row['best_moves']}</td><td>{$row['best_time']}</td></tr>";
                                        $i++;
                                    }
                                } else {
                                    $time5x5 = $time5x5 . "<tr><td>---</td><td>---</td><td>---</td><td>---</td></tr>";
                                }
                                $time5x5 = $time5x5 . "</table>";
                                print $time5x5;

                                $i = 1;
                                $move5x5 = "<table id=\"move5x5\" class=\"hide\"><tr><th>Rank</th><th>User</th><th>Moves</th><th id=\"timeToggle5x5\" class=\"tileHover\">Time</th></tr>";
                                $results = $conn->query("SELECT u.username, MIN(gs.time_taken_seconds) AS best_time, MIN(gs.moves_count) AS best_moves FROM users u JOIN game_stats gs ON u.user_id = gs.user_id WHERE u.banned = FALSE AND gs.puzzle_size = '5' GROUP BY u.user_id ORDER BY best_moves ASC, best_time ASC;");
                                if($results->num_rows > 0){
                                    while($row = $results->fetch_assoc()) {
                                        $move5x5 = $move5x5 . "<tr><td>$i</td><td>{$row['username']}</td><td>{$row['best_moves']}</td><td>{$row['best_time']}</td></tr>";
                                        $i++;
                                    }
                                } else {
                                    $move5x5 = $move5x5 . "<tr><td>---</td><td>---</td><td>---</td><td>---</td></tr>";
                                }
                                $move5x5 = $move5x5 . "</table>";
                                print $move5x5;
                            ?>
                        </div>
                    </td>
                </tr>
            </table>

            <form class="gameStartForm" method="post" action="game.php">
                <div class="modalGap">
                    <span>
                        <input type="radio" id="board3x3" name="size" value="3">
                        <label for="board3x3">3 x 3</label>
                    </span>
                    <span>
                        <input type="radio" id="board4x4" name="size" value="4" checked>
                        <label for="board4x4">4 x 4</label>
                    </span>
                    <span>
                        <input type="radio" id="board5x5" name="size" value="5">
                        <label for="board5x5">5 x 5</label>
                    </span>
                </div>
                <button type="submit">Start Game</button>
            </form>

            <div class="flavorText"><?php 
                $trivia = $conn->query("SELECT triviaText FROM trivia ORDER BY RAND() LIMIT 1;");
                print $trivia->fetch_assoc()["triviaText"];
            ?></div>
        </main>
        <div id="validators">
            <a href="http://validator.w3.org/check/referer"><img src="valid-xhtml11.png"></a><br>
            <a href="http://jigsaw.w3.org/css-validator/check/referer"><img src="valid-css.png"></a>
        </div>
        <script src="home.js"></script>
    </body>
</html>
<?php $conn->close(); ?>