CREATE DEFINER=`swang`@`%` PROCEDURE `get_player_club_index`(tid INT, pid INT)
BEGIN

DECLARE year INT;
DECLARE tmnt_start DATETIME;
DECLARE num_count INT;
DECLARE num_games INT;
DECLARE player varchar(64);
DECLARE calc_start DATETIME;
DECLARE club_index DECIMAL(3,1) DEFAULT null;

SELECT DATE_ADD(start_at, INTERVAL -1 day) INTO tmnt_start FROM tournaments tt WHERE tt.id = tid AND tt.status = 'A';  
set year = DATE_FORMAT(tmnt_start, '%Y');

set calc_start = DATE_ADD(tmnt_start, INTERVAL -760 day);  

SELECT COUNT(*)
    FROM tplayers tp
    JOIN tournaments tt ON tp.tournament_id = tt.id 
    WHERE player_id = pid  AND tp.status = 'A' AND tt.status ='A'
        AND tt.start_at BETWEEN calc_start AND tmnt_start
        AND tp.gross_score > 0
        AND (tt.game_id < 5 or tt.game_id = 6)
        INTO num_count;
SET num_games = CEIL(num_count / 2);

SELECT AVG(idx_diff) 
    FROM (SELECT tp.idx_diff
            FROM tplayers tp
            JOIN tournaments tt ON tp.tournament_id = tt.id
            WHERE tp.player_id = pid AND tp.status = 'A' AND tt.status ='A' 
                AND tt.start_at BETWEEN calc_start AND tmnt_start
                AND tp.gross_score > 0
                AND (tt.game_id < 5 or tt.game_id = 6)
                AND tp.idx_diff > 0
            ORDER BY tp.idx_diff LIMIT num_games
    ) q INTO club_index;
SELECT club_index;
END
