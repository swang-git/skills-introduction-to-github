DELIMITER $$
CREATE DEFINER=swang@localhost PROCEDURE get_handicap_and_aliases(IN gameId INT)
BEGIN
SELECT p.id AS player_id, 
			a.game_id AS game_id,
			a.alias AS alias,
			handicap_last_10_handi_diff(p.id) AS handicap,
			p.gender AS gender,
			concat(p.lastname,', ',p.firstname) AS name
FROM players p 
JOIN player_aliases a on a.player_id = p.id
WHERE a.game_id = gameId AND p.status = 'A' AND a.status = 'A'
ORDER BY alias;

END$$
DELIMITER ;