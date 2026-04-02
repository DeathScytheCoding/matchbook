PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS gamelist (
  game_id INTEGER PRimary KEY,
  game_name TEXT NOT NULL,
  score_type INTEGER NOT NULL DEFAULT 0
);
  
CREATE TABLE IF NOT EXISTS maps (
  map_id INTEGER PRIMARY KEY,
  game_id INTEGER NOT NULL REFERENCES gamelist(game_id) ON DELETE CASCADE,
  map_name TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS characters (
  char_id INTEGER PRIMARY KEY,
  game_id INTEGER NOT NULL REFERENCES gamelist(game_id) ON DELETE CASCADE,
  char_name TEXT NOT NULL
);