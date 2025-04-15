CREATE DEFINER=`swang`@`%` PROCEDURE `get_open_rounds`(pid INT)
BEGIN
declare today DATE;
set today = curdate();
select -1 as id, payee_id as courseId, Fin.payeeName(2, 4, payee_id, purchasedon) as course, null as teeboxId, purchasedon as teetime, notes as note, null as teebox 
-- select -1 as id, payee_id as courseId, p.name as course, null as teeboxId, purchasedon as teetime, notes as note, null as teebox 
FROM Fin.spends s
-- JOIN princeton_golf.courses p on p.id = s.payee_id
WHERE cat_id = 2 and s.subcat_id = 4 and purchasedon >= today and s.user_id = pid and s.status = 'A' and purchasedon not in (select teetime from scores where teetime >= today)
UNION
select s.id, courseId, c.name as course, teeboxId, teetime, note, concat_ws(' ~ ', teebox, concat_ws(' ', rating, slope, yardage)) as teebox
from scores s
join courses c on s.courseId = c.id
join course_tees t on s.courseId = t.course_id and s.teeboxId = t.id
where s.playerId = pid and teetime >= today and s.status = 'O' and c.status = 'A' and t.status = 'A'
order by teetime asc;
END

----------------------------------------------






CREATE DEFINER=`swang`@`%` PROCEDURE `get_open_rounds`(pid INT)
BEGIN
declare today DATE;
set today = curdate();
select 777777 as id, 77777 as courseId, 'CourseName' as course, 77777 as teeboxId, purchasedon as teetime, notes as note, 'Teebox' as teebox 
FROM Fin.spends
WHERE cat_id = 2 and subcat_id = 4 and purchasedon >= today and user_id = pid and status = 'A' and purchasedon not in (select teetime from scores where teetime >= today)
UNION
select s.id, courseId, c.name as course, teeboxId, teetime, note, concat_ws(' ~ ', teebox, concat_ws(' ', rating, slope, yardage)) as teebox
from scores s
join courses c on s.courseId = c.id
join course_tees t on s.courseId = t.course_id and s.teeboxId = t.id
where s.playerId = pid and teetime >= today and s.status = 'O' and c.status = 'A' and t.status = 'A'
order by teetime asc;
END

---------------------------------------------
CREATE DEFINER=`swang`@`%` PROCEDURE `get_open_rounds`(pid INT)
BEGIN
declare today DATE;
set today = curdate();
select s.id, courseId, c.name as course, teeboxId, teetime, note, concat_ws(' ~ ', teebox, concat_ws(' ', rating, slope, yardage)) as teebox
from scores s
join courses c on s.courseId = c.id
join course_tees t on s.courseId = t.course_id and s.teeboxId = t.id
where s.playerId = pid and teetime >= today and s.status = 'O' and c.status = 'A' and t.status = 'A'
order by teetime asc;
END

--------- princeton golf ------------
CREATE DEFINER=`swang`@`%` PROCEDURE `get_open_rounds`(pid INT)
BEGIN
declare today DATE;
set today = curdate();
select s.id, courseId, c.name as course, teeboxId, teetime, note, concat_ws(' ~ ', teebox, concat_ws(' ', rating, slope, yardage)) as teebox
from scores s
join courses c on s.courseId = c.id
join course_tees t on s.courseId = t.course_id and s.teeboxId = t.id
where s.playerId = pid and teetime >= today and s.status = 'O' and c.status = 'A' and t.status = 'A'
order by teetime asc;
END

--------- DEV -----------------------

CREATE DEFINER=`swang`@`%` PROCEDURE `get_open_rounds`(pid INT)
BEGIN
declare today DATE;
set today = curdate();
select s.id, courseId, c.name as course, teeboxId, teetime, note, concat_ws(' ~ ', teebox, concat_ws(' ', rating, slope, yardage)) as teebox
from scores s
join courses c on s.courseId = c.id
join course_tees t on s.courseId = t.course_id and s.teeboxId = t.id
where s.playerId = pid and teetime >= today and s.status = 'O' and c.status = 'A' and t.status = 'A'
order by teetime asc;
END
