
CREATE DEFINER=`swang`@`%` PROCEDURE `get_player_game_scores`(pid INT, gameId INT)
BEGIN
-- declare back_n_days INT;
-- select double_value from constant where id = 'back_n_days' into back_n_days;
select c.name, t.start_at, p.gross_score, (p.gross_score - ct.rating) * 113 / ct.slope as idxDiff, ct.rating, ct.slope
from tplayers p
join tournaments t on t.id = p.tournament_id
join courses c on c.id = t.course_id
join course_tees ct on c.id = ct.course_id AND ct.id = t.mens_tee_id
where p.player_id = pid and t.game_id = gameId
	and t.status = 'A' and p.status = 'A' and c.status = 'A'
    and p.gross_score > 65  -- and t.start_at > DATE_ADD(now(), interval back_n_days day)
order by t.start_at desc;
END
===================================
CREATE DEFINER=`swang`@`%` PROCEDURE `get_player_game_scores`(pid INT, gameId INT)
BEGIN
-- declare back_n_days INT;
-- select double_value from constant where id = 'back_n_days' into back_n_days;
select c.name, t.start_at, p.gross_score, (p.gross_score - ct.rating) * 113 / ct.slope as idxDiff
from tplayers p
join tournaments t on t.id = p.tournament_id
join courses c on c.id = t.course_id
join course_tees ct on c.id = ct.course_id AND ct.id = t.mens_tee_id
where p.player_id = pid and t.game_id = gameId
	and t.status = 'A' and p.status = 'A' and c.status = 'A'
    and p.gross_score > 65  -- and t.start_at > DATE_ADD(now(), interval back_n_days day)
order by t.start_at desc;
END
==================================
CREATE DEFINER=`swang`@`%` PROCEDURE `get_player_game_scores`(pid INT, gameId INT)
BEGIN
-- declare back_n_days INT;
-- select double_value from constant where id = 'back_n_days' into back_n_days;
select c.name, t.start_at, p.gross_score
from tplayers p
join tournaments t on t.id = p.tournament_id
join courses c on c.id = t.course_id
where p.player_id = pid and t.game_id = gameId
	and t.status = 'A' and p.status = 'A' and c.status = 'A'
--    and p.gross_score > 0 and t.start_at > DATE_ADD(now(), interval back_n_days day)
    and p.gross_score > 0
order by t.start_at desc;
END
===================================
CREATE DEFINER=`swang`@`%` PROCEDURE `get_player_game_scores`(pid INT, gameId INT)
BEGIN
declare back_n_days INT;
select double_value from constant where id = 'back_n_days' into back_n_days;
select c.name, t.start_at, p.gross_score
from tplayers p
join tournaments t on t.id = p.tournament_id
join courses c on c.id = t.course_id
where p.player_id = pid and t.game_id = gameId
	and t.status = 'A' and p.status = 'A' and c.status = 'A'
    and p.gross_score > 0 and t.start_at > DATE_ADD(now(), interval back_n_days day)
order by t.start_at desc;
END
====================================
CREATE DEFINER=`swang`@`%` PROCEDURE `get_player_game_scores`(pid INT, gameId INT, nday INT)
BEGIN
select c.name, t.start_at, p.gross_score
from tplayers p
join tournaments t on t.id = p.tournament_id
join courses c on c.id = t.course_id
where p.player_id = pid and t.game_id = gameId
	and t.status = 'A' and p.status = 'A' and c.status = 'A'
    and p.gross_score > 0 and t.start_at > DATE_ADD(now(), interval nday day)
order by t.start_at desc;
END
