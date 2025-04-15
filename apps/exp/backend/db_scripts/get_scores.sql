CREATE DEFINER=`swang`@`%` PROCEDURE `get_scores`(pid INT, scoreId INT)
BEGIN
SELECT sc.id, co.name as course, co.id as courseId, ct.teebox, ct.id as teeboxId, ct.rating, ct.slope, DATE_FORMAT(sc.teetime, '%Y-%m-%d %H:%i') as teetime, 
	sc.h1, sc.h2, sc.h3, sc.h4, sc.h5, sc.h6, sc.h7, sc.h8, sc.h9,
    sc.h10, sc.h11, sc.h12, sc.h13, sc.h14, sc.h15, sc.h16, sc.h17, sc.h18,
    sc.front9, sc.back9, sc.totalscore, cast(sc.hdcpdiff as decimal(4,2)) as hdcpdiff, sc.note
FROM golf.scores sc, golf.courses co, golf.course_tees ct
WHERE co.status = 'A' AND ct.status ='A' AND sc.status = 'O'
	AND co.id = ct.course_id
	AND ct.course_id = sc.courseId
    AND ct.id = sc.teeboxId
    AND sc.playerId = pid
    -- AND sc.teetime <= now()
    AND CASE WHEN scoreId > 0 THEN scoreId = sc.id ELSE sc.id > 0 END
ORDER BY sc.`teetime` DESC;
END
