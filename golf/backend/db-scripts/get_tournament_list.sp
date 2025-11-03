DELIMITER $$
CREATE DEFINER=`swang`@`%` PROCEDURE `get_tournament_list`(IN `tid` INT)
BEGIN
SELECT t.id, t.year, CASE WHEN g.name regexp '^[1-9]' then concat(g.name, ' ', 'Tournament') ELSE g.name END AS game, 
	DATE_FORMAT(t.start_at, '%Y-%m-%dT%H:%i') AS 'start_at', DATE_FORMAT(t.start_at, '%m-%d %H:%i') AS 'disptm',
	c.name AS courseName, t.note, t.fees, t.links,
    t.course_id, g.id AS game_id, teetime_gap, 
    
    cm.id as mtee_id, CONCAT(cm.teebox, ' ~ ', CONCAT_WS(' ', cm.rating, cm.slope, cm.yardage)) as mtee,
    cl.id as ltee_id, CONCAT(cl.teebox, ' ~ ', CONCAT_WS(' ', cl.rating, cl.slope, cl.yardage)) as ltee
FROM tournaments t
JOIN courses c ON t.course_id = c.id
JOIN course_tees cm ON t.mens_tee_id = cm.id AND t.course_id = cm.course_id
JOIN course_tees cl ON t.lady_tee_id = cl.id AND t.course_id = cl.course_id
JOIN game_names g ON t.game_id = g.id
WHERE c.status = 'A' AND t.status ='A' AND cm.status = 'A' AND g.status = 'A' 
	AND (CASE WHEN tid>0 THEN t.id = tid ELSE t.id > 0 END)
ORDER BY t.`start_at` DESC, g.id DESC;
END$$
DELIMITER ;

/* ============================
DELIMITER $$
CREATE DEFINER=`swang`@`%` PROCEDURE `get_tournament_list`(IN `tid` INT)
BEGIN
SELECT t.id, t.year, CASE WHEN g.name regexp '^[1-9]' then concat(g.name, ' ', 'Tournament') ELSE g.name END AS game, 
	DATE_FORMAT(t.start_at, '%Y-%m-%dT%H:%i') AS 'start_at', DATE_FORMAT(t.start_at, '%m-%d %H:%i') AS 'disptm',
	c.name AS courseName, t.note, t.fees,
    t.course_id, g.id AS game_id, teetime_gap,
    -- cm.yardage as myard, cm.rating as mrating, cm.slope as mslope,
    -- cl.yardage as lyard, cl.rating as lrating, cl.slope as lslope
    cm.id as mtee_id, CONCAT(cm.teebox, ' ~ ', CONCAT_WS(' ', cm.rating, cm.slope, cm.yardage)) as mtee,
    cl.id as ltee_id, CONCAT(cl.teebox, ' ~ ', CONCAT_WS(' ', cl.rating, cl.slope, cl.yardage)) as ltee
FROM tournaments t
JOIN courses c ON t.course_id = c.id
JOIN course_tees cm ON t.mens_tee_id = cm.id AND t.course_id = cm.course_id
JOIN course_tees cl ON t.lady_tee_id = cl.id AND t.course_id = cl.course_id
JOIN game_names g ON t.game_id = g.id
WHERE c.status = 'A' AND t.status ='A' AND cm.status = 'A' AND g.status = 'A' 
	AND (CASE WHEN tid>0 THEN t.id = tid ELSE t.id > 0 END)
ORDER BY t.`start_at` DESC, g.id DESC;
END$$
DELIMITER ;

=================================
CREATE DEFINER=`swang`@`%` PROCEDURE `get_tournament_list`(tid INT)
BEGIN
SELECT t.id, t.year, CASE WHEN g.name regexp '^[1-9]' then concat(g.name, ' ', 'Tournament') ELSE g.name END AS game, 
	DATE_FORMAT(t.start_at, '%Y-%m-%dT%H:%i') AS 'start_at', DATE_FORMAT(t.start_at, '%m-%d %H:%i') AS 'disptm',
	c.name AS courseName, t.note, t.fees,
    t.course_id, g.id AS game_id, teetime_gap,
    
    
    cm.id as mtee_id, CONCAT(cm.teebox, ' ~ ', CONCAT_WS(' ', cm.rating, cm.slope, cm.yardage)) as mtee,
    cl.id as ltee_id, CONCAT(cl.teebox, ' ~ ', CONCAT_WS(' ', cl.rating, cl.slope, cl.yardage)) as ltee
FROM tournaments t
JOIN courses c ON t.course_id = c.id
JOIN course_tees cm ON t.mens_tee_id = cm.id AND t.course_id = cm.course_id
JOIN course_tees cl ON t.lady_tee_id = cl.id AND t.course_id = cl.course_id
JOIN game_names g ON t.game_id = g.id
WHERE c.status = 'A' AND t.status ='A' AND cm.status = 'A' AND g.status = 'A' 
	AND (CASE WHEN tid>0 THEN t.id = tid ELSE t.id > 0 END)
ORDER BY t.`start_at` DESC, g.id DESC;
END

=============================
CREATE DEFINER=`swang`@`%` PROCEDURE `get_tournament_list`(tid INT)
BEGIN
SELECT t.id, t.year, CASE WHEN g.name regexp '^[1-9]' then concat(g.name, ' ', 'Tournament') ELSE g.name END AS game, 
	DATE_FORMAT(t.start_at, '%Y-%m-%dT%H:%i') AS 'start_at', DATE_FORMAT(t.start_at, '%m-%d %H:%i') AS 'disptm',
	c.name AS courseName, t.note, t.fees,
    t.course_id, g.id AS game_id, teetime_gap,
    -- cm.yardage as myard, cm.rating as mrating, cm.slope as mslope,
    -- cl.yardage as lyard, cl.rating as lrating, cl.slope as lslope
    cm.id as mtee_id, CONCAT(cm.teebox, ' ~ ', CONCAT_WS(' ', cm.rating, cm.slope, cm.yardage)) as mtee,
    cl.id as ltee_id, CONCAT(cl.teebox, ' ~ ', CONCAT_WS(' ', cl.rating, cl.slope, cl.yardage)) as ltee
FROM tournaments t
JOIN courses c ON t.course_id = c.id
JOIN course_tees cm ON t.mens_tee_id = cm.id AND t.course_id = cm.course_id
LEFT JOIN course_tees cl ON t.lady_tee_id = cl.id AND t.course_id = cl.course_id
JOIN game_names g ON t.game_id = g.id
WHERE c.status = 'A' AND t.status ='A' AND cm.status = 'A' AND g.status = 'A' 
	AND (CASE WHEN tid>0 THEN t.id = tid ELSE t.id > 0 END)
ORDER BY t.`start_at` DESC, g.id DESC;
END
============================
CREATE DEFINER=`swang`@`%` PROCEDURE `get_tournament_list`(tid INT)
BEGIN
SELECT t.id, t.year, CASE WHEN g.name regexp '^[1-9]' then concat(g.name, ' ', 'Tournament') ELSE g.name END AS game, 
	DATE_FORMAT(t.start_at, '%Y-%m-%dT%H:%i') AS 'start_at', DATE_FORMAT(t.start_at, '%m-%d %H:%i') AS 'disptm',
	c.name AS courseName, t.note, t.fees,
    t.course_id, g.id AS game_id,
    -- cm.yardage as myard, cm.rating as mrating, cm.slope as mslope,
    -- cl.yardage as lyard, cl.rating as lrating, cl.slope as lslope
    cm.id as mtee_id, CONCAT(cm.teebox, ' ~ ', CONCAT_WS(' ', cm.rating, cm.slope, cm.yardage)) as mtee,
    cl.id as ltee_id, CONCAT(cl.teebox, ' ~ ', CONCAT_WS(' ', cl.rating, cl.slope, cl.yardage)) as ltee
FROM tournaments t
JOIN courses c ON t.course_id = c.id
JOIN course_tees cm ON t.mens_tee_id = cm.id AND t.course_id = cm.course_id
LEFT JOIN course_tees cl ON t.lady_tee_id = cl.id AND t.course_id = cl.course_id
JOIN game_names g ON t.game_id = g.id
WHERE c.status = 'A' AND t.status ='A' AND cm.status = 'A' AND g.status = 'A' 
	AND (CASE WHEN tid>0 THEN t.id = tid ELSE t.id > 0 END)
ORDER BY t.`start_at` DESC, g.id DESC;
END
*/