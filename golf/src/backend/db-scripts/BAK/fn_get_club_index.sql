
USE `Golf_dev`;
DROP function IF EXISTS `get_club_index`;

DELIMITER $$
USE `Golf_dev`$$

CREATE DEFINER=`swang`@`%` FUNCTION `get_club_index`(pid INT, tournamentId INT) RETURNS decimal(3,1)
    DETERMINISTIC READS SQL DATA
BEGIN
	DECLARE tmnt_start_at DATETIME;
	DECLARE clubIndex DECIMAL(3,1);
    DECLARE num_count INT;
    declare game_count INT;
    
    IF (tournamentId > 0) THEN
		SELECT start_at FROM tournaments WHERE id = tournamentId AND status = 'A' INTO tmnt_start_at;
    END IF;
    
    SELECT COUNT(*)
		FROM tplayers_tmnt tp
        JOIN tournaments tt ON tp.tournament_id = tt.id 
		WHERE player_id = pid  
			AND tt.start_at < tmnt_start_at 
            AND tt.start_at > DATE_ADD(tmnt_start_at, INTERVAL -760 day)  -- 2 year + 1 month
            AND tp.gross_score > 0
            AND (tt.game_id < 5 or tt.game_id = 6)
            INTO num_count;
    SET game_count = CEIL(num_count / 2);

	SELECT AVG(idx_diff) 
    FROM (
		select idx_diff
		FROM tplayers tp
		JOIN tournaments tt ON tp.tournament_id = tt.id
		WHERE tp.status = 'A' AND tt.status ='A' 
			AND tp.gross_score > 0
            AND tp.idx_diff IS NOT NULL
            AND (tt.game_id < 5 or tt.game_id = 6)
            AND tp.player_id = pid 
            AND tt.start_at <  tmnt_start_at AND tt.start_at > DATE_ADD(tmnt_start_at, INTERVAL -760 day)
		ORDER BY tp.idx_diff LIMIT game_count
	) q INTO clubIndex;

	RETURN (clubIndex);
END$$
delimiter ;