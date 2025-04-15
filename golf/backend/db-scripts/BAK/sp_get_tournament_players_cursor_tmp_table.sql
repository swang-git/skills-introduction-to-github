USE princeton_golf;
DROP PROCEDURE IF EXISTS get_tournament_players;

DELIMITER $$
USE princeton_golf$$

CREATE DEFINER=`pccadmin`@`%` PROCEDURE get_tournament_players(tid INT, pid INT)
BEGIN

CALL create_tmp_table_poy_point(tid, -1);     -- the poy_point  for current tournament
CALL create_tmp_table_club_index(tid, False); -- the club_index for current tournament
CALL create_tmp_table_club_index(tid, TRUE);  -- the club_index for year's 1st tournament

SELECT p.id as pid, tp.id, concat(p.lastname, ', ', p.firstname) as player, p.gender, tp.hole19 as dinner, 
        tp.gross_score as GSC, tp.player_id as playerId, tp.pos, CASE WHEN tp.hole19 = 'Don' THEN 'Yes' END as dinner_only,
        DATE_FORMAT(tp.created_at, '%Y-%m-%d %H:%i') AS signed_at, 
        py.poy, (ybcx.club_index - cx.club_index) as npy

FROM tplayers tp
JOIN tournaments tt ON tp.tournament_id = tt.id
JOIN players p ON tp.player_id = p.id
LEFT JOIN tmp_poy_point py ON py.id = p.id
LEFT JOIN tmp_club_index cx ON cx.pid = p.id
LEFT JOIN tmp_club_index_yb ybcx ON ybcx.pid = p.id

WHERE tp.status in ('A', 'Y') AND tp.tournament_id = tid AND p.status = 'A' AND CASE WHEN pid>0 THEN tp.player_id = pid ELSE tp.player_id>0 END 
AND CASE WHEN tt.start_at >= DATE_FORMAT(now(), '%Y-%m-%d') THEN tp.player_id>0 ELSE tp.gross_score > 0 END
ORDER BY player;
END$$

DELIMITER ;

CALL get_tournament_players(68, -1);

