CREATE DEFINER=`swang`@`%` PROCEDURE `get_tplayer_scores`(playerId INT)
BEGIN
SELECT CONCAT(tt.year, '-', tt.game_id) AS tournament, course_id,
tp.gross_score FROM tplayers tp 
JOIN tournaments tt ON tp.tournament_id = tt.id 
WHERE tp.player_id = playerId AND tp.gross_score > 0 and tp.status = 'A' and tt.status = 'A'
ORDER BY tt.year, tt.game_id;
END
