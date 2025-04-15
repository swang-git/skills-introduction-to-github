CREATE DEFINER=`swang`@`%` PROCEDURE `get_member_list`(yr INT)
BEGIN

IF (yr > 0) THEN
	SELECT yr INTO @year;
ELSE
	SELECT DATE_FORMAT(now(), '%Y') into @year;
END IF;
# SELECT @year;

SELECT p.id, m.id as mid, p.lastname, p.firstname, p.gender, p.phone, p.email, p.chname, p.nkname, m.type, m.fees, m.year
FROM players p
LEFT JOIN (select id, player_id, type, fees, year from memberships where year = @year and status = 'A') m ON p.id = m.player_id
WHERE p.status = 'A'
ORDER BY lastname, firstname;
-- ORDER BY fees DESC, lastname ASC;

END

=====================
CREATE DEFINER=`swang`@`%` PROCEDURE `get_member_list`(yr INT)
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

END
