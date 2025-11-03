CREATE DEFINER=`swang`@`%` PROCEDURE `get_tplayers`(tid INT)
BEGIN
-- SELECT tp.id, tp.player_id as playerId, pl.gender, concat(pl.lastname, ', ', pl.firstname) as fullname, tp.captain
SELECT tp.id, pl.gender, concat(pl.lastname, ', ', pl.firstname) as fullname, tp.captain, tp.group
FROM players pl, tplayers tp
WHERE pl.id = tp.player_id
	AND	pl.status = 'A'
    AND tp.status = 'A'
    AND tp.tournament_id = tid
    AND tp.hole19 != 'Don' -- dinner only person
ORDER BY tp.captain desc, tp.group asc, fullname;

END
