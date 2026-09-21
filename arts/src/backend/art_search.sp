DELIMITER $$
CREATE DEFINER=`swang`@`localhost` PROCEDURE `get_daily_pxload`()
BEGIN
SELECT D.id, X.tag, X.ymd AS due_date, D.addtm AS due_in, D.tit AS details, X.count
FROM DailyDat D 
JOIN (SELECT tag, ymd, count(tit) AS count FROM DailyDat WHERE ymd=DATE_FORMAT(now(), '%Y-%m-%d') GROUP BY tag, ymd) X
ON X.tag = D.tag AND X.ymd = D.ymd AND X.tag = 'PXQG' 
ORDER BY D.addtm DESC LIMIT 1;

END$$
DELIMITER ;
