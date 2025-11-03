CREATE DEFINER=`swang`@`localhost` FUNCTION `game_cnt`(playerId INT, gameId INT, nday INT) RETURNS int
    DETERMINISTIC
BEGIN
declare cnt INT;
select count(*) from tplayers p
join tournaments t on t.id = p.tournament_id
where player_id = playerId and p.game_id = gameId and gross_score > 0 and t.status = 'A' and p.status = 'A' and start_at > date_add(now(), interval nday day) into cnt;
RETURN cnt;
END
