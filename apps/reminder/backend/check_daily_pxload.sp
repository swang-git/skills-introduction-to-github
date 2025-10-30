DELIMITER $$
CREATE DEFINER=`swang`@`localhost` PROCEDURE `check_daily_pxload`()
    DETERMINISTIC

(SELECT D.id, X.tag, X.ymd AS due_date, D.addtm AS due_in, D.tit AS message, X.count
FROM MyWeb.DailyDat D 
JOIN (SELECT tag, ymd, count(tit) AS count FROM MyWeb.DailyDat WHERE ymd=DATE_FORMAT(now(), '%Y-%m-%d') GROUP BY tag, ymd) X
ON X.tag = D.tag AND X.ymd = D.ymd AND X.tag = 'PXQG' 
ORDER BY D.id DESC LIMIT 1,1)

UNION

(SELECT D.id, X.tag, X.ymd AS due_date, D.addtm AS due_in, D.tit AS message, X.count
FROM MyWeb.DailyDat D 
JOIN (SELECT tag, ymd, count(tit) AS count FROM MyWeb.DailyDat WHERE ymd=DATE_FORMAT(now(), '%Y-%m-%d') GROUP BY tag, ymd) X
ON X.tag = D.tag AND X.ymd = D.ymd AND X.tag = 'PXWW' 
ORDER BY D.id DESC LIMIT 1,1)

UNION

(SELECT D.id, X.tag, X.ymd AS due_date, D.addtm AS due_in, D.tit AS message, X.count
FROM MyWeb.DailyDat D 
JOIN (SELECT tag, ymd, count(tit) AS count FROM MyWeb.DailyDat WHERE ymd=DATE_FORMAT(now(), '%Y-%m-%d') GROUP BY tag, ymd) X
ON X.tag = D.tag AND X.ymd = D.ymd AND X.tag = 'PXWX' 
ORDER BY D.id DESC LIMIT 1,1)


/*
(SELECT D.id, X.tag, X.ymd AS due_date, D.addtm AS due_in, D.tit AS message, X.count
FROM MyWeb.DailyDat D 
JOIN (SELECT tag, ymd, count(tit) AS count FROM MyWeb.DailyDat WHERE ymd=DATE_FORMAT(now(), '%Y-%m-%d') GROUP BY tag, ymd) X
ON X.tag = D.tag AND X.ymd = D.ymd AND X.tag = 'PXQG' 
ORDER BY D.id DESC LIMIT 1,1)

UNION

(SELECT D.id, X.tag, X.ymd AS due_date, D.addtm AS due_in, D.tit AS message, X.count
FROM MyWeb.DailyDat D 
JOIN (SELECT tag, ymd, count(tit) AS count FROM MyWeb.DailyDat WHERE ymd=DATE_FORMAT(now(), '%Y-%m-%d') GROUP BY tag, ymd) X
ON X.tag = D.tag AND X.ymd = D.ymd AND X.tag = 'PXWW' 
ORDER BY D.id DESC LIMIT 1,1)

UNION

(SELECT D.id, X.tag, X.ymd AS due_date, D.addtm AS due_in, D.tit AS message, X.count
FROM MyWeb.DailyDat D 
JOIN (SELECT tag, ymd, count(tit) AS count FROM MyWeb.DailyDat WHERE ymd=DATE_FORMAT(now(), '%Y-%m-%d') GROUP BY tag, ymd) X
ON X.tag = D.tag AND X.ymd = D.ymd AND X.tag = 'PXWX' 
ORDER BY D.id DESC LIMIT 1,1)
*/