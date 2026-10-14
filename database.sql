-- Import this file once in phpMyAdmin (Import tab). It creates the database, both tables and the starter questions.
-- Already imported an older version? Don't re-import. Instead run this one line in the SQL tab:
--   ALTER TABLE questions ADD COLUMN image VARCHAR(255) NULL;
-- and, for the login system, these two statements (run each once):
--   CREATE TABLE users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(20) NOT NULL UNIQUE, password_hash VARCHAR(255) NOT NULL, role ENUM('player','admin') NOT NULL DEFAULT 'player', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
--   ALTER TABLE scores ADD COLUMN user_id INT NULL, ADD CONSTRAINT fk_scores_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;

CREATE DATABASE IF NOT EXISTS mlbb_quiz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mlbb_quiz;

-- "answer" is the position of the correct option: 0 = option_a, 1 = option_b, 2 = option_c, 3 = option_d
-- "image" is optional: the file name of a picture inside the images/ folder (for example layla.png). Leave it empty for no picture.
CREATE TABLE IF NOT EXISTS questions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  category VARCHAR(50) NOT NULL,
  question VARCHAR(255) NOT NULL,
  option_a VARCHAR(100) NOT NULL,
  option_b VARCHAR(100) NOT NULL,
  option_c VARCHAR(100) NOT NULL,
  option_d VARCHAR(100) NOT NULL,
  answer TINYINT NOT NULL,
  image VARCHAR(255) NULL
);

-- Player and admin accounts. "role" is either 'player' or 'admin'. Passwords are stored hashed, never as plain text.
-- Create the first admin by opening create_admin.php once in your browser.
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(20) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('player', 'admin') NOT NULL DEFAULT 'player',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS scores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  name VARCHAR(20) NOT NULL,
  score TINYINT NOT NULL,
  total TINYINT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_scores_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO questions (category, question, option_a, option_b, option_c, option_d, answer) VALUES
('Game basics', 'Which company developed Mobile Legends: Bang Bang?', 'Moonton', 'Riot Games', 'Garena', 'Supercell', 0),
('Game basics', 'How many players are on each team in a classic match?', '3', '4', '5', '6', 2),
('Game basics', 'What is the name of the main battlefield map?', 'Land of Dawn', 'Summoner''s Rift', 'Howling Abyss', 'Magic Chess Arena', 0),
('Game basics', 'How many lanes are on the main map?', '1', '2', '3', '4', 2),
('Game basics', 'How many hero roles are there (Tank, Fighter, Assassin, Mage, Marksman, Support)?', '4', '5', '6', '7', 2),
('Game basics', 'Which big jungle monster, when killed, helps your team push a lane with a powerful minion?', 'Lord', 'Crab', 'Golem', 'Imp', 0),
('Heroes', 'What is Tigreal''s role?', 'Mage', 'Tank', 'Marksman', 'Assassin', 1),
('Heroes', 'What is Layla''s role?', 'Marksman', 'Support', 'Fighter', 'Tank', 0),
('Heroes', 'What is Eudora''s role?', 'Fighter', 'Tank', 'Mage', 'Support', 2),
('Heroes', 'What is Alucard''s role?', 'Fighter', 'Mage', 'Support', 'Marksman', 0),
('Heroes', 'What is Gusion''s role?', 'Tank', 'Assassin', 'Support', 'Marksman', 1),
('Heroes', 'What is Angela''s role?', 'Mage', 'Assassin', 'Support', 'Fighter', 2),
('Heroes', 'Which hero is famous for pulling enemies with an iron hook?', 'Franco', 'Layla', 'Zilong', 'Eudora', 0),
('Heroes', 'Which tank can transform into a car?', 'Tigreal', 'Johnson', 'Atlas', 'Minotaur', 1),
('Heroes', 'Which mage fights using an umbrella?', 'Eudora', 'Nana', 'Kagura', 'Cecilion', 2),
('Heroes', 'Which assassin uses steel cables to zip around the map?', 'Fanny', 'Saber', 'Gusion', 'Hayabusa', 0),
('Battle spells', 'Which battle spell is essential for junglers to kill jungle monsters?', 'Retribution', 'Flicker', 'Purify', 'Sprint', 0),
('Battle spells', 'Which battle spell teleports you a short distance?', 'Purify', 'Flicker', 'Execute', 'Petrify', 1),
('Battle spells', 'Which battle spell removes negative effects from your hero?', 'Sprint', 'Aegis', 'Purify', 'Revitalize', 2),
('Ranks & kills', 'How many kills in quick succession earn you a Savage?', '3', '4', '5', '6', 2),
('Ranks & kills', 'What is the lowest ranked tier in Mobile Legends?', 'Warrior', 'Elite', 'Master', 'Epic', 0),
('Ranks & kills', 'Which rank comes right after Epic?', 'Grandmaster', 'Legend', 'Mythic', 'Master', 1);
