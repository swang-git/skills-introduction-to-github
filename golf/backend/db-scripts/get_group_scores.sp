DELIMITER $$
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_group_scores`(IN `tmntId` INT, IN `ttim` DATETIME)
BEGIN
SELECT sc.id, pl.name, tnum, pl.player_id, course_id, teebox_id, front9, back9,
h1, h2, h3, h4, h5, h6, h7, h8, h9, h10, h11, h12, h13, h14, h15, h16, h17, h18

FROM scores sc
JOIN tplayers pl on pl.tournament_id = sc.tournament_id and pl.player_id = sc.player_id
WHERE teetime = ttim and sc.tournament_id = tmntId
ORDER BY tnum;
END$$
DELIMITER ;

/* ===============================
BEGIN
select sc.id, pl.name, tnum, pl.player_id, course_id, teebox_id, front9, back9,
h1, h2, h3, h4, h5, h6, h7, h8, h9, h10, h11, h12, h13, h14, h15, h16, h17, h18
-- , pl.tournament_id, pl.status
from scores sc
join tplayers pl on pl.tournament_id = sc.tournament_id and pl.player_id = sc.player_id
where teetime = ttim and sc.tournament_id = tmntId
order by tnum;
END
================================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_group_scores`(tmntId INT, ttim DATETIME)
BEGIN
select sc.id, pl.name, tnum, playerId, courseId, teeboxId, front9, back9,
h1, h2, h3, h4, h5, h6, h7, h8, h9, h10, h11, h12, h13, h14, h15, h16, h17, h18
-- , pl.tournament_id, pl.status
from scores sc
join tplayers pl on pl.tournament_id = sc.tournamentId and pl.player_id = sc.playerId
where teetime = ttim and tournamentId = tmntId
order by tnum;
END
================================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_group_scores`(tmntId INT, ttim DATETIME)
BEGIN
select id, playerId, courseId, teeboxId, front9, back9,
h1, h2, h3, h4, h5, h6, h7, h8, h9, h10, h11, h12, h13, h14, h15, h16, h17, h18
from scores
where teetime = ttim and tournamentId = tmntId;
END
*/
