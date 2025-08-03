SET @db_name = DATABASE();

SET FOREIGN_KEY_CHECKS = 0;

SET @tables_to_truncate = 'background_images,game_stats,user_preferences,users';

DELIMITER $$

CREATE PROCEDURE truncate_if_exists(IN db_name VARCHAR(64), IN table_list TEXT)
BEGIN
  DECLARE done INT DEFAULT FALSE;
  DECLARE tbl_name VARCHAR(64);
  DECLARE cur CURSOR FOR
    SELECT table_name
    FROM information_schema.tables
    WHERE FIND_IN_SET(table_name, table_list) > 0
      AND table_schema = db_name;
  DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

  OPEN cur;
  read_loop: LOOP
    FETCH cur INTO tbl_name;
    IF done THEN
      LEAVE read_loop;
    END IF;

    SET @stmt = CONCAT('TRUNCATE TABLE `', db_name, '`.`', tbl_name, '`');
    PREPARE stmt FROM @stmt;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END LOOP;
  CLOSE cur;
END $$

DELIMITER ;

CALL truncate_if_exists(@db_name, @tables_to_truncate);

DROP PROCEDURE IF EXISTS truncate_if_exists;

SET FOREIGN_KEY_CHECKS = 1;
