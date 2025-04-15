CREATE DEFINER=`swang`@`%` PROCEDURE `create_tmp_table_club_index`(tid INT, year_begin BOOLEAN)
BEGIN

DECLARE v_finished INTEGER DEFAULT 0;
DECLARE avg_cdiff decimal(3,1) DEFAULT null;
DECLARE year INT;
DECLARE tmnt_start DATETIME;
DECLARE num_count INT;
DECLARE num_games INT;
DECLARE player varchar(64);
DECLARE pid INT;
DECLARE calc_start DATETIME;
DECLARE club_index DECIMAL(3,1) DEFAULT null;


DEClARE pid_cursor CURSOR FOR 
SELECT p.id as pid FROM 
        tplayers tp
        JOIN players p on p.id = tp.player_id
        WHERE tp.status = 'A' AND p.status = 'A' AND tournament_id = tid  order by player_id ASC;
 

DECLARE CONTINUE HANDLER 
    FOR NOT FOUND SET v_finished = 1;

SELECT DATE_ADD(start_at, INTERVAL -1 day) INTO tmnt_start FROM tournaments tt WHERE tt.id = tid AND tt.status = 'A';  
set year = DATE_FORMAT(tmnt_start, '%Y');


IF year_begin = TRUE THEN
    SELECT tt.start_at INTO tmnt_start FROM tournaments tt WHERE tt.year = year AND tt.status = 'A' AND tt.game_id = 1;
END IF;

set calc_start = DATE_ADD(tmnt_start, INTERVAL -760 day);  



IF year_begin = 0 THEN
    DROP TEMPORARY TABLE IF EXISTS tmp_club_index;
    
    CREATE TEMPORARY TABLE tmp_club_index(pid INT, club_index DECIMAL(3,1));
ELSE
    DROP TEMPORARY TABLE IF EXISTS tmp_club_index_yb;
    
    CREATE TEMPORARY TABLE tmp_club_index_yb(pid INT, club_index DECIMAL(3,1));
END IF;

OPEN pid_cursor;
 
LOOP_BLOCK: LOOP
 
FETCH pid_cursor INTO pid; 
SELECT COUNT(*)
    FROM tplayers tp
    JOIN tournaments tt ON tp.tournament_id = tt.id 
    WHERE player_id = pid  AND tp.status = 'A' AND tt.status ='A'
        AND tt.start_at BETWEEN calc_start AND tmnt_start
        AND tp.gross_score > 0
        AND (tt.game_id < 5 or tt.game_id = 6)
        INTO num_count;
SET num_games = CEIL(num_count / 2);

SELECT AVG(idx_diff) 
    FROM (SELECT tp.idx_diff
            FROM tplayers tp
            JOIN tournaments tt ON tp.tournament_id = tt.id
            WHERE tp.player_id = pid AND tp.status = 'A' AND tt.status ='A' 
                AND tt.start_at BETWEEN calc_start AND tmnt_start
                AND tp.gross_score > 0
                AND (tt.game_id < 5 or tt.game_id = 6)
                AND tp.idx_diff > 0
            ORDER BY tp.idx_diff LIMIT num_games
    ) q INTO club_index;

IF v_finished = 1 THEN 
    LEAVE LOOP_BLOCK;
END IF;

IF year_begin = 0 THEN
    
    INSERT INTO tmp_club_index(pid, club_index) VALUES(pid, club_index);
ELSE
    INSERT INTO tmp_club_index_yb(pid, club_index) VALUES(pid, club_index);
END IF;
END LOOP LOOP_BLOCK;
CLOSE pid_cursor;
END