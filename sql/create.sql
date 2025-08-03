CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    banned BOOLEAN NOT NULL DEFAULT FALSE,
    role ENUM('player', 'admin') NOT NULL DEFAULT 'player'
);

CREATE TABLE IF NOT EXISTS background_images (
    image_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_url VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS game_stats (
    stat_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    puzzle_size VARCHAR(10) NOT NULL,
    time_taken_seconds INT NOT NULL,
    moves_count INT NOT NULL,
    game_date DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS trivia (
    trivia_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    triviaText varchar(1024) NOT NULL
);
