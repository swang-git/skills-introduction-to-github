use Golf_dev;
delimiter $$
DROP procedure IF EXISTS `tt_club_index`;
use Golf_dev$$

CREATE DEFINER=`swang`@`%` procedure `tt_club_index`(tid INT, p_id INT)
BEGIN
DECLARE tmnt_start_at DATETIME;
DECLARE calc_idx_start_at DATETIME;
-- DECLARE clubIndex DECIMAL(3,1);
DECLARE num_count INT;
DECLARE num_games INT;

SELECT start_at FROM tournaments WHERE id = tid AND status = 'A' INTO tmnt_start_at;
SET calc_idx_start_at =  DATE_ADD(tmnt_start_at, INTERVAL -760 day);  -- last 2 year + 1 month from this tournament(tid)
-- set how many tournaments to use for calculating club index - currently using the average of the half best of tournaments and playoff in the past 2 years from this tournament(tid) exclusive

SET @@session.sql_notes = 0;

DROP TEMPORARY TABLE IF EXISTS tmp_club_index;
CREATE TEMPORARY TABLE tmp_club_index AS  
    SELECT tp.id as pid, cidx
    FROM tplayers tp, (
        SELECT COUNT(*)
            FROM tplayers tp
            JOIN tournaments tt ON tp.tournament_id = tt.id 
            WHERE player_id = pid  
                AND tt.start_at > calc_idx_start_at
                AND tt.start_at < tmnt_start_at -- exclude this tournament(tid) 
                AND tp.gross_score > 0
                AND (tt.game_id < 5 or tt.game_id = 6)
                INTO num_count
        SET num_games = CEIL(num_count / 2);

        SELECT p.id as pid, p.lastname, p.firstname, AVG(tp.idx_diff) as cidx --, count(*) as count
        FROM tplayers tp
        JOIN tournaments tt ON tp.tournament_id = tt.id
        JOIN players p ON tp.player_id = p.id
        WHERE tp.status = 'A' AND tt.status ='A' 
            AND tp.gross_score > 0
            AND tp.idx_diff IS NOT NULL
            AND (tt.game_id < 5 or tt.game_id = 6)
            -- AND tp.player_id = p_id 
            AND tt.start_at > calc_idx_start_at AND tt.start_at < tmnt_start_at
        ORDER BY tp.idx_diff LIMIT num_games;
    ) pcidx
    GROUP BY p.id;

SET @@session.sql_notes = 1;

select * from tmp_club_index order by pid, cidx;

END$$
delimiter ;
