BEGIN
declare pname varchar(128);
if (catId = 2 AND (subcatId = 4 OR subcatId in (select id from subcategories where name like '%Tournament%' or name like '%Playoff%')))
then
	select name into pname from golf.courses where id = payeeId and status = 'A';
else
	select name into pname from payees where id = payeeId and status = 'A'; 
end if;

RETURN pname;
END


BEGIN
declare pname varchar(128);
if (catId != 2 or (subcatId != 4 and subcatId not in 
	(select id from subcategories where name like '%Tournament%' or name like '%Outing%' or name like '%Playoff%')) or DATEDIFF(ptime, '2019-9-10') < 0) then
	select name into pname from payees where id = payeeId and status = 'A'; 
else
	select name into pname from golf.courses where id = payeeId and status = 'A';
end if;
RETURN pname;
END
=============================
BEGIN
declare pname varchar(128);
if (catId != 2 or (subcatId != 4 and subcatId not in 
	(select id from subcategories where name like '%Tournament%' or name like '%Playoff%')) or DATEDIFF(ptime, '2019-9-10') < 0) then
	select name into pname from payees where id = payeeId and status = 'A'; 
else

	select name into pname from golf.courses where id = payeeId and status = 'A';
end if;

--select name into pname from golf.courses where id = payeeId and status = 'A';

RETURN pname;
END

===========================
BEGIN
declare pname varchar(128);
if (catId != 2 or (subcatId != 4 and subcatId not in 
	(select id from subcategories where name like '%Tournament%' or name like '%Playoff%')) or DATEDIFF(ptime, '2019-9-10') < 0) then
	select name into pname from payees where id = payeeId and status = 'A'; 
else

	select name into pname from golf.courses where id = payeeId and status = 'A';
end if;

--select name into pname from golf.courses where id = payeeId and status = 'A';

RETURN pname;
END
====================
CREATE DEFINER=`swang`@`localhost` FUNCTION `payeeName`(catId INT, subCatId INT, payeeId INT, ptime DATETIME) RETURNS varchar(128) CHARSET utf8
    DETERMINISTIC
BEGIN
declare pname varchar(128);
if (catId != 2 or (subcatId != 4 and subcatId not in
	(select id from subcategories where name like '%Tournament%' or name like '%Outing%' or name like '%Playoff%')) or DATEDIFF(ptime, '2019-9-10') < 0) then
	select name into pname from payees where id = payeeId and status = 'A';
else
	select name into pname from golf.courses where id = payeeId and status = 'A';
end if;
RETURN pname;
END
