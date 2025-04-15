use Golf_dev;
delimiter $$
DROP procedure IF EXISTS `get_net_poy`;
use Golf_dev$$

CREATE DEFINER=`swang`@`%` FUNCTION `get_net_poy`(pid INT, tid INT) RETURNS decimal(3,1)
BEGIN
DECLARE netpoy DECIMAL(3,1);
DECLARE ptid INT;
DECLARE year INT;
DECLARE year_begin_cidx DECIMAL(3,1);
DECLARE year_end_cidx DECIMAL(3,1);

select DATE_FORMAT(tt.start_at, '%Y') from tournaments tt where tt.id = tid and tt.status = 'A' into year;
-- select id from tournaments tt where tt.year = year and tt.game_id = 1 and tt.status = 'A' into ptid;
-- select get_club_index(pid, ptid) - get_club_index(pid, tid) into netpoy;
select club_idx from tplayers p where p.player_id = pid and p.year = year and p.game_id = 1 and p.status = 'A' into year_begin_cidx;
set year_end_cidx = get_club_index(pid, tid);

if (year_begin_cidx > 0) then
	set netpoy = year_begin_cidx - year_end_cidx;
else
	set netpoy = null;
end if;

RETURN netpoy;
END
