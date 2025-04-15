CREATE DEFINER=`swang`@`%` PROCEDURE `get_players_for_tournament`(tmntId INT)
BEGIN
SELECT p.id as playerId, concat(p.lastname, ', ', p.firstname) as player
FROM players p 
where p.status = 'A' and p.id not IN (select player_id from tplayers where status = 'A' and tournament_Id = tmntId)
ORDER BY player;

END
