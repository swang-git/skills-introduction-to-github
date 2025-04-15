USE Golf_dev;
DROP PROCEDURE IF EXISTS get_club_index;
DELIMITER $$

USE Golf_dev$$

CREATE DEFINER=`swang`@`%` PROCEDURE `get_club_index`(tid INT, pid INT, net_poy BOOL)
BEGIN
DECLARE year INT;
DECLARE tmnt_start_at DATETIME;
DECLARE num_count INT;
DECLARE num_games INT;
DECLARE begin_cidx_calc DATETIME;
DECLARE club_index DECIMAL(3,1);
DECLARE year_begin_club_index DECIMAL(3,1);

select tt.start_at from tournaments tt where tt.id = tid and tt.status = 'A' into tmnt_start_at;
set year = DATE_FORMAT(tmnt_start_at, '%Y');
set begin_cidx_calc = DATE_ADD(tmnt_start_at, INTERVAL -760 day);  -- 2 year + 1 month

SELECT COUNT(*)
		FROM tplayers tp
        JOIN tournaments tt ON tp.tournament_id = tt.id 
		WHERE player_id = pid 
			AND tt.start_at >= begin_cidx_calc 
            AND tt.start_at < tmnt_start_at
            AND tp.gross_score > 0
            AND (tt.game_id < 5 or tt.game_id = 6)
            INTO num_count;
SET num_games = CEIL(num_count / 2);

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
            AND tt.start_at >= begin_cidx_calc AND tt.start_at < tmnt_start_at
		ORDER BY tp.idx_diff LIMIT num_games
	) q into club_index;

-- IF net_poy = TRUE
-- BEGIN
--     SELECT start_at into tmnt_start_at from tournament where status = 'A' and game_id = 1 and DATE_FORMAT(tmnt_start_at, '%Y') = year;
--     set begin_cidx_calc = DATE_ADD(tmnt_start_at, INTERVAL -760 day);  -- 2 year + 1 month

-- END

END$$
DELIMITER ;

-- call get_club_index(69, 29);