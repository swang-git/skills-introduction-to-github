DELIMITER $$
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_pgc_game_players`(IN `tmntId` INT, IN `gameId` INT, IN `year` INT)
BEGIN
SELECT pl.id AS player_id, pl.firstname, pl.lastname, CONCAT(pl.lastname, ', ', pl.firstname) AS name, tp.id, tp.tournament_id, pl.gender, tp.tnum,
	tp.captain, tp.grp, tp.gross_score AS pscore, gameId as game_id, 
    m.type, m.course_member, m.fees, tp.pos, tp.poyg, tp.note, tp.year, get_poy(pl.id, year, gameId) AS poy
FROM players pl
LEFT JOIN tplayers tp on pl.id = tp.player_id AND tp.game_id = gameId AND tp.tournament_id = tmntId AND tp.year = year AND tp.status = 'A'
LEFT JOIN memberships m on pl.id = m.player_id and m.year = year AND m.status = 'A'
WHERE 
	pl.status = 'A'
ORDER BY pl.id, grp, captain;
END$$
DELIMITER ;

/*
DELIMITER $$
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_pgc_game_players`(IN `tmntId` INT, IN `gameId` INT, IN `year` INT)
BEGIN
SELECT tp.id, tp.tournament_id, pl.gender, tp.player_id, tp.name, tp.tnum,
	tp.captain as tscore, tp.grp, tp.gross_score as pscore, tp.game_id, 
    m.type, m.course_member, tp.pos, tp.poyg, tp.note, tp.year, get_poy(tp.player_id, year, gameId) as poy
FROM tplayers tp
JOIN players pl on pl.id = tp.player_id
JOIN memberships m on m.player_id = pl.id
where 
	
	pl.status = 'A' and 
    tp.status = 'A' and
    
    tp.year = year and
    tp.game_id = gameId and
    tp.tournament_id = tmntId and
    tp.year = year and
    m.year = year
    
ORDER BY tp.grp, tp.tnum;
END$$
DELIMITER ;
*/