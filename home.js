document.addEventListener('DOMContentLoaded', () => {
    const radios = document.querySelectorAll('input[name="tab"]');
    
    const time3x3 = document.getElementById("time3x3");
    const move3x3 = document.getElementById("move3x3");
    const time4x4 = document.getElementById("time4x4");
    const move4x4 = document.getElementById("move4x4");
    const time5x5 = document.getElementById("time5x5");
    const move5x5 = document.getElementById("move5x5");

    const hideAllTables = () => {
        time3x3.style.display = 'none';
        move3x3.style.display = 'none';
        time4x4.style.display = 'none';
        move4x4.style.display = 'none';
        time5x5.style.display = 'none';
        move5x5.style.display = 'none';
    };

    const showSelectedTable = () => {
        hideAllTables();
        if (document.getElementById("leaderboard3x3").checked) {
            time3x3.style.display = 'table';
            move3x3.style.display = 'table';
        } else if (document.getElementById("leaderboard4x4").checked) {
            time4x4.style.display = 'table';
            move4x4.style.display = 'table';
        } else if (document.getElementById("leaderboard5x5").checked) {
            time5x5.style.display = 'table';
            move5x5.style.display = 'table';
        }
    };

    radios.forEach(radio => {
        radio.addEventListener('change', () => {
            showSelectedTable();
        });
    });

    document.getElementById("moveToggle3x3").addEventListener("click", () => {
        time3x3.classList.add("hide");
        move3x3.classList.remove("hide");
    });
    document.getElementById("timeToggle3x3").addEventListener("click", () => {
        time3x3.classList.remove("hide");
        move3x3.classList.add("hide");
    });

    document.getElementById("moveToggle4x4").addEventListener("click", () => {
        time4x4.classList.add("hide");
        move4x4.classList.remove("hide");
    });
    document.getElementById("timeToggle4x4").addEventListener("click", () => {
        time4x4.classList.remove("hide");
        move4x4.classList.add("hide");
    });

    document.getElementById("moveToggle5x5").addEventListener("click", () => {
        time5x5.classList.add("hide");
        move5x5.classList.remove("hide");
    });
    document.getElementById("timeToggle5x5").addEventListener("click", () => {
        time5x5.classList.remove("hide");
        move5x5.classList.add("hide");
    });

    showSelectedTable();
});
