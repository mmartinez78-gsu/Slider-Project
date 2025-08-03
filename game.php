<?php
session_start();
$size = 4;
if(isset($_POST["size"])){
    $size = intval($_POST["size"]);
}

$settingsJSON = file_get_contents(__DIR__ . "/databaseSettings_mysql.json");
$settings = json_decode($settingsJSON);
$conn = new mysqli("localhost", $settings->username, $settings->password, $settings->dbName);
if($conn->connect_error) {
    die("Error: " . $conn->connect_error);
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
            <a href="home.php" class="returnBtn"><button>Return Home</button></a>
            <div id="timer" class="timer">0:00</div>

            <img id="sourceImage" src="<?php 
                $image = $conn->query("SELECT image_url FROM background_images WHERE is_active = TRUE ORDER BY RAND() LIMIT 1;");
                print $image->fetch_assoc()["image_url"];
            ?>" alt="source"> 

            <h1>Fifteen Puzzle</h1>
            <div class="flavorText">The goal of the fifteen puzzle is to un-jumble its squares by repeatedly making moves that slide squares into the empty space. How quickly can you solve it?</div>

            <table data-size="<?php print $size; ?>" id="gameboard" class="gameBoard">
                <?php
                    for($y = 0; $y < $size; $y++){
                        print "<tr>\n";
                        for($x = 0; $x < $size; $x++){
                            $tileNum = $x + ($y * $size) + 1;
                            print "<td class=\"gameBoard\"><div class=\"tile tileHover\" id=\"tile$tileNum\"></div></td>\n";
                        }
                        print "</tr>\n";
                    }
                ?>
            </table>
            <div class="flavorText"><?php 
                $trivia = $conn->query("SELECT triviaText FROM trivia ORDER BY RAND() LIMIT 1;");
                print $trivia->fetch_assoc()["triviaText"];
        ?></main>
        <div id="validators">
            <a href="http://validator.w3.org/check/referer"><img src="valid-xhtml11.png"></a><br>
            <a href="http://jigsaw.w3.org/css-validator/check/referer"><img src="valid-css.png"></a>
        </div>
        <script src="game.js"></script>
    </body>
</html>
<?php $conn->close(); ?>