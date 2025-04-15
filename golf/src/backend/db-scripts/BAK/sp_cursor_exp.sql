use Golf_dev;
drop procedure if exists player_list
DELIMITER $$
 
CREATE PROCEDURE player_list (INOUT email_list varchar(max))
BEGIN
 
DECLARE v_finished INTEGER DEFAULT 0;
DECLARE v_player varchar(100) DEFAULT "";
 
 -- declare cursor for players
DEClARE player_cursor CURSOR FOR 
SELECT id, lastname, firstname FROM players;
 
 -- declare NOT FOUND handler
 DECLARE CONTINUE HANDLER 
    FOR NOT FOUND SET v_finished = 1;
 
 OPEN player_cursor;
 
 get_player: LOOP
 
 FETCH player_cursor INTO v_player;
 
 IF v_finished = 1 THEN 
    LEAVE get_player;
 END IF;
 
 -- build email list
 SET player_list = CONCAT(v_player,";", player_list);
 
 END LOOP get_player;
 
 CLOSE player_cursor;
 
END$$
 
DELIMITER ;
-- You can test the player_list stored procedure using the following script:
/*
SET @player_list = "";
CALL player_list(@player_list);
SELECT @player_list;
*/