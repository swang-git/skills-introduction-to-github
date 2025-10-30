CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_tplayers`(tmntId INT)
BEGIN
-- SELECT tp.id, tp.player_id as playerId, pl.gender, concat(pl.lastname, ', ', pl.firstname) as fullname, tp.captain
SELECT tp.id, pl.gender, concat(pl.lastname, ', ', pl.firstname) as fullname, tp.captain, tp.group
FROM players pl, tplayers tp
where 
	pl.id = tp.player_id and
	pl.status = 'A' and 
    tp.status = 'A' and
    tp.tournament_id = tmntId
ORDER BY tp.captain desc, tp.group asc, fullname;

END
