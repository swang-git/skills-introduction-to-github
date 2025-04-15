CREATE DEFINER=`swang`@`%` PROCEDURE `get_player_club_index_and_progress`(tid INT, pid INT)
BEGIN

DECLARE year INT;
DECLARE tmnt_start DATETIME;
DECLARE calc_start DATETIME;
DECLARE y1st_tmnt_start DATETIME;
DECLARE y1st_calc_start DATETIME;
DECLARE num_count INT;
DECLARE num_games INT;
DECLARE player varchar(64);
DECLARE club_index DECIMAL(3,1) DEFAULT null;
DECLARE club_index_yb DECIMAL(3,1) DEFAULT null;
DECLARE year_progress DECIMAL(3,1) DEFAULT null;

IF tid = 0 THEN
	select id into tid from tournaments where status = 'A' and game_id in (1, 2, 3, 4, 6) and start_at < now() order by id desc limit 1;
END IF;

SELECT DATE_ADD(start_at, INTERVAL -1 day) INTO tmnt_start FROM tournaments tt WHERE tt.id = tid AND tt.status = 'A';  
set year = DATE_FORMAT(tmnt_start, '%Y');
set calc_start = DATE_ADD(tmnt_start, INTERVAL -790 day);  -- roughly 26 months back

SELECT COUNT(*)
    FROM tplayers tp
    JOIN tournaments tt ON tp.tournament_id = tt.id 
    WHERE tp.player_id = pid  AND tp.status = 'A' AND tt.status ='A'
        AND tt.start_at BETWEEN calc_start AND tmnt_start
        AND tp.gross_score > 0
        -- AND tp.idx_diff > 0
        AND tt.game_id in (1, 2, 3, 4, 6)
        INTO num_count;
SET num_games = CEIL(num_count / 2);

SELECT AVG(idx_diff) 
    FROM (SELECT tp.idx_diff
            FROM tplayers tp
            JOIN tournaments tt ON tp.tournament_id = tt.id
            WHERE tp.player_id = pid AND tp.status = 'A' AND tt.status ='A' 
                AND tt.start_at BETWEEN calc_start AND tmnt_start
                AND tp.gross_score > 0
                AND tt.game_id in (1, 2, 3, 4, 6)
            ORDER BY tp.idx_diff LIMIT num_games
    ) q INTO club_index;


SELECT tt.start_at INTO y1st_tmnt_start FROM tournaments tt WHERE tt.year = year AND tt.status = 'A' AND tt.game_id = 1;
set y1st_calc_start = DATE_ADD(y1st_tmnt_start, INTERVAL -790 day);    -- roughly 26 months back

SELECT COUNT(*)
    FROM tplayers tp
    JOIN tournaments tt ON tp.tournament_id = tt.id 
    WHERE tp.player_id = pid AND tp.status = 'A' AND tt.status ='A'
        AND tt.start_at BETWEEN y1st_calc_start AND y1st_tmnt_start
		AND tp.gross_score > 0
        -- AND tp.idx_diff > 0
        AND tt.game_id in (1, 2, 3, 4, 6)
        INTO num_count;
SET num_games = CEIL(num_count / 2);
-- select num_games;

SELECT AVG(idx_diff) 
    FROM (SELECT tp.idx_diff
            FROM tplayers tp
            JOIN tournaments tt ON tp.tournament_id = tt.id
            WHERE tp.player_id = pid AND tp.status = 'A' AND tt.status ='A' 
                AND tt.start_at BETWEEN y1st_calc_start AND y1st_tmnt_start
				AND tp.gross_score > 0
                -- AND tp.idx_diff > 0
                AND tt.game_id in (1, 2, 3, 4, 6)
            ORDER BY tp.idx_diff LIMIT num_games
    ) tb INTO club_index_yb;

IF club_index is NULL or club_index_yb is NULL THEN
    SET year_progress = NULL;
ELSE
    SET year_progress = club_index_yb - club_index;
END IF;
-- SELECT club_index, year_progress, num_games, calc_start, tmnt_start, y1st_calc_start, y1st_tmnt_start;
SELECT IF(club_index > 0, club_index, NULL) as club_index, IF(year_progress > 0, year_progress, NULL) as year_progress; -- , num_games, calc_start, tmnt_start, y1st_calc_start, y1st_tmnt_start;

END
