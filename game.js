var moves = 0;
var time = 0;

var wait = true;
var win = false;

const size = document.getElementById("gameboard").dataset.size;

const gameArray = Array.from({length: size}, () => new Array(size).fill(0));
for(y = 0; y < size; y++){
    for(x = 0; x < size; x++){
        gameArray[y][x] = (y * size) + x + 1;
    }
}
gameArray[size - 1][size - 1] = 0

const finishedArray = JSON.stringify(gameArray);
const tileArray = Array((size ** 2) - 1);
const finalTile = document.getElementById(`tile${size ** 2}`);
finalTile.style.display = "none";

const img = document.getElementById("sourceImage");

const imageWait = new Promise((resolve, _reject) => {
    if (img.complete) {
      resolve();
    } else {
      img.onload = () => resolve();
      img.onerror = () => window.location.reload(true);
    }
});

imageWait.then(() => {
const tileImages = splitImage(document.getElementById("sourceImage"), size);

// Tile UI Initialization
for(i = 0; i < (size ** 2) - 1; i++){
    tileArray[i] = document.getElementById(`tile${i + 1}`);
    let index = i + 1;

    const numberText = document.createElement('div');
    numberText.innerText = String(index);
    numberText.classList.add("tileOverlay");
    tileArray[i].appendChild(numberText);
    
    tileArray[i].style.backgroundImage = `url(${tileImages[i].src})`;
    tileArray[i].addEventListener("click", () => {
        if(wait) return;
        move(index, true);
        if(win){
            finalTile.style.display = "block";
            finalTile.classList.remove("tileHover");
            tileArray[index - 1].addEventListener("transitionend", () => {
                tileArray.forEach(element => {
                    element.classList.remove("tileHover");
                });

                // ------------------------
                // TODO: add win logic here
                // ------------------------

                // alert(`You win!\nFinished with: ${moves} moves\nFinal time: ${time} seconds`);

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = "home.php";
                form.style.display = 'none';

                const sizeField = document.createElement('input');
                sizeField.type = 'hidden';
                sizeField.name = 'size';
                sizeField.value = String(size);
                form.appendChild(sizeField);

                const timeField = document.createElement('input');
                timeField.type = 'hidden';
                timeField.name = 'time';
                timeField.value = String(time);
                form.appendChild(timeField);

                const movesField = document.createElement('input');
                movesField.type = 'hidden';
                movesField.name = 'moves';
                movesField.value = String(moves);
                form.appendChild(movesField);

                const finishField = document.createElement('input');
                finishField.type = 'hidden';
                finishField.name = 'finish';
                finishField.value = "1";
                form.appendChild(finishField);

                document.body.appendChild(form);

                form.submit();
            });
        }
    });
}

const numberText = document.createElement('div');
numberText.innerText = String(size ** 2);
numberText.classList.add("tileOverlay");
finalTile.appendChild(numberText);
finalTile.style.backgroundImage = `url(${tileImages[(size ** 2) - 1].src})`;

// Shuffle
const shuffleAmount = randomInt(1000, 10000);
for(i = 0; i < shuffleAmount; i++){
    let emptyY = gameArray.findIndex(row => row.includes(0));
    let emptyX = gameArray[emptyY].indexOf(0);
    let viableIndicies = [];

    for(y = 0; y < size; y++){
        if(gameArray[y][emptyX] != 0) viableIndicies.push(gameArray[y][emptyX]);
    }

    for(x = 0; x < size; x++){
        if(gameArray[emptyY][x] != 0) viableIndicies.push(gameArray[emptyY][x]);
    }

    move(viableIndicies[randomInt(0, viableIndicies.length - 1)], false);
}

wait = false;
gameTimer();


});


// ---------
// Functions
// ---------

/**
 * @param {number} min 
 * @param {number} max 
 */
function randomInt(min, max){
    return Math.floor(Math.random() * (max - min + 1)) + min;
}

/**
 * @param {number} index 
 * @param {boolean} countMove 
 */
function move(index, countMove) {
    const clickedY = gameArray.findIndex(row => row.includes(index));
    const clickedX = gameArray[clickedY].indexOf(index);

    const emptyY = gameArray.findIndex(row => row.includes(0));
    const emptyX = gameArray[emptyY].indexOf(0);

    const subArray = [];

    if (clickedX == emptyX) {
        const col = clickedX;
        const from = Math.min(clickedY, emptyY);
        const to = Math.max(clickedY, emptyY);

        for (let y = from; y <= to; y++) {
            subArray.push(gameArray[y][col]);
        }

        const direction = clickedY < emptyY ? 1 : -1;
        for (let y = emptyY; y != clickedY; y -= direction) {
            gameArray[y][col] = gameArray[y - direction][col];
        }

        gameArray[clickedY][col] = 0;

        if(subArray[0] == 0){
            // Up
            subArray.splice(0, 1)
            subArray.forEach(element => {
                translate(tileArray[element - 1], 0, -100);
                if(countMove) moves++;
            });
        } else {
            // Down
            subArray.splice(-1, 1)
            subArray.forEach(element => {
                translate(tileArray[element - 1], 0, 100);
                if(countMove) moves++;
            });
        }
    } else if (clickedY == emptyY) {
        const row = clickedY;
        const from = Math.min(clickedX, emptyX);
        const to = Math.max(clickedX, emptyX);

        for (let x = from; x <= to; x++) {
            subArray.push(gameArray[row][x]);
        }

        const direction = clickedX < emptyX ? 1 : -1;
        for (let x = emptyX; x != clickedX; x -= direction) {
            gameArray[row][x] = gameArray[row][x - direction];
        }

        gameArray[row][clickedX] = 0;

        if(subArray[0] == 0){
            // Left
            subArray.splice(0, 1)
            subArray.forEach(element => {
                translate(tileArray[element - 1], -100, 0);
                if(countMove) moves++;
            });
        } else {
            // Right
            subArray.splice(-1, 1)
            subArray.forEach(element => {
                translate(tileArray[element - 1], 100, 0);
                if(countMove) moves++;
            });
        }
    } else {
        return;
    }

    if(countMove){
        if(JSON.stringify(gameArray) === finishedArray){
            wait = true;
            win = true;
        }
    }
}

/**
 * @param {HTMLElement} element 
 * @param {number} dX 
 * @param {number} dY
 */
function translate(element, dX, dY) {
    const transform = element.style.transform || "";

    var currentX = 0;
    var currentY = 0;

    const xMatch = transform.match(/translateX\((-?[\d.]+)%\)/);
    if (xMatch) currentX = parseFloat(xMatch[1]);

    const yMatch = transform.match(/translateY\((-?[\d.]+)%\)/);
    if (yMatch) currentY = parseFloat(yMatch[1]);

    const cleanedTransform = transform
        .replace(/translateX\((-?[\d.]+)%\)/, "")
        .replace(/translateY\((-?[\d.]+)%\)/, "")
        .trim();

    element.style.transform =
        `${cleanedTransform} translateX(${currentX + dX}%) translateY(${currentY + dY}%)`.trim();
}

/**
 * @param {HTMLElement} image 
 * @param {number} boardSize 
 * @returns {Array<HTMLImageElement>}
 */
function splitImage(image, boardSize){
    const tileSize = image.naturalWidth / boardSize;
    const canvas = document.createElement('canvas');
    canvas.width = tileSize;
    canvas.height = tileSize;
    const ctx = canvas.getContext('2d');

    const tiles = [];

    for (let y = 0; y < boardSize; y++) {
        const row = [];
        for (let x = 0; x < boardSize; x++) {
            ctx.clearRect(0, 0, tileSize, tileSize);
            ctx.drawImage(
                image,
                x * tileSize,
                y * tileSize,
                tileSize,
                tileSize,
                0,
                0,
                tileSize,
                tileSize
            );

            const tileImg = new Image();
            tileImg.src = canvas.toDataURL();
            row.push(tileImg);
        }
        tiles.push(row);
    }

    return tiles.flat();
}

function gameTimer(){
    let interval = setInterval(timer, 1000);
    function timer() {
        time++;
        let timeDisplay = document.getElementById("timer");
        timeDisplay.innerText = `${Math.floor(time / 60)}:${(time % 60).toString().padStart(2, '0')}`;
        if(wait){
            clearInterval(interval);
        }
    }
}
