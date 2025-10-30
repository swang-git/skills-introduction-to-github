BEGIN
SELECT tp.id, tp.tournament_id AS tmntId, pl.gender, tp.player_id, tp.name, tp.tnum as team, tp.activity,
	tp.captain AS tscore, tp.grp, tp.score_id AS scoreId, tp.gross_score AS pscore, tp.game_id, avg_score(tp.player_id, tp.game_id) AS avg,
    handicap_idx(tp.player_id, gameId, gameDate) AS handicap, group_scenario
FROM players pl
JOIN tplayers tp ON tp.player_id = pl.id
LEFT JOIN match_groups mg ON mg.tournament_id = tp.tournament_id
WHERE
	pl.id = tp.player_id AND
	pl.status = 'A' AND 
    tp.status = 'A' AND
    CASE WHEN gameDate IS NOT null THEN tp.tournament_id IN (SELECT id FROM tournaments where date_format(start_at, '%Y-%m-%d') = gameDate AND game_id = gameId)
		 ELSE tp.tournament_id = tmntId 
	END
ORDER BY tp.grp, tp.tnum;
END

=======================================
DELIMITER $$
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_team_match_players`(IN `tmntId` INT, IN `gameId` INT, IN `gameDate` DATE)
BEGIN
SELECT tp.id, tp.tournament_id AS tmntId, pl.gender, tp.player_id, tp.name, tp.tnum as team, tp.activity,
	tp.captain AS tscore, tp.grp, tp.score_id AS scoreId, tp.gross_score AS pscore, tp.game_id, avg_score(tp.player_id, tp.game_id) AS avg,
    handicap_idx(tp.player_id, gameId, gameDate) AS handicap, group_scenario
FROM players pl
JOIN tplayers tp ON tp.player_id = pl.id
LEFT JOIN match_groups mg ON mg.tournament_id = tp.tournament_id
WHERE
	pl.id = tp.player_id AND
	pl.status = 'A' AND 
    tp.status = 'A' AND
    CASE WHEN gameDate IS NOT null THEN tp.tournament_id IN (SELECT id FROM tournaments where date_format(start_at, '%Y-%m-%d') = gameDate AND game_id = gameId)

/* ======================================
DELIMITER $$
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_team_match_players`(IN `tmntId` INT, IN `gameId` INT, IN `gameDate` DATE)
BEGIN
SELECT tp.id, tp.tournament_id AS tmntId, pl.gender, tp.player_id, tp.name, tp.tnum as team, tp.activity,
	tp.captain AS tscore, tp.grp, tp.gross_score AS pscore, tp.game_id, avg_score(tp.player_id, tp.game_id) AS avg,
    handicap_idx(tp.player_id, gameId, gameDate) AS handicap, group_scenario
FROM players pl
JOIN tplayers tp ON tp.player_id = pl.id
LEFT JOIN match_groups mg ON mg.tournament_id = tp.tournament_id
WHERE
	pl.id = tp.player_id AND
	pl.status = 'A' AND 
    tp.status = 'A' AND
    CASE WHEN gameDate IS NOT null THEN tp.tournament_id IN (SELECT id FROM tournaments where date_format(start_at, '%Y-%m-%d') = gameDate AND game_id = gameId)
		 ELSE tp.tournament_id = tmntId 
	END
ORDER BY tp.grp, tp.tnum;
END$$
DELIMITER ;

=======================================
BEGIN
SELECT tp.id, tp.tournament_id as tmntId, pl.gender, tp.player_id, tp.name, tp.tnum as team, tp.activity,
	tp.captain as tscore, tp.grp, tp.gross_score as pscore, tp.game_id, avg_score(tp.player_id, tp.game_id) as avg,
    handicap_last_10_handi_diff(tp.player_id) as handicap, mg.group_scenario
FROM tplayers tp
JOIN players pl on pl.id = tp.player_id
JOIN match_groups mg on mg.tournament_id = tp.tournament_id
WHERE
	pl.id = tp.player_id AND
	pl.status = 'A' AND
    tp.status = 'A' AND
    CASE WHEN gameDate is NOT null THEN tp.tournament_id in (select id from tournaments WHERE date_format(start_at, '%Y-%m-%d') = gameDate and game_id = gameId)
		 ELSE tp.tournament_id = tmntId 
	END
ORDER BY tp.grp, tp.tnum;
END

============================

BEGIN
SELECT tp.id, tp.tournament_id as tmntId, pl.gender, tp.player_id, tp.name, tp.tnum as team, tp.activity,
	tp.captain as tscore, tp.grp, tp.gross_score as pscore, tp.game_id, avg_score(tp.player_id, tp.game_id) as avg,
    handicap_idx(tp.player_id) as handicap
FROM players pl, tplayers tp
where 
	pl.id = tp.player_id and
	pl.status = 'A' and 
    tp.status = 'A' and
    CASE WHEN gameDate is not null THEN tp.tournament_id in (select id from tournaments where date_format(start_at, '%Y-%m-%d') = gameDate and game_id = gameId)
		 ELSE tp.tournament_id = tmntId 
	END
ORDER BY tp.grp, tp.tnum;
END

=========================
BEGIN
SELECT tp.id, tp.tournament_id as tmntId, pl.gender, tp.player_id, tp.name, tp.tnum as team, tp.activity,
	tp.captain as tscore, tp.grp, tp.gross_score as pscore, tp.game_id, avg_score(tp.player_id, tp.game_id) as avg,
    handicap_idx(tp.player_id) as handicapIdx
FROM players pl, tplayers tp
where 
	pl.id = tp.player_id and
	pl.status = 'A' and 
    tp.status = 'A' and
    CASE WHEN gameDate is not null THEN tp.tournament_id in (select id from tournaments where date_format(start_at, '%Y-%m-%d') = gameDate and game_id = gameId)
		 ELSE tp.tournament_id = tmntId 
	END
ORDER BY tp.grp, tp.tnum;
END

==========================

CREATE DEFINER=`swang`@`%` PROCEDURE `get_team_match_players`(tmntId INT, gameId INT, gameDate DATE)
BEGIN
SELECT tp.id, tp.tournament_id as tmntId, pl.gender, tp.player_id, tp.name, tp.tnum as team, tp.activity,
	tp.captain as tscore, tp.grp, tp.gross_score as pscore, tp.game_id, avg_score(tp.player_id, tp.game_id) as avg
FROM players pl, tplayers tp
where 
	pl.id = tp.player_id and
	pl.status = 'A' and 
    tp.status = 'A' and
    CASE WHEN gameDate is not null THEN tp.tournament_id in (select id from tournaments where date_format(start_at, '%Y-%m-%d') = gameDate and game_id = gameId)
		 ELSE tp.tournament_id = tmntId 
	END
ORDER BY tp.grp, tp.tnum;
END
===========================
BEGIN
SELECT tp.id, tp.tournament_id as tmntId, pl.gender, tp.player_id, tp.name, tp.tnum as team, tp.activity,
	tp.captain as tscore, tp.grp, tp.gross_score as pscore, tp.game_id, avg_score(tp.player_id, tp.game_id) as avg,
    course_handicap(tp.player_id, t.course_id, if(t.mens_tee_id = null, lady_tee_id, mens_tee_id)) as chandicap
FROM players pl, tplayers tp, tournaments t
where 
	pl.id = tp.player_id and
	pl.status = 'A' and 
    tp.status = 'A' and
    CASE WHEN gameDate is not null THEN tp.tournament_id in (select id from tournaments where date_format(start_at, '%Y-%m-%d') = gameDate and game_id = gameId)
		 ELSE tp.tournament_id = tmntId 
	END
    AND tp.tournament_id = t.id
ORDER BY tp.grp, tp.tnum;
END
---------------------------
CREATE DEFINER=`swang`@`%` PROCEDURE `get_team_match_players`(tmntId INT, gameId INT, gameDate DATE)
BEGIN
SELECT tp.id, tp.tournament_id as tmntId, pl.gender, tp.player_id, tp.name, tp.tnum as team, 
	tp.captain as tscore, tp.grp, tp.gross_score as pscore, tp.game_id, avg_score(tp.player_id, tp.game_id) as avg
FROM players pl, tplayers tp
where 
	pl.id = tp.player_id and
	pl.status = 'A' and 
    tp.status = 'A' and
    CASE WHEN gameDate is not null THEN tp.tournament_id in (select id from tournaments where date_format(start_at, '%Y-%m-%d') = gameDate and game_id = gameId)
		 ELSE tp.tournament_id = tmntId 
	END
ORDER BY tp.grp, tp.tnum;
END

==========================
CREATE DEFINER=`swang`@`%` PROCEDURE `get_team_match_players`(tmntId INT, gameId INT, gameDate DATE)
BEGIN
SELECT tp.id, tp.tournament_id as tmntId, pl.gender, tp.player_id, tp.name, tp.tnum as team, 
	tp.captain as tscore, tp.grp, tp.gross_score as pscore, tp.game_id, avg_score(tp.player_id, tp.game_id) as avg
FROM players pl, tplayers tp
where 
	pl.id = tp.player_id and
	pl.status = 'A' and 
    tp.status = 'A' and
    CASE WHEN gameDate is not null THEN tp.tournament_id = 0 or tp.tournament_id in (select id from tournaments 
		   where date_format(start_at, '%Y-%m-%d') = gameDate and game_id = gameId    )
		 ELSE tp.tournament_id = tmntId 
	END
ORDER BY tp.grp, tp.tnum;
END
==========================
CREATE DEFINER=`swang`@`%` PROCEDURE `get_team_match_players`(tmntId INT, gameId INT, gameDate DATE)
BEGIN
SELECT tp.id, tp.tournament_id as tmntId, pl.gender, tp.player_id, tp.name, tp.tnum as team, 
	tp.captain as tscore, tp.grp, tp.gross_score as pscore, tp.game_id, avg_score(tp.player_id, tp.game_id) as avg
FROM players pl, tplayers tp
where 
	pl.id = tp.player_id and
	pl.status = 'A' and 
    tp.status = 'A' and
    CASE WHEN gameDate is not null THEN tp.tournament_id in (select id from tournaments 
		   where date_format(start_at, '%Y-%m-%d') = gameDate and game_id = gameId    )
		 ELSE tp.tournament_id = tmntId 
	END
ORDER BY tp.grp, tp.tnum;
END
==========================

CREATE DEFINER=`swang`@`%` PROCEDURE `get_team_match_players`(tmntId INT, gameDate DATE)
BEGIN
SELECT tp.id, tp.tournament_id as tmntId, pl.gender, tp.player_id, tp.player, tp.tnum as team,
	tp.captain as tscore, tp.grp, tp.gross_score as pscore, tp.game_id
FROM players pl, tplayers tp
where
	pl.id = tp.player_id and
	pl.status = 'A' and
    tp.status = 'A' and
    CASE WHEN gameDate is not null THEN tp.tournament_id in (select id from tournaments where date_format(start_at, '%Y-%m-%d') = gameDate)
		ELSE tp.tournament_id = tmntId
	END
ORDER BY tp.grp, tp.tnum;
END
*/