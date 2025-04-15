CREATE DEFINER=`swang`@`%` PROCEDURE `create_tmp_table_poy_point`(tid INT, pid INT)
BEGIN
declare tmnt_start_at datetime;
declare bound INT;
select tt.start_at from tournaments tt where tt.id = tid into tmnt_start_at;
set bound = 17;

SET @@session.sql_notes = 0;
DROP TEMPORARY TABLE IF EXISTS tmp_poy_point;
CREATE TEMPORARY TABLE tmp_poy_point AS  
    select p.id, CONCAT(p.lastname, ', ', p.firstname) as player,
    sum(case when bound - gross_rank > 0 then bound - gross_rank else 0 end) -     
    min(case when bound - gross_rank > 0 then bound - gross_rank else 0 end) as poy
    from tplayers tp
    join tournaments tt on tp.tournament_id = tt.id
    join players p on tp.player_id = p.id
    where tt.game_id in (1, 2, 3, 4)
    and tt.year = date_format(tmnt_start_at, '%Y') 
    and tt.start_at <= tmnt_start_at 
    group by p.id;
SET @@session.sql_notes = 1;

END
