CREATE DATABASE IF NOT EXISTS futcarreira
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE futcarreira;

CREATE TABLE IF NOT EXISTS countries (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL UNIQUE,
  code CHAR(3) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS leagues (
  id INT AUTO_INCREMENT PRIMARY KEY,
  country_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(80) NOT NULL UNIQUE,
  FOREIGN KEY (country_id) REFERENCES countries(id)
);

CREATE TABLE IF NOT EXISTS divisions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  league_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  level TINYINT NOT NULL,
  UNIQUE KEY unique_league_level (league_id, level),
  FOREIGN KEY (league_id) REFERENCES leagues(id)
);

CREATE TABLE IF NOT EXISTS clubs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  division_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  reputation INT NOT NULL DEFAULT 50,
  FOREIGN KEY (division_id) REFERENCES divisions(id)
);

CREATE TABLE IF NOT EXISTS players (
  id INT AUTO_INCREMENT PRIMARY KEY,
  club_id INT NOT NULL,
  user_controlled TINYINT(1) NOT NULL DEFAULT 0,
  name VARCHAR(120) NOT NULL,
  position ENUM('GOL','ZAG','LE','LD','VOL','MC','MEI','PE','PD','ATA') NOT NULL,
  nationality VARCHAR(80) NOT NULL,
  dominant_foot ENUM('Direita','Esquerda') NOT NULL DEFAULT 'Direita',
  height_cm INT NOT NULL DEFAULT 175,
  weight_kg INT NOT NULL DEFAULT 70,
  start_country CHAR(3) NOT NULL DEFAULT 'BRA',
  age INT NOT NULL DEFAULT 18,
  overall INT NOT NULL DEFAULT 58,
  potential INT NOT NULL DEFAULT 75,
  pace INT NOT NULL DEFAULT 60,
  dribbling INT NOT NULL DEFAULT 60,
  finishing INT NOT NULL DEFAULT 60,
  defending INT NOT NULL DEFAULT 60,
  passing INT NOT NULL DEFAULT 60,
  physical INT NOT NULL DEFAULT 60,
  stamina INT NOT NULL DEFAULT 100,
  goals INT NOT NULL DEFAULT 0,
  assists INT NOT NULL DEFAULT 0,
  appearances INT NOT NULL DEFAULT 0,
  season_rating_total DECIMAL(6,2) NOT NULL DEFAULT 0.00,
  season_rating_count INT NOT NULL DEFAULT 0,
  bench_games INT NOT NULL DEFAULT 0,
  injured_until_match INT DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (club_id) REFERENCES clubs(id)
);

CREATE TABLE IF NOT EXISTS seasons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  current_year INT NOT NULL DEFAULT 2026,
  current_round INT NOT NULL DEFAULT 1,
  active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS fixtures (
  id INT AUTO_INCREMENT PRIMARY KEY,
  season_id INT NOT NULL,
  division_id INT NOT NULL,
  round_number INT NOT NULL,
  home_club_id INT NOT NULL,
  away_club_id INT NOT NULL,
  home_goals INT DEFAULT NULL,
  away_goals INT DEFAULT NULL,
  played TINYINT(1) NOT NULL DEFAULT 0,
  user_match TINYINT(1) NOT NULL DEFAULT 0,
  FOREIGN KEY (season_id) REFERENCES seasons(id),
  FOREIGN KEY (division_id) REFERENCES divisions(id),
  FOREIGN KEY (home_club_id) REFERENCES clubs(id),
  FOREIGN KEY (away_club_id) REFERENCES clubs(id)
);

CREATE TABLE IF NOT EXISTS match_events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fixture_id INT NOT NULL,
  minute INT NOT NULL,
  event_text VARCHAR(255) NOT NULL,
  FOREIGN KEY (fixture_id) REFERENCES fixtures(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS transfer_offers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  player_id INT NOT NULL,
  from_club_id INT NOT NULL,
  to_club_id INT NOT NULL,
  season_year INT NOT NULL,
  accepted TINYINT(1) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (player_id) REFERENCES players(id),
  FOREIGN KEY (from_club_id) REFERENCES clubs(id),
  FOREIGN KEY (to_club_id) REFERENCES clubs(id)
);

CREATE OR REPLACE VIEW liga_inglaterra_primeira AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'inglaterra' AND d.level = 1;
CREATE OR REPLACE VIEW liga_inglaterra_segunda AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'inglaterra' AND d.level = 2;
CREATE OR REPLACE VIEW liga_franca_primeira AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'franca' AND d.level = 1;
CREATE OR REPLACE VIEW liga_franca_segunda AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'franca' AND d.level = 2;
CREATE OR REPLACE VIEW liga_brasil_primeira AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'brasil' AND d.level = 1;
CREATE OR REPLACE VIEW liga_brasil_segunda AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'brasil' AND d.level = 2;
CREATE OR REPLACE VIEW liga_espanha_primeira AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'espanha' AND d.level = 1;
CREATE OR REPLACE VIEW liga_espanha_segunda AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'espanha' AND d.level = 2;
CREATE OR REPLACE VIEW liga_alemanha_primeira AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'alemanha' AND d.level = 1;
CREATE OR REPLACE VIEW liga_alemanha_segunda AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'alemanha' AND d.level = 2;
CREATE OR REPLACE VIEW liga_italia_primeira AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'italia' AND d.level = 1;
CREATE OR REPLACE VIEW liga_italia_segunda AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'italia' AND d.level = 2;
CREATE OR REPLACE VIEW liga_portugal_primeira AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'portugal' AND d.level = 1;
CREATE OR REPLACE VIEW liga_portugal_segunda AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'portugal' AND d.level = 2;

INSERT IGNORE INTO countries (name, code) VALUES
('Inglaterra','ENG'),('França','FRA'),('Brasil','BRA'),('Espanha','ESP'),
('Alemanha','GER'),('Itália','ITA'),('Portugal','POR');

INSERT IGNORE INTO leagues (country_id, name, slug)
SELECT id, 'Liga Inglesa', 'inglaterra' FROM countries WHERE code='ENG';
INSERT IGNORE INTO leagues (country_id, name, slug)
SELECT id, 'Liga Francesa', 'franca' FROM countries WHERE code='FRA';
INSERT IGNORE INTO leagues (country_id, name, slug)
SELECT id, 'Liga Brasileira', 'brasil' FROM countries WHERE code='BRA';
INSERT IGNORE INTO leagues (country_id, name, slug)
SELECT id, 'Liga Espanhola', 'espanha' FROM countries WHERE code='ESP';
INSERT IGNORE INTO leagues (country_id, name, slug)
SELECT id, 'Liga Alemã', 'alemanha' FROM countries WHERE code='GER';
INSERT IGNORE INTO leagues (country_id, name, slug)
SELECT id, 'Liga Italiana', 'italia' FROM countries WHERE code='ITA';
INSERT IGNORE INTO leagues (country_id, name, slug)
SELECT id, 'Liga Portuguesa', 'portugal' FROM countries WHERE code='POR';

INSERT IGNORE INTO divisions (league_id, name, level)
SELECT id, 'Primeira Divisão', 1 FROM leagues;
INSERT IGNORE INTO divisions (league_id, name, level)
SELECT id, 'Segunda Divisão', 2 FROM leagues;

INSERT IGNORE INTO clubs (division_id, name, slug, reputation)
SELECT d.id, x.name, x.slug, x.rep
FROM divisions d
JOIN leagues l ON l.id = d.league_id
JOIN (
  SELECT 'inglaterra' league_slug, 1 level, 'Manchester Vermelho' name, 'manchester-vermelho' slug, 88 rep UNION ALL
  SELECT 'inglaterra', 1, 'Londres Azul', 'londres-azul', 84 UNION ALL
  SELECT 'inglaterra', 2, 'Leeds City', 'leeds-city', 65 UNION ALL
  SELECT 'inglaterra', 2, 'Nottingham Athletic', 'nottingham-athletic', 62 UNION ALL
  SELECT 'franca', 1, 'Paris Estrela', 'paris-estrela', 90 UNION ALL
  SELECT 'franca', 1, 'Marselha Porto', 'marselha-porto', 78 UNION ALL
  SELECT 'franca', 2, 'Bordeaux Clube', 'bordeaux-clube', 61 UNION ALL
  SELECT 'franca', 2, 'Saint Denis', 'saint-denis', 58 UNION ALL
  SELECT 'brasil', 1, 'Rio Rubro', 'rio-rubro', 86 UNION ALL
  SELECT 'brasil', 1, 'São Paulo Tricolor', 'sao-paulo-tricolor', 84 UNION ALL
  SELECT 'brasil', 2, 'Campinas FC', 'campinas-fc', 61 UNION ALL
  SELECT 'brasil', 2, 'Recife Clube', 'recife-clube', 60 UNION ALL
  SELECT 'espanha', 1, 'Madrid Real', 'madrid-real', 91 UNION ALL
  SELECT 'espanha', 1, 'Barcelona Azulgrana', 'barcelona-azulgrana', 89 UNION ALL
  SELECT 'espanha', 2, 'Zaragoza União', 'zaragoza-uniao', 62 UNION ALL
  SELECT 'espanha', 2, 'Oviedo Norte', 'oviedo-norte', 60 UNION ALL
  SELECT 'alemanha', 1, 'Munique Vermelho', 'munique-vermelho', 91 UNION ALL
  SELECT 'alemanha', 1, 'Dortmund Amarelo', 'dortmund-amarelo', 85 UNION ALL
  SELECT 'alemanha', 2, 'Hamburgo SV', 'hamburgo-sv', 64 UNION ALL
  SELECT 'alemanha', 2, 'Düsseldorf 95', 'dusseldorf-95', 60 UNION ALL
  SELECT 'italia', 1, 'Milão Preto Azul', 'milao-preto-azul', 87 UNION ALL
  SELECT 'italia', 1, 'Turim Branco', 'turim-branco', 85 UNION ALL
  SELECT 'italia', 2, 'Palermo Rosa', 'palermo-rosa', 62 UNION ALL
  SELECT 'italia', 2, 'Bari Sul', 'bari-sul', 59 UNION ALL
  SELECT 'portugal', 1, 'Lisboa Águia', 'lisboa-aguia', 84 UNION ALL
  SELECT 'portugal', 1, 'Porto Dragão', 'porto-dragao', 84 UNION ALL
  SELECT 'portugal', 2, 'Coimbra Acadêmica', 'coimbra-academica', 59 UNION ALL
  SELECT 'portugal', 2, 'Madeira Verde', 'madeira-verde', 58
) x ON x.league_slug = l.slug AND x.level = d.level;

INSERT IGNORE INTO seasons (id, current_year, current_round, active)
VALUES (1, 2026, 1, 1);
