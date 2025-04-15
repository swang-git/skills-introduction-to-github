CREATE DEFINER=`swang`@`%` PROCEDURE `get_tournaments`(tid INT)
BEGIN
SELECT t.id, t.year, CASE WHEN g.name regexp '^[1-9]' then concat(g.name, ' ', 'Tournament') ELSE g.name END AS game, 
	DATE_FORMAT(t.start_at, '%Y-%m-%d %H:%i') AS 'start_at',
	c.name AS courseName, cm.teebox AS mtee, cl.teebox AS ltee, t.note, t.fees,
    t.course_id, cm.id AS mtee_id, cl.id AS ltee_id, g.id AS game_id,
    cm.yardage as myard, cm.rating as mrating, cm.slope as mslope,
    cl.yardage as lyard, cl.rating as lrating, cl.slope as lslope, teetime_gap
FROM tournaments t
JOIN courses c ON t.course_id = c.id
JOIN course_tees cm ON t.mens_tee_id = cm.id AND t.course_id = cm.course_id
LEFT JOIN course_tees cl ON t.lady_tee_id = cl.id AND t.course_id = cl.course_id
JOIN game_names g ON t.game_id = g.id
WHERE c.status = 'A' AND t.status ='A' AND cm.status = 'A' AND g.status = 'A' 
	AND (CASE WHEN tid>0 THEN t.id = tid ELSE t.id > 0 END)
ORDER BY t.`start_at` DESC, g.id DESC;
END
==========================
CREATE DEFINER=`swang`@`%` PROCEDURE `get_tournaments`(tid INT)
BEGIN
SELECT t.id, t.year, CASE WHEN g.name regexp '^[1-9]' then concat(g.name, ' ', 'Tournament') ELSE g.name END AS game, 
	DATE_FORMAT(t.start_at, '%Y-%m-%d %H:%i') AS 'start_at',
	c.name AS courseName, cm.teebox AS mtee, cl.teebox AS ltee, t.note, t.fees,
    t.course_id, cm.id AS mtee_id, cl.id AS ltee_id, g.id AS game_id,
    cm.yardage as myard, cm.rating as mrating, cm.slope as mslope,
    cl.yardage as lyard, cl.rating as lrating, cl.slope as lslope
FROM tournaments t
JOIN courses c ON t.course_id = c.id
JOIN course_tees cm ON t.mens_tee_id = cm.id AND t.course_id = cm.course_id
LEFT JOIN course_tees cl ON t.lady_tee_id = cl.id AND t.course_id = cl.course_id
JOIN game_names g ON t.game_id = g.id
WHERE c.status = 'A' AND t.status ='A' AND cm.status = 'A' AND g.status = 'A' 
	AND (CASE WHEN tid>0 THEN t.id = tid ELSE t.id > 0 END)
ORDER BY t.`start_at` DESC, g.id DESC;
END
