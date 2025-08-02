<?php 
$size = 4;
if(isset($_POST["size"])){
    $size = intval($_POST["size"]);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <!-- Any image may be placed in src provided it is perfectly square -->
        <img id="sourceImage" src="test.jpg" alt="source"> 
        <table data-size="<?php print $size; ?>" id="gameboard">
            <?php
                for($y = 0; $y < $size; $y++){
                    print "<tr>\n";
                    for($x = 0; $x < $size; $x++){
                        $tileNum = $x + ($y * 4) + 1;
                        print "<td><div class=\"tile tileHover\" id=\"tile$tileNum\"></div></td>\n";
                    }
                    print "</tr>\n";
                }
            ?>
        </table>
        <script src="game.js"></script>
    </body>
</html>