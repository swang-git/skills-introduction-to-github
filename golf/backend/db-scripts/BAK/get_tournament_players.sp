CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_tournament_players`(tid INT, pid INT)
BEGIN
SELECT tp.id, concat(p.lastname, ', ', p.firstname) as player, p.gender, tp.hole19 as dinner, 
	tp.gross_score as GSC, tp.player_id as playerId, tp.pos, tp.status as dinner_only,
    DATE_FORMAT(tp.created_at, '%Y-%m-%d %H:%i') AS 'registered at', get_poy_points(p.id, tid) as poy, get_net_poy(p.id, tid) as npy
        
FROM tplayers tp
JOIN tournaments tt ON tp.tournament_id = tt.id
JOIN players p ON tp.player_id = p.id


WHERE 
tp.status in ('A', 'Y')
AND tp.tournament_id = tid 
AND p.status = 'A'
AND CASE WHEN pid>0 THEN tp.player_id = pid ELSE tp.player_id>0 END 
AND CASE WHEN tt.start_at >= DATE_FORMAT(now(), '%Y-%m-%d') THEN tp.player_id>0 ELSE tp.gross_score > 0 END
-- ORDER BY t.gross_score;
ORDER BY player;
END

