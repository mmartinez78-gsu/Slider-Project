CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    role ENUM('player', 'admin') NOT NULL DEFAULT 'player',
    registration_date DATETIME NOT NULL,
    last_login DATETIME
);

CREATE TABLE IF NOT EXISTS background_images (
    image_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    image_name VARCHAR(100) NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    uploaded_by_user_id INT UNSIGNED,
    FOREIGN KEY (uploaded_by_user_id) REFERENCES users(user_id)
        ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS game_stats (
    stat_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    puzzle_size VARCHAR(10) NOT NULL,
    time_taken_seconds INT NOT NULL,
    moves_count INT NOT NULL,
    background_image_id INT UNSIGNED,
    win_status BOOLEAN NOT NULL,
    game_date DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (background_image_id) REFERENCES background_images(image_id)
        ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS user_preferences (
    preference_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    default_puzzle_size VARCHAR(10) DEFAULT '4x4',
    preferred_background_image_id INT UNSIGNED,
    sound_enabled BOOLEAN DEFAULT TRUE,
    animations_enabled BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (preferred_background_image_id) REFERENCES background_images(image_id)
        ON DELETE SET NULL ON UPDATE CASCADE
);
