DELIMITER $$
CREATE DEFINER=`swang`@`%localhost` PROCEDURE `get_active_players`(IN `gameId` INT, IN `gameCount` INT)
    COMMENT 'get active players based on how many game played for the last 90'
BEGIN
SELECT distinct 
	t.player_id, 
	CONCAT(p.lastname, ', ', p.firstname) as name, 
	p.gender, 
	handicap_idx(t.player_id) as idx, 
	game_cnt(t.player_id, gameId) as cnt,
    -- ONCAT(LEFT(p.lastname, 1), LEFT(p.firstname, 1)) as alias
    a.alias
FROM tplayers t
JOIN players p on p.id = t.player_id
JOIN player_aliases a on a.player_id = p.id
WHERE 
	gameId in (p.team) AND 
	game_cnt(t.player_id, gameId) >= gameCount AND 
	p.status = 'A' AND t.status = 'A' AND a.status = 'A'
ORDER BY idx;
END$$
DELIMITER ;
-- ================
/* CREATE DEFINER=`swang`@`%` PROCEDURE `get_active_players`(gameId INT, gameCount INT)
BEGIN
SELECT distinct 
	t.player_id, 
	CONCAT(p.lastname, ', ', p.firstname) as name, 
	p.gender, 
	avg_score(t.player_id, gameId) as avg, 
	game_cnt(t.player_id, gameId) as cnt
FROM tplayers t
JOIN players p on p.id = t.player_id
WHERE 
	gameId in (p.team) AND 
	game_cnt(t.player_id, gameId) >= gameCount AND 
	p.status = 'A' AND t.status = 'A'
ORDER BY name;
END
*/