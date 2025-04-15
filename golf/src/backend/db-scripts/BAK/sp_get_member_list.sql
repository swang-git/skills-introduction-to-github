USE `Golf_dev`;
DROP procedure IF EXISTS `get_member_list`;

DELIMITER $$
USE `Golf_dev`$$
CREATE DEFINER=`swang`@`%` PROCEDURE `get_member_list`()
BEGIN

IF (yr > 0) THEN
	SELECT yr INTO @year;
ELSE
	SELECT DATE_FORMAT(now(), '%Y') into @year;
END IF;
# SELECT @year;

SELECT p.id, m.id as mid, p.lastname, p.firstname, p.gender, m.type, m.fees, m.year
FROM players p
LEFT JOIN (select id, player_id, type, fees, year from memberships where year = @year and status = 'A') m ON p.id = m.player_id
WHERE p.status = 'A'
ORDER BY fees DESC, lastname ASC;

END$$
DELIMITER ;

/*
DECLARE tmnt_year INT;
SELECT DATE_FORMAT(tt.start_at, '%Y') FROM tournaments tt WHERE tt.id = tournamentId INTO tmnt_year;

SELECT tm.id AS tpid, p.id AS player_id, CONCAT(p.lastname, ', ', p.firstname) AS player, p.gender,
tm.gross_score AS GSC, 
tm.gross_rank AS GRK, 
GET_CLUB_INDEX(p.id, tournamentId) AS CDX,
GET_POY_POINTS(p.id, tournamentId) AS PYP,
gross_score - GET_CLUB_INDEX(p.id, tournamentId) AS NSC, 
tm.pos

FROM player_info p
LEFT JOIN tplayers_tmnt tm ON p.id = tm.player_id AND tm.tournament_id = tournamentId      

WHERE 
(GET_CLUB_INDEX(p.id, tournamentId) > 0 OR GET_POY_RANK(p.id, tournamentId) > 0) 
AND p.year = tmnt_year
-- AND (gross_score > 0 OR case when !score_only then gross_score IS NULL END)
AND (gross_score > 0 OR gross_score is NULL)

ORDER BY gross_score;

END$$
delimiter ;
*/