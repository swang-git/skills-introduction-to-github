sum(prank) as poy from 
	        (select (case when bound - gross_rank > 0 then bound - gross_rank else 0 end ) as prank
		        from tplayers tp
		        join tournaments tt on tp.tournament_id = tid
                join players p on tp.player_id = pid
                where tt.game_id in (1, 2, 3, 4) and tp.player_id = playerId
                and tt.year = date_format(tmnt_start_at, '%Y') 
                and tt.start_at <= tmnt_start_at 
                order by prank desc limit 3
            ) ptmp;

select (case when bound - gross_rank > 0 then bound - gross_rank else 0 end ) as prank
from tplayers tp
join tournaments tt on tp.tournament_id = tid
join players p on tp.player_id = pid
where tt.game_id in (1, 2, 3, 4) and tp.player_id = playerId
and tt.year = date_format(tmnt_start_at, '%Y') 
and tt.start_at <= tmnt_start_at 
order by prank desc limit 3
-------------------------------------------------------
use Golf_dev;
delimiter $$
DROP procedure IF EXISTS `tt_poy_points`;
use Golf_dev$$
CREATE DEFINER=`swang`@`%` PROCEDURE `tt_poy_points`(tid INT)
BEGIN
declare tmnt_start_at datetime;
declare bound INT;
select tt.start_at from tournaments tt where tt.id = tid into tmnt_start_at;
set bound = 17;

SET @@session.sql_notes = 0;
DROP TEMPORARY TABLE IF EXISTS tmp_poy_points;
CREATE TEMPORARY TABLE tmp_poy_points AS  
    select p.id, p.lastname, p.firstname, 
    sum(case when bound - gross_rank > 0 then bound - gross_rank else 0 end) -     -- take one lowest gross_rank off i.e. just take 3 tournaments in consideration
    min(case when bound - gross_rank > 0 then bound - gross_rank else 0 end) as poy
    from tplayers tp
    join tournaments tt on tp.tournament_id = tt.id
    join players p on tp.player_id = p.id
    where tt.game_id in (1, 2, 3, 4)
    and tt.year = date_format(tmnt_start_at, '%Y') 
    and tt.start_at <= tmnt_start_at 
    group by p.id;
    -- order by poy;
SET @@session.sql_notes = 1;
select * from tmp_poy_points order by poy desc;
END$$
delimiter ;



CREATE DEFINER=`user`@`localhost` PROCEDURE `emp_performance`(id VARCHAR(10))
BEGIN
SET @@session.sql_notes = 0;
DROP TEMPORARY TABLE IF EXISTS performance;
CREATE TEMPORARY TABLE performance AS  
    SELECT time_in, time_out, day FROM attendance WHERE employee_id = id;
SET @@session.sql_notes = 1;
END

