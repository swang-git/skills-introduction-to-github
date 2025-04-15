CREATE VIEW match_players_view AS
SELECT mp.id, mp.player_id, mp.alias,
  pl.lastname, pl.firstname
FROM match_players mp, players pl
WHERE mp.player_id = pl.id
