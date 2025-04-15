







==============================
CREATE DEFINER=`swang`@`localhost` FUNCTION `payeeName`(catId INT, subCatId INT, payeeId INT, ptime DATETIME) RETURNS varchar(128) CHARSET utf8
    DETERMINISTIC
BEGIN
declare pname varchar(128);
if (catId != 2 or subcatId != 4 or DATEDIFF(ptime, '2019-9-10') < 0) then

	select name into pname from payees where id = payeeId and status = 'A';
else
	select name into pname from golf.courses where id = payeeId and status = 'A';
end if;
RETURN pname;
END
