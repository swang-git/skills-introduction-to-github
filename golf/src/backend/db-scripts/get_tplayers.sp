CREATE DEFINER=`swang`@`%` PROCEDURE `get_tplayers`(tmntId INT)
BEGIN
-- SELECT tp.id, tp.player_id as playerId, pl.gender, concat(pl.lastname, ', ', pl.firstname) as fullname, tp.captain
SELECT tp.id, pl.gender, concat(pl.lastname, ', ', pl.firstname) as fullname, tp.captain, tp.grp, tp.player_id as playerId
-- SELECT tp.id, pl.gender, tp.player as fullname, tp.captain, tp.grp, tp.player_id as playerId
FROM players pl, tplayers tp
where 
	pl.id = tp.player_id and
	pl.status = 'A' and 
    tp.status = 'A' and
    tp.activity != 'dinn' and
    tp.tournament_id = tmntId 
ORDER BY tp.grp, tp.captain desc, fullname;
END
========================
CREATE DEFINER=`swang`@`%` PROCEDURE `get_tplayers`(tmntId INT)
BEGIN
-- SELECT tp.id, tp.player_id as playerId, pl.gender, concat(pl.lastname, ', ', pl.firstname) as fullname, tp.captain
-- SELECT tp.id, pl.gender, concat(pl.lastname, ', ', pl.firstname) as fullname, tp.captain, tp.grp, tp.player_id as playerId
SELECT tp.id, pl.gender, player as fullname, tp.captain, tp.grp, tp.player_id as playerId
FROM players pl, tplayers tp
where 
	pl.id = tp.player_id and
	pl.status = 'A' and 
    tp.status = 'A' and
    tp.activity != 'dinn' and
    tp.tournament_id = tmntId 
ORDER BY tp.grp, tp.captain desc, fullname;
END
========================
CREATE DEFINER=`swang`@`%` PROCEDURE `get_tplayers`(tmntId INT)
BEGIN
-- SELECT tp.id, tp.player_id as playerId, pl.gender, concat(pl.lastname, ', ', pl.firstname) as fullname, tp.captain
SELECT tp.id, pl.gender, concat(pl.lastname, ', ', pl.firstname) as fullname, tp.captain, tp.grp
FROM players pl, tplayers tp
where 
	pl.id = tp.player_id and
	pl.status = 'A' and 
    tp.status = 'A' and
    tp.tournament_id = tmntId
ORDER BY tp.grp, tp.captain desc, fullname;
END
