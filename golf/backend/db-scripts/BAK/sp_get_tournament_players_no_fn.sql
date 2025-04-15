USE `Golf_dev`;
DROP procedure IF EXISTS `get_tournament_players_no_fn`;

DELIMITER $$
USE `Golf_dev`$$

CREATE DEFINER=`swang`@`%` PROCEDURE `get_tournament_players_no_fn`(tid INT, pid INT)
BEGIN
declare tmnt_start_at datetime;
declare bound INT;
select tt.start_at from tournaments tt where tt.id = tid into tmnt_start_at;
set bound = 17;

SET @@session.sql_notes = 0;
DROP TEMPORARY TABLE IF EXISTS tmp_poy_point;
CREATE TEMPORARY TABLE tmp_poy_point AS  
    select p.id,
    sum(case when bound - gross_rank > 0 then bound - gross_rank else 0 end) -     -- take one lowest gross_rank off i.e. just take 3 tournaments in consideration
    min(case when bound - gross_rank > 0 then bound - gross_rank else 0 end) as poy
    from tplayers tp
    join tournaments tt on tp.tournament_id = tt.id
    join players p on tp.player_id = p.id
    where tt.game_id in (1, 2, 3, 4)
    and tt.year = date_format(tmnt_start_at, '%Y') 
    and tt.start_at <= tmnt_start_at 
    group by p.id;
SET @@session.sql_notes = 1;

CALL tt_club_index_cur(tid, 0); -- the club_index for current tournament
CALL tt_club_index_cur(tid, 1); -- the club_index for year's 1st tournament

SELECT p.id as pid, tp.id, concat(p.lastname, ', ', p.firstname) as player, p.gender, tp.hole19 as dinner, 
        tp.gross_score as GSC, tp.player_id as playerId, tp.pos, CASE WHEN tp.hole19 = 'Don' THEN 'Yes' END as dinner_only,
        DATE_FORMAT(tp.created_at, '%Y-%m-%d %H:%i') AS signed_at, 
        py.poy, (ybcx.club_index - cx.club_index) as npy

FROM tplayers tp
JOIN tournaments tt ON tp.tournament_id = tt.id
JOIN players p ON tp.player_id = p.id
LEFT JOIN tmp_poy_point py ON py.id = p.id
LEFT JOIN tmp_club_index cx ON cx.pid = p.id
LEFT JOIN tmp_yb_club_index ybcx ON ybcx.pid = p.id

WHERE tp.status in ('A', 'Y') AND tp.tournament_id = tid AND p.status = 'A' AND CASE WHEN pid>0 THEN tp.player_id = pid ELSE tp.player_id>0 END 
AND CASE WHEN tt.start_at >= DATE_FORMAT(now(), '%Y-%m-%d') THEN tp.player_id>0 ELSE tp.gross_score > 0 END
ORDER BY p.id;


END$$
DELIMITER ;
