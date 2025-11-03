CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_course_list_order_by_frequency`()
BEGIN
SELECT C1.id as value, CONCAT(C1.name, ' (', F.cnt, ')') as label, F.cnt as cnt, C1.name as cname
FROM courses C1
JOIN (select course_id, count(*) as cnt from (select distinct course_id, DATE_FORMAT(start_at, '%Y-%m-%d') from tournaments where status = 'A') T group by course_id order by cnt desc) as F
ON C1.id = F.course_id
WHERE C1.status = 'A'
UNION
SELECT C2.id as value, C2.name as label, 0 as cnt, C2.name as cname
FROM courses C2 
JOIN (select id from courses where status = 'A' and id not in (select distinct course_id from tournaments where status = 'A')) E
ON C2.id = E.id
WHERE C2.status = 'A'
ORDER by cnt desc, cname;
END
==============================================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_course_list_order_by_frequency`()
BEGIN
SELECT C1.id as value, CONCAT(C1.name, ' (', F.cnt, ')') as label, F.cnt as cnt, C1.name as cname
FROM courses C1
JOIN (select course_id, count(*) as cnt from tournaments where status = 'A' and status = 'A' group by course_id order by cnt desc) as F
ON C1.id = F.course_id
WHERE C1.status = 'A'
UNION
SELECT C2.id as value, C2.name as label, 0 as cnt, C2.name as cname
FROM courses C2 
JOIN (select id from courses where status = 'A' and id not in (select distinct course_id from tournaments where status = 'A')) E
ON C2.id = E.id
WHERE C2.status = 'A'
ORDER by cnt desc, cname;
END
===============================================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_course_list_order_by_frequency`()
BEGIN
SELECT C1.id as value, CONCAT(C1.name, ' (', F.cnt, ')') as label, F.cnt as cnt
FROM courses C1
JOIN (select course_id, count(*) as cnt from tournaments where status = 'A' and status = 'A' group by course_id order by cnt desc) as F
ON C1.id = F.course_id
WHERE C1.status = 'A'
UNION
SELECT C2.id as value, C2.name as label, 0 as cnt
FROM courses C2 
JOIN (select id from courses where status = 'A' and id not in (select distinct course_id from tournaments where status = 'A')) E
ON C2.id = E.id
WHERE C2.status = 'A'
ORDER by cnt desc;
END
================================================
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_course_list_order_by_frequency`()
BEGIN
select C.id as value, CONCAT(C.name, ' (', F.cnt, ')') as label from courses C
JOIN (select course_id, count(*) as cnt from tournaments where status = 'A' and status = 'A' group by course_id order by cnt desc) as F
ON C.id = F.course_id
where C.status = 'A'
ORDER by F.cnt desc;
END

