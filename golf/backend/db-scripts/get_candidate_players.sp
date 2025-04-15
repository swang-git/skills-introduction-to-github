CREATE DEFINER=`swang`@`%` PROCEDURE `get_candidate_players`(tmntId INT, gameId INT)
BEGIN
IF tmntId = 0 THEN
	SELECT distinct t.player_id, CONCAT(p.lastname, ', ', p.firstname) as name, p.team as tgameId, p.gender, 0 as id, null as team, null as score, null as grp,
		avg_score(t.player_id, gameId) as avg, game_cnt(t.player_id, gameId) as cnt
	FROM tplayers t
	JOIN players p on p.id = t.player_id
	WHERE gameId in (p.team) AND game_cnt(t.player_id, gameId) > 3 AND p.status = 'A' AND t.status = 'A'
	ORDER BY name;
ELSE
	select id as player_id, concat(lastname, ', ', firstname) as name, p.team as tgameId, gender, 0 as id, null as team,
		null as score, null as grp, null as avg, 0 as cnt
	from players p
	where status = 'A' and id not in 
		(select distinct player_id from tplayers where status = 'A' AND gameId in (p.team) AND game_cnt(player_id, gameId) > 3)
	order by cnt desc, name; 
END IF;
END