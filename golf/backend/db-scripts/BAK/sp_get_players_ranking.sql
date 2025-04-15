USE `Golf_dev`;
DROP procedure IF EXISTS `get_players_ranking`;

DELIMITER $$
USE `Golf_dev`$$
CREATE DEFINER=`swang`@`%` PROCEDURE `get_players_ranking`(tid INT)
BEGIN

CALL create_tmp_table_poy_point(tid, -1);     -- the poy_point for current tournament

SELECT tp.player_id, CONCAT(pl.lastname, ', ', pl.firstname) AS player, pl.gender,
	tp.id AS tpid, 
	tp.gross_score AS GSC, 
	tp.gross_rank AS GRK, 
	tp.POS,
	pp.poy AS PYP
FROM players pl
LEFT JOIN tplayers tp ON pl.id = tp.player_id
LEFT JOIN tmp_poy_point pp ON pl.id = pp.id -- AND pp.id = tp.player_id
WHERE tp.tournament_id = tid
	AND pl.status = 'A'
	AND tp.status = 'A'


UNION
SELECT id AS player_id, CONCAT(lastname, ', ', firstname) AS player, gender,
	NULL AS tpid, 
	NULL AS GSC, 
	NULL AS GRK, 
	NULL AS POS,
	NULL AS PYP

FROM players pl
WHERE pl.status = 'A'
	AND pl.id NOT IN (SELECT player_id FROM tplayers WHERE tournament_id = tid AND status = 'A')
ORDER BY GSC;

END
$$
delimiter ;

