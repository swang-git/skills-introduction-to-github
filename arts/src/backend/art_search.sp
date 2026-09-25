
DELIMITER $$
CREATE DEFINER=`swang`@`%` PROCEDURE `art_search`(IN `cat` CHAR(3) CHARSET utf8mb4, IN `txt` VARCHAR(16) CHARSET utf8mb4)
BEGIN
IF cat = 'aut' THEN
	select * from DailyDat where status = 'A' and aut like concat('%', txt COLLATE utf8mb4_unicode_ci, '%') order by tim desc;
    -- select * from DailyDat where status = 'A' and aut = txt COLLATE utf8mb4_unicode_ci order by tim desc;
ELSEIF cat = 'tit' THEN
	select * from DailyDat where status = 'A' and tit like concat('%', txt COLLATE utf8mb4_general_ci, '%') order by tim desc;
ELSEIF cat = 'txt' THEN
	select * from DailyDat d
    JOIN DailyArt a on d.tag = a.tag
    where d.status = 'A' and a.qid = a.fid and  a.txt like concat('%', txt COLLATE utf8mb4_0900_ai_ci, '%') order by d.tim desc;
END IF;
END$$
DELIMITER ;


=========================
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
