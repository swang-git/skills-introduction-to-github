CREATE DEFINER=`swang`@`%` PROCEDURE `get_pgc_not_grouped_players`(tmntId INT, gameId TINYINT, year SMALLINT)
BEGIN
SELECT p.id as player_id, p.gender, CONCAT(p.lastname, ', ', p.firstname) as name, null as tnum, 
	null as grp, null as pscore, tmntId as tournament_id, gameId as game_id, year,
    m.type, m.course_member
from (select * from players where id not in (select player_id from tplayers where tournament_id = tmntId and game_id = gameId and year = year and status = 'A')) p
left join memberships m ON m.player_id = p.id and m.year = year and m.status = 'A' -- or (p.id = 46 and m.type = 'N')
-- JOIN players pl on pl.id = tp.player_id
-- RIGHT JOIN memberships m on m.player_id = p.id AND m.year = year AND m.status = 'A'
where p.status = 'A' -- and m.status = 'A' -- AND m.year = year
	-- pl.id = tp.player_id and
	-- p.status = 'A' and p.id not in (select player_id from tplayers where tournament_id = tmntId and game_id = gameId and year = year and status = 'A')
    -- tp.status = 'A' and
    -- pl.id = m.player_id and
    -- tp.year = year and
    -- tp.game_id = gameId and
    -- tp.tournament_id = tmntId and
    -- tp.year = year and
	   -- and m.year = year
    -- tp.tournament_id in (select id from tournaments where date_format(start_at, '%Y-%m-%d') = gameDate and game_id = gameId)
ORDER BY name;
END
============================
CREATE DEFINER=`swang`@`%` PROCEDURE `get_pgc_not_grouped_players`(tmntId INT, gameId INT, year INT)
BEGIN
SELECT p.id as player_id, p.gender, CONCAT(p.lastname, ', ', p.firstname) as name, null as tnum, 
	null as grp, null as pscore, tmntId as tournament_id, gameId as game_id, year,
    m.type, m.course_member
FROM (select * from players where id not in (select player_id from tplayers where tournament_id = tmntId and game_id = gameId and year = year and status = 'A')) p
-- JOIN players pl on pl.id = tp.player_id
LEFT JOIN memberships m on m.player_id = p.id AND m.year = year
where p.status = 'A'
	-- pl.id = tp.player_id and
	-- p.status = 'A' and p.id not in (select player_id from tplayers where tournament_id = tmntId and game_id = gameId and year = year and status = 'A')
    -- tp.status = 'A' and
    -- pl.id = m.player_id and
    -- tp.year = year and
    -- tp.game_id = gameId and
    -- tp.tournament_id = tmntId and
    -- tp.year = year and
	--	m.year = year
    -- tp.tournament_id in (select id from tournaments where date_format(start_at, '%Y-%m-%d') = gameDate and game_id = gameId)
ORDER BY name;
END