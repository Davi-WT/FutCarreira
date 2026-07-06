USE futcarreira;

START TRANSACTION;

DELETE FROM match_events;
DELETE FROM fixtures;
DELETE FROM transfer_offers;
DELETE FROM players;

INSERT INTO countries (name, code) VALUES
('Inglaterra','ENG'),
('Alemanha','GER'),
('Espanha','ESP'),
('Itália','ITA'),
('Brasil','BRA'),
('Portugal','POR'),
('Holanda','NED'),
('Argentina','ARG'),
('França','FRA'),
('Uruguai','URU')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO leagues (country_id, name, slug)
SELECT id, 'Liga Inglesa', 'inglaterra' FROM countries WHERE code = 'ENG'
ON DUPLICATE KEY UPDATE country_id = VALUES(country_id), name = VALUES(name);
INSERT INTO leagues (country_id, name, slug)
SELECT id, 'Liga Alemã', 'alemanha' FROM countries WHERE code = 'GER'
ON DUPLICATE KEY UPDATE country_id = VALUES(country_id), name = VALUES(name);
INSERT INTO leagues (country_id, name, slug)
SELECT id, 'Liga Espanhola', 'espanha' FROM countries WHERE code = 'ESP'
ON DUPLICATE KEY UPDATE country_id = VALUES(country_id), name = VALUES(name);
INSERT INTO leagues (country_id, name, slug)
SELECT id, 'Liga Italiana', 'italia' FROM countries WHERE code = 'ITA'
ON DUPLICATE KEY UPDATE country_id = VALUES(country_id), name = VALUES(name);
INSERT INTO leagues (country_id, name, slug)
SELECT id, 'Liga Brasileira', 'brasil' FROM countries WHERE code = 'BRA'
ON DUPLICATE KEY UPDATE country_id = VALUES(country_id), name = VALUES(name);
INSERT INTO leagues (country_id, name, slug)
SELECT id, 'Liga Portuguesa', 'portugal' FROM countries WHERE code = 'POR'
ON DUPLICATE KEY UPDATE country_id = VALUES(country_id), name = VALUES(name);
INSERT INTO leagues (country_id, name, slug)
SELECT id, 'Liga Holandesa', 'holanda' FROM countries WHERE code = 'NED'
ON DUPLICATE KEY UPDATE country_id = VALUES(country_id), name = VALUES(name);
INSERT INTO leagues (country_id, name, slug)
SELECT id, 'Liga Argentina', 'argentina' FROM countries WHERE code = 'ARG'
ON DUPLICATE KEY UPDATE country_id = VALUES(country_id), name = VALUES(name);
INSERT INTO leagues (country_id, name, slug)
SELECT id, 'Liga Francesa', 'franca' FROM countries WHERE code = 'FRA'
ON DUPLICATE KEY UPDATE country_id = VALUES(country_id), name = VALUES(name);
INSERT INTO leagues (country_id, name, slug)
SELECT id, 'Liga Uruguaia', 'uruguai' FROM countries WHERE code = 'URU'
ON DUPLICATE KEY UPDATE country_id = VALUES(country_id), name = VALUES(name);

INSERT INTO divisions (league_id, name, level)
SELECT id, 'Primeira Divisão', 1 FROM leagues
ON DUPLICATE KEY UPDATE name = VALUES(name);
INSERT INTO divisions (league_id, name, level)
SELECT id, 'Segunda Divisão', 2 FROM leagues
ON DUPLICATE KEY UPDATE name = VALUES(name);

DELETE c FROM clubs c
JOIN divisions d ON d.id = c.division_id
JOIN leagues l ON l.id = d.league_id
WHERE l.slug IN ('inglaterra','alemanha','espanha','italia','brasil','portugal','holanda','argentina','franca','uruguai');

INSERT INTO clubs (division_id, name, slug, reputation)
SELECT d.id, x.name, x.slug, x.overall
FROM divisions d
JOIN leagues l ON l.id = d.league_id
JOIN (
  SELECT 'inglaterra' league_slug, 1 level, 'Manchester City' name, 'manchester-city' slug, 97 overall UNION ALL
  SELECT 'inglaterra', 1, 'Arsenal', 'arsenal', 96 UNION ALL
  SELECT 'inglaterra', 1, 'Liverpool', 'liverpool', 95 UNION ALL
  SELECT 'inglaterra', 1, 'Chelsea', 'chelsea', 90 UNION ALL
  SELECT 'inglaterra', 1, 'Manchester United', 'manchester-united', 89 UNION ALL
  SELECT 'inglaterra', 1, 'Tottenham Hotspur', 'tottenham-hotspur', 88 UNION ALL
  SELECT 'inglaterra', 1, 'Newcastle United', 'newcastle-united', 87 UNION ALL
  SELECT 'inglaterra', 1, 'Aston Villa', 'aston-villa', 87 UNION ALL
  SELECT 'inglaterra', 1, 'Brighton & Hove Albion', 'brighton-hove-albion', 84 UNION ALL
  SELECT 'inglaterra', 1, 'West Ham United', 'west-ham-united', 83 UNION ALL
  SELECT 'inglaterra', 1, 'Crystal Palace', 'crystal-palace', 81 UNION ALL
  SELECT 'inglaterra', 1, 'Bournemouth', 'bournemouth', 80 UNION ALL
  SELECT 'inglaterra', 1, 'Fulham', 'fulham', 80 UNION ALL
  SELECT 'inglaterra', 1, 'Wolverhampton Wanderers', 'wolverhampton-wanderers', 79 UNION ALL
  SELECT 'inglaterra', 1, 'Everton', 'everton', 78 UNION ALL
  SELECT 'inglaterra', 1, 'Brentford', 'brentford', 78 UNION ALL
  SELECT 'inglaterra', 1, 'Nottingham Forest', 'nottingham-forest', 77 UNION ALL
  SELECT 'inglaterra', 1, 'Leicester City', 'leicester-city', 76 UNION ALL
  SELECT 'inglaterra', 1, 'Ipswich Town', 'ipswich-town', 74 UNION ALL
  SELECT 'inglaterra', 1, 'Southampton', 'southampton', 73 UNION ALL
  SELECT 'inglaterra', 2, 'Leeds United', 'leeds-united', 76 UNION ALL
  SELECT 'inglaterra', 2, 'Burnley', 'burnley', 75 UNION ALL
  SELECT 'inglaterra', 2, 'Luton Town', 'luton-town', 74 UNION ALL
  SELECT 'inglaterra', 2, 'Sheffield United', 'sheffield-united', 74 UNION ALL
  SELECT 'inglaterra', 2, 'Coventry City', 'coventry-city', 72 UNION ALL
  SELECT 'inglaterra', 2, 'Middlesbrough', 'middlesbrough', 72 UNION ALL
  SELECT 'inglaterra', 2, 'West Bromwich Albion', 'west-bromwich-albion', 72 UNION ALL
  SELECT 'inglaterra', 2, 'Sunderland', 'sunderland', 71 UNION ALL
  SELECT 'inglaterra', 2, 'Norwich City', 'norwich-city', 71 UNION ALL
  SELECT 'inglaterra', 2, 'Hull City', 'hull-city', 70 UNION ALL
  SELECT 'inglaterra', 2, 'Bristol City', 'bristol-city', 69 UNION ALL
  SELECT 'inglaterra', 2, 'Watford', 'watford', 69 UNION ALL
  SELECT 'inglaterra', 2, 'Swansea City', 'swansea-city', 69 UNION ALL
  SELECT 'inglaterra', 2, 'Stoke City', 'stoke-city', 68 UNION ALL
  SELECT 'inglaterra', 2, 'Millwall', 'millwall', 68 UNION ALL
  SELECT 'inglaterra', 2, 'Preston North End', 'preston-north-end', 68 UNION ALL
  SELECT 'inglaterra', 2, 'Blackburn Rovers', 'blackburn-rovers', 67 UNION ALL
  SELECT 'inglaterra', 2, 'Queens Park Rangers', 'queens-park-rangers', 67 UNION ALL
  SELECT 'inglaterra', 2, 'Cardiff City', 'cardiff-city', 67 UNION ALL
  SELECT 'inglaterra', 2, 'Sheffield Wednesday', 'sheffield-wednesday', 66 UNION ALL
  SELECT 'inglaterra', 2, 'Plymouth Argyle', 'plymouth-argyle', 65 UNION ALL
  SELECT 'inglaterra', 2, 'Portsmouth', 'portsmouth', 64 UNION ALL
  SELECT 'inglaterra', 2, 'Derby County', 'derby-county', 64 UNION ALL
  SELECT 'inglaterra', 2, 'Oxford United', 'oxford-united', 63 UNION ALL

  SELECT 'alemanha', 1, 'Bayern de Munique', 'bayern-de-munique', 96 UNION ALL
  SELECT 'alemanha', 1, 'Bayer Leverkusen', 'bayer-leverkusen', 93 UNION ALL
  SELECT 'alemanha', 1, 'Borussia Dortmund', 'borussia-dortmund', 91 UNION ALL
  SELECT 'alemanha', 1, 'RB Leipzig', 'rb-leipzig', 89 UNION ALL
  SELECT 'alemanha', 1, 'VfB Stuttgart', 'vfb-stuttgart', 86 UNION ALL
  SELECT 'alemanha', 1, 'Eintracht Frankfurt', 'eintracht-frankfurt', 84 UNION ALL
  SELECT 'alemanha', 1, 'Hoffenheim', 'hoffenheim', 80 UNION ALL
  SELECT 'alemanha', 1, 'Freiburg', 'freiburg', 80 UNION ALL
  SELECT 'alemanha', 1, 'Werder Bremen', 'werder-bremen', 78 UNION ALL
  SELECT 'alemanha', 1, 'Wolfsburg', 'wolfsburg', 78 UNION ALL
  SELECT 'alemanha', 1, 'Augsburg', 'augsburg', 76 UNION ALL
  SELECT 'alemanha', 1, 'Borussia Mönchengladbach', 'borussia-monchengladbach', 76 UNION ALL
  SELECT 'alemanha', 1, 'Mainz 05', 'mainz-05', 75 UNION ALL
  SELECT 'alemanha', 1, 'Heidenheim', 'heidenheim', 75 UNION ALL
  SELECT 'alemanha', 1, 'Union Berlin', 'union-berlin', 74 UNION ALL
  SELECT 'alemanha', 1, 'VfL Bochum', 'vfl-bochum', 72 UNION ALL
  SELECT 'alemanha', 1, 'St. Pauli', 'st-pauli', 72 UNION ALL
  SELECT 'alemanha', 1, 'Holstein Kiel', 'holstein-kiel', 70 UNION ALL
  SELECT 'alemanha', 2, 'Köln', 'koln', 74 UNION ALL
  SELECT 'alemanha', 2, 'Darmstadt 98', 'darmstadt-98', 72 UNION ALL
  SELECT 'alemanha', 2, 'Fortuna Düsseldorf', 'fortuna-dusseldorf', 71 UNION ALL
  SELECT 'alemanha', 2, 'Hamburger SV', 'hamburger-sv', 71 UNION ALL
  SELECT 'alemanha', 2, 'Schalke 04', 'schalke-04', 70 UNION ALL
  SELECT 'alemanha', 2, 'Hertha Berlim', 'hertha-berlim', 70 UNION ALL
  SELECT 'alemanha', 2, 'Hannover 96', 'hannover-96', 69 UNION ALL
  SELECT 'alemanha', 2, 'Paderborn 07', 'paderborn-07', 68 UNION ALL
  SELECT 'alemanha', 2, 'Karlsruher SC', 'karlsruher-sc', 68 UNION ALL
  SELECT 'alemanha', 2, 'Nürnberg', 'nurnberg', 67 UNION ALL
  SELECT 'alemanha', 2, 'Kaiserslautern', 'kaiserslautern', 67 UNION ALL
  SELECT 'alemanha', 2, 'Greuther Fürth', 'greuther-furth', 67 UNION ALL
  SELECT 'alemanha', 2, 'Magdeburg', 'magdeburg', 66 UNION ALL
  SELECT 'alemanha', 2, 'Eintracht Braunschweig', 'eintracht-braunschweig', 65 UNION ALL
  SELECT 'alemanha', 2, 'Wehen Wiesbaden', 'wehen-wiesbaden', 64 UNION ALL
  SELECT 'alemanha', 2, 'Hansa Rostock', 'hansa-rostock', 64 UNION ALL
  SELECT 'alemanha', 2, 'Osnabrück', 'osnabruck', 63 UNION ALL
  SELECT 'alemanha', 2, 'Elversberg', 'elversberg', 63 UNION ALL

  SELECT 'espanha', 1, 'Real Madrid', 'real-madrid', 98 UNION ALL
  SELECT 'espanha', 1, 'Barcelona', 'barcelona', 95 UNION ALL
  SELECT 'espanha', 1, 'Atlético de Madrid', 'atletico-de-madrid', 91 UNION ALL
  SELECT 'espanha', 1, 'Real Sociedad', 'real-sociedad', 86 UNION ALL
  SELECT 'espanha', 1, 'Girona', 'girona', 85 UNION ALL
  SELECT 'espanha', 1, 'Athletic Bilbao', 'athletic-bilbao', 85 UNION ALL
  SELECT 'espanha', 1, 'Real Betis', 'real-betis', 82 UNION ALL
  SELECT 'espanha', 1, 'Villarreal', 'villarreal', 82 UNION ALL
  SELECT 'espanha', 1, 'Sevilla', 'sevilla', 81 UNION ALL
  SELECT 'espanha', 1, 'Valencia', 'valencia', 80 UNION ALL
  SELECT 'espanha', 1, 'Osasuna', 'osasuna', 79 UNION ALL
  SELECT 'espanha', 1, 'Getafe', 'getafe', 78 UNION ALL
  SELECT 'espanha', 1, 'Celta de Vigo', 'celta-de-vigo', 78 UNION ALL
  SELECT 'espanha', 1, 'Mallorca', 'mallorca', 77 UNION ALL
  SELECT 'espanha', 1, 'Deportivo Alavés', 'deportivo-alaves', 76 UNION ALL
  SELECT 'espanha', 1, 'Rayo Vallecano', 'rayo-vallecano', 76 UNION ALL
  SELECT 'espanha', 1, 'Las Palmas', 'las-palmas', 75 UNION ALL
  SELECT 'espanha', 1, 'Leganés', 'leganes', 74 UNION ALL
  SELECT 'espanha', 1, 'Real Valladolid', 'real-valladolid', 73 UNION ALL
  SELECT 'espanha', 1, 'Espanyol', 'espanyol', 73 UNION ALL
  SELECT 'espanha', 2, 'Cádiz', 'cadiz', 73 UNION ALL
  SELECT 'espanha', 2, 'Almería', 'almeria', 72 UNION ALL
  SELECT 'espanha', 2, 'Granada', 'granada', 72 UNION ALL
  SELECT 'espanha', 2, 'Eibar', 'eibar', 71 UNION ALL
  SELECT 'espanha', 2, 'Real Oviedo', 'real-oviedo', 70 UNION ALL
  SELECT 'espanha', 2, 'Sporting Gijón', 'sporting-gijon', 70 UNION ALL
  SELECT 'espanha', 2, 'Elche', 'elche', 69 UNION ALL
  SELECT 'espanha', 2, 'Racing Santander', 'racing-santander', 69 UNION ALL
  SELECT 'espanha', 2, 'Levante', 'levante', 69 UNION ALL
  SELECT 'espanha', 2, 'Burgos', 'burgos', 68 UNION ALL
  SELECT 'espanha', 2, 'Tenerife', 'tenerife', 68 UNION ALL
  SELECT 'espanha', 2, 'Real Zaragoza', 'real-zaragoza', 68 UNION ALL
  SELECT 'espanha', 2, 'Ferrol', 'ferrol', 67 UNION ALL
  SELECT 'espanha', 2, 'Albacete', 'albacete', 66 UNION ALL
  SELECT 'espanha', 2, 'Cartagena', 'cartagena', 66 UNION ALL
  SELECT 'espanha', 2, 'Huesca', 'huesca', 66 UNION ALL
  SELECT 'espanha', 2, 'Mirandés', 'mirandes', 65 UNION ALL
  SELECT 'espanha', 2, 'Eldense', 'eldense', 65 UNION ALL
  SELECT 'espanha', 2, 'Deportivo La Coruña', 'deportivo-la-coruna', 65 UNION ALL
  SELECT 'espanha', 2, 'Castellón', 'castellon', 64 UNION ALL
  SELECT 'espanha', 2, 'Córdoba', 'cordoba', 64 UNION ALL
  SELECT 'espanha', 2, 'Málaga', 'malaga', 64 UNION ALL

  SELECT 'italia', 1, 'Inter de Milão', 'inter-de-milao', 94 UNION ALL
  SELECT 'italia', 1, 'Juventus', 'juventus', 91 UNION ALL
  SELECT 'italia', 1, 'Milan', 'milan', 90 UNION ALL
  SELECT 'italia', 1, 'Atalanta', 'atalanta', 89 UNION ALL
  SELECT 'italia', 1, 'Napoli', 'napoli', 88 UNION ALL
  SELECT 'italia', 1, 'Roma', 'roma', 86 UNION ALL
  SELECT 'italia', 1, 'Lazio', 'lazio', 85 UNION ALL
  SELECT 'italia', 1, 'Fiorentina', 'fiorentina', 83 UNION ALL
  SELECT 'italia', 1, 'Bologna', 'bologna', 82 UNION ALL
  SELECT 'italia', 1, 'Torino', 'torino', 79 UNION ALL
  SELECT 'italia', 1, 'Monza', 'monza', 77 UNION ALL
  SELECT 'italia', 1, 'Genoa', 'genoa', 77 UNION ALL
  SELECT 'italia', 1, 'Lecce', 'lecce', 75 UNION ALL
  SELECT 'italia', 1, 'Udinese', 'udinese', 75 UNION ALL
  SELECT 'italia', 1, 'Cagliari', 'cagliari', 74 UNION ALL
  SELECT 'italia', 1, 'Empoli', 'empoli', 74 UNION ALL
  SELECT 'italia', 1, 'Verona', 'verona', 73 UNION ALL
  SELECT 'italia', 1, 'Parma', 'parma', 73 UNION ALL
  SELECT 'italia', 1, 'Como', 'como', 72 UNION ALL
  SELECT 'italia', 1, 'Venezia', 'venezia', 71 UNION ALL
  SELECT 'italia', 2, 'Frosinone', 'frosinone', 72 UNION ALL
  SELECT 'italia', 2, 'Sassuolo', 'sassuolo', 72 UNION ALL
  SELECT 'italia', 2, 'Salernitana', 'salernitana', 71 UNION ALL
  SELECT 'italia', 2, 'Cremonese', 'cremonese', 70 UNION ALL
  SELECT 'italia', 2, 'Catanzaro', 'catanzaro', 69 UNION ALL
  SELECT 'italia', 2, 'Palermo', 'palermo', 69 UNION ALL
  SELECT 'italia', 2, 'Sampdoria', 'sampdoria', 69 UNION ALL
  SELECT 'italia', 2, 'Brescia', 'brescia', 68 UNION ALL
  SELECT 'italia', 2, 'Cosenza', 'cosenza', 67 UNION ALL
  SELECT 'italia', 2, 'Modena', 'modena', 67 UNION ALL
  SELECT 'italia', 2, 'Reggiana', 'reggiana', 66 UNION ALL
  SELECT 'italia', 2, 'Südtirol', 'sudtirol', 66 UNION ALL
  SELECT 'italia', 2, 'Pisa', 'pisa', 66 UNION ALL
  SELECT 'italia', 2, 'Cittadella', 'cittadella', 65 UNION ALL
  SELECT 'italia', 2, 'Spezia', 'spezia', 65 UNION ALL
  SELECT 'italia', 2, 'Bari', 'bari', 65 UNION ALL
  SELECT 'italia', 2, 'Mantova', 'mantova', 64 UNION ALL
  SELECT 'italia', 2, 'Carrarese', 'carrarese', 63 UNION ALL
  SELECT 'italia', 2, 'Cesena', 'cesena', 63 UNION ALL
  SELECT 'italia', 2, 'Juve Stabia', 'juve-stabia', 62 UNION ALL

  SELECT 'brasil', 1, 'Palmeiras', 'palmeiras', 86 UNION ALL
  SELECT 'brasil', 1, 'Flamengo', 'flamengo', 86 UNION ALL
  SELECT 'brasil', 1, 'Atlético Mineiro', 'atletico-mineiro', 84 UNION ALL
  SELECT 'brasil', 1, 'Botafogo', 'botafogo', 84 UNION ALL
  SELECT 'brasil', 1, 'São Paulo', 'sao-paulo', 82 UNION ALL
  SELECT 'brasil', 1, 'Internacional', 'internacional', 82 UNION ALL
  SELECT 'brasil', 1, 'Fluminense', 'fluminense', 81 UNION ALL
  SELECT 'brasil', 1, 'Grêmio', 'gremio', 81 UNION ALL
  SELECT 'brasil', 1, 'Athletico Paranaense', 'athletico-paranaense', 80 UNION ALL
  SELECT 'brasil', 1, 'Cruzeiro', 'cruzeiro', 80 UNION ALL
  SELECT 'brasil', 1, 'Fortaleza', 'fortaleza', 80 UNION ALL
  SELECT 'brasil', 1, 'Bahia', 'bahia', 79 UNION ALL
  SELECT 'brasil', 1, 'Corinthians', 'corinthians', 79 UNION ALL
  SELECT 'brasil', 1, 'Vasco da Gama', 'vasco-da-gama', 78 UNION ALL
  SELECT 'brasil', 1, 'Red Bull Bragantino', 'red-bull-bragantino', 78 UNION ALL
  SELECT 'brasil', 1, 'Cuiabá', 'cuiaba', 75 UNION ALL
  SELECT 'brasil', 1, 'Criciúma', 'criciuma', 74 UNION ALL
  SELECT 'brasil', 1, 'Juventude', 'juventude', 73 UNION ALL
  SELECT 'brasil', 1, 'Vitória', 'vitoria', 73 UNION ALL
  SELECT 'brasil', 1, 'Atlético Goianiense', 'atletico-goianiense', 72 UNION ALL
  SELECT 'brasil', 2, 'Santos', 'santos', 77 UNION ALL
  SELECT 'brasil', 2, 'América Mineiro', 'america-mineiro', 73 UNION ALL
  SELECT 'brasil', 2, 'Goiás', 'goias', 72 UNION ALL
  SELECT 'brasil', 2, 'Coritiba', 'coritiba', 72 UNION ALL
  SELECT 'brasil', 2, 'Sport Recife', 'sport-recife', 71 UNION ALL
  SELECT 'brasil', 2, 'Ceará', 'ceara', 71 UNION ALL
  SELECT 'brasil', 2, 'Novorizontino', 'novorizontino', 69 UNION ALL
  SELECT 'brasil', 2, 'Mirassol', 'mirassol', 68 UNION ALL
  SELECT 'brasil', 2, 'Operário-PR', 'operario-pr', 67 UNION ALL
  SELECT 'brasil', 2, 'Vila Nova', 'vila-nova', 67 UNION ALL
  SELECT 'brasil', 2, 'Avaí', 'avai', 66 UNION ALL
  SELECT 'brasil', 2, 'Ponte Preta', 'ponte-preta', 66 UNION ALL
  SELECT 'brasil', 2, 'CRB', 'crb', 65 UNION ALL
  SELECT 'brasil', 2, 'Chapecoense', 'chapecoense', 65 UNION ALL
  SELECT 'brasil', 2, 'Guarani', 'guarani', 64 UNION ALL
  SELECT 'brasil', 2, 'Botafogo-SP', 'botafogo-sp', 64 UNION ALL
  SELECT 'brasil', 2, 'Ituano', 'ituano', 63 UNION ALL
  SELECT 'brasil', 2, 'Brusque', 'brusque', 62 UNION ALL
  SELECT 'brasil', 2, 'Amazonas', 'amazonas', 62 UNION ALL
  SELECT 'brasil', 2, 'Paysandu', 'paysandu', 62 UNION ALL

  SELECT 'portugal', 1, 'Sporting CP', 'sporting-cp', 89 UNION ALL
  SELECT 'portugal', 1, 'Benfica', 'benfica', 88 UNION ALL
  SELECT 'portugal', 1, 'Porto', 'porto', 87 UNION ALL
  SELECT 'portugal', 1, 'Sporting de Braga', 'sporting-de-braga', 82 UNION ALL
  SELECT 'portugal', 1, 'Vitória de Guimarães', 'vitoria-de-guimaraes', 79 UNION ALL
  SELECT 'portugal', 1, 'Moreirense', 'moreirense', 75 UNION ALL
  SELECT 'portugal', 1, 'Arouca', 'arouca', 74 UNION ALL
  SELECT 'portugal', 1, 'Famalicão', 'famalicao', 74 UNION ALL
  SELECT 'portugal', 1, 'Casa Pia', 'casa-pia', 72 UNION ALL
  SELECT 'portugal', 1, 'Farense', 'farense', 71 UNION ALL
  SELECT 'portugal', 1, 'Rio Ave', 'rio-ave', 71 UNION ALL
  SELECT 'portugal', 1, 'Gil Vicente', 'gil-vicente', 70 UNION ALL
  SELECT 'portugal', 1, 'Estoril Praia', 'estoril-praia', 70 UNION ALL
  SELECT 'portugal', 1, 'Boavista', 'boavista', 69 UNION ALL
  SELECT 'portugal', 1, 'Estrela da Amadora', 'estrela-da-amadora', 68 UNION ALL
  SELECT 'portugal', 1, 'Santa Clara', 'santa-clara', 68 UNION ALL
  SELECT 'portugal', 1, 'Nacional', 'nacional-portugal', 67 UNION ALL
  SELECT 'portugal', 1, 'AVS Futebol SAD', 'avs-futebol-sad', 66 UNION ALL
  SELECT 'portugal', 2, 'Chaves', 'chaves', 67 UNION ALL
  SELECT 'portugal', 2, 'Vizela', 'vizela', 66 UNION ALL
  SELECT 'portugal', 2, 'Portimonense', 'portimonense', 66 UNION ALL
  SELECT 'portugal', 2, 'Marítimo', 'maritimo', 65 UNION ALL
  SELECT 'portugal', 2, 'Paços de Ferreira', 'pacos-de-ferreira', 64 UNION ALL
  SELECT 'portugal', 2, 'Tondela', 'tondela', 64 UNION ALL
  SELECT 'portugal', 2, 'Torreense', 'torreense', 63 UNION ALL
  SELECT 'portugal', 2, 'Benfica B', 'benfica-b', 63 UNION ALL
  SELECT 'portugal', 2, 'Porto B', 'porto-b', 63 UNION ALL
  SELECT 'portugal', 2, 'Mafra', 'mafra', 62 UNION ALL
  SELECT 'portugal', 2, 'Leixões', 'leixoes', 62 UNION ALL
  SELECT 'portugal', 2, 'Académico de Viseu', 'academico-de-viseu', 62 UNION ALL
  SELECT 'portugal', 2, 'Penafiel', 'penafiel', 61 UNION ALL
  SELECT 'portugal', 2, 'União de Leiria', 'uniao-de-leiria', 61 UNION ALL
  SELECT 'portugal', 2, 'Feirense', 'feirense', 60 UNION ALL
  SELECT 'portugal', 2, 'Alverca', 'alverca', 59 UNION ALL
  SELECT 'portugal', 2, 'Felgueiras', 'felgueiras', 58 UNION ALL
  SELECT 'portugal', 2, 'Oliveirense', 'oliveirense', 57 UNION ALL

  SELECT 'holanda', 1, 'PSV Eindhoven', 'psv-eindhoven', 88 UNION ALL
  SELECT 'holanda', 1, 'Feyenoord', 'feyenoord', 86 UNION ALL
  SELECT 'holanda', 1, 'Ajax', 'ajax', 84 UNION ALL
  SELECT 'holanda', 1, 'AZ Alkmaar', 'az-alkmaar', 81 UNION ALL
  SELECT 'holanda', 1, 'FC Twente', 'fc-twente', 80 UNION ALL
  SELECT 'holanda', 1, 'NEC Nijmegen', 'nec-nijmegen', 75 UNION ALL
  SELECT 'holanda', 1, 'FC Utrecht', 'fc-utrecht', 75 UNION ALL
  SELECT 'holanda', 1, 'Sparta Rotterdam', 'sparta-rotterdam', 74 UNION ALL
  SELECT 'holanda', 1, 'Go Ahead Eagles', 'go-ahead-eagles', 73 UNION ALL
  SELECT 'holanda', 1, 'Fortuna Sittard', 'fortuna-sittard', 72 UNION ALL
  SELECT 'holanda', 1, 'Heerenveen', 'heerenveen', 72 UNION ALL
  SELECT 'holanda', 1, 'PEC Zwolle', 'pec-zwolle', 70 UNION ALL
  SELECT 'holanda', 1, 'Almere City', 'almere-city', 69 UNION ALL
  SELECT 'holanda', 1, 'Heracles Almelo', 'heracles-almelo', 69 UNION ALL
  SELECT 'holanda', 1, 'RKC Waalwijk', 'rkc-waalwijk', 68 UNION ALL
  SELECT 'holanda', 1, 'Willem II', 'willem-ii', 68 UNION ALL
  SELECT 'holanda', 1, 'Groningen', 'groningen', 67 UNION ALL
  SELECT 'holanda', 1, 'NAC Breda', 'nac-breda', 66 UNION ALL
  SELECT 'holanda', 2, 'Excelsior', 'excelsior', 67 UNION ALL
  SELECT 'holanda', 2, 'Volendam', 'volendam', 66 UNION ALL
  SELECT 'holanda', 2, 'Vitesse', 'vitesse', 65 UNION ALL
  SELECT 'holanda', 2, 'Roda JC', 'roda-jc', 64 UNION ALL
  SELECT 'holanda', 2, 'ADO Den Haag', 'ado-den-haag', 64 UNION ALL
  SELECT 'holanda', 2, 'De Graafschap', 'de-graafschap', 63 UNION ALL
  SELECT 'holanda', 2, 'FC Emmen', 'fc-emmen', 63 UNION ALL
  SELECT 'holanda', 2, 'FC Dordrecht', 'fc-dordrecht', 62 UNION ALL
  SELECT 'holanda', 2, 'Jong Ajax', 'jong-ajax', 62 UNION ALL
  SELECT 'holanda', 2, 'Jong PSV', 'jong-psv', 62 UNION ALL
  SELECT 'holanda', 2, 'Cambuur', 'cambuur', 61 UNION ALL
  SELECT 'holanda', 2, 'VVV-Venlo', 'vvv-venlo', 60 UNION ALL
  SELECT 'holanda', 2, 'Helmond Sport', 'helmond-sport', 59 UNION ALL
  SELECT 'holanda', 2, 'Jong AZ', 'jong-az', 59 UNION ALL
  SELECT 'holanda', 2, 'MVV Maastricht', 'mvv-maastricht', 58 UNION ALL
  SELECT 'holanda', 2, 'FC Den Bosch', 'fc-den-bosch', 58 UNION ALL
  SELECT 'holanda', 2, 'Telstar', 'telstar', 57 UNION ALL
  SELECT 'holanda', 2, 'Eindhoven', 'eindhoven', 57 UNION ALL
  SELECT 'holanda', 2, 'TOP Oss', 'top-oss', 56 UNION ALL
  SELECT 'holanda', 2, 'Jong Utrecht', 'jong-utrecht', 55 UNION ALL

  SELECT 'argentina', 1, 'River Plate', 'river-plate-argentina', 85 UNION ALL
  SELECT 'argentina', 1, 'Boca Juniors', 'boca-juniors', 83 UNION ALL
  SELECT 'argentina', 1, 'Racing Club', 'racing-club', 81 UNION ALL
  SELECT 'argentina', 1, 'Talleres', 'talleres', 80 UNION ALL
  SELECT 'argentina', 1, 'Estudiantes de La Plata', 'estudiantes-de-la-plata', 79 UNION ALL
  SELECT 'argentina', 1, 'San Lorenzo', 'san-lorenzo', 78 UNION ALL
  SELECT 'argentina', 1, 'Independiente', 'independiente', 77 UNION ALL
  SELECT 'argentina', 1, 'Defensa y Justicia', 'defensa-y-justicia', 77 UNION ALL
  SELECT 'argentina', 1, 'Vélez Sarsfield', 'velez-sarsfield', 77 UNION ALL
  SELECT 'argentina', 1, 'Lanús', 'lanus', 76 UNION ALL
  SELECT 'argentina', 1, 'Rosario Central', 'rosario-central', 76 UNION ALL
  SELECT 'argentina', 1, 'Newell''s Old Boys', 'newells-old-boys', 75 UNION ALL
  SELECT 'argentina', 1, 'Argentinos Juniors', 'argentinos-juniors', 75 UNION ALL
  SELECT 'argentina', 1, 'Godoy Cruz', 'godoy-cruz', 75 UNION ALL
  SELECT 'argentina', 1, 'Belgrano', 'belgrano', 74 UNION ALL
  SELECT 'argentina', 1, 'Huracán', 'huracan', 74 UNION ALL
  SELECT 'argentina', 1, 'Atlético Tucumán', 'atletico-tucuman', 72 UNION ALL
  SELECT 'argentina', 1, 'Gimnasia y Esgrima', 'gimnasia-y-esgrima', 72 UNION ALL
  SELECT 'argentina', 1, 'Banfield', 'banfield', 71 UNION ALL
  SELECT 'argentina', 1, 'Platense', 'platense', 71 UNION ALL
  SELECT 'argentina', 1, 'Instituto', 'instituto', 71 UNION ALL
  SELECT 'argentina', 1, 'Central Córdoba', 'central-cordoba-argentina', 70 UNION ALL
  SELECT 'argentina', 1, 'Tigre', 'tigre', 70 UNION ALL
  SELECT 'argentina', 1, 'Barracas Central', 'barracas-central', 69 UNION ALL
  SELECT 'argentina', 1, 'Sarmiento', 'sarmiento', 68 UNION ALL
  SELECT 'argentina', 1, 'Unión de Santa Fe', 'union-de-santa-fe', 68 UNION ALL
  SELECT 'argentina', 1, 'Independiente Rivadavia', 'independiente-rivadavia', 67 UNION ALL
  SELECT 'argentina', 1, 'Riestra', 'riestra', 65 UNION ALL
  SELECT 'argentina', 2, 'Colón de Santa Fe', 'colon-de-santa-fe', 70 UNION ALL
  SELECT 'argentina', 2, 'Arsenal de Sarandí', 'arsenal-de-sarandi', 68 UNION ALL
  SELECT 'argentina', 2, 'Quilmes', 'quilmes', 67 UNION ALL
  SELECT 'argentina', 2, 'San Martín de Tucumán', 'san-martin-de-tucuman', 67 UNION ALL
  SELECT 'argentina', 2, 'Chacarita Juniors', 'chacarita-juniors', 66 UNION ALL
  SELECT 'argentina', 2, 'Ferro Carril Oeste', 'ferro-carril-oeste', 66 UNION ALL
  SELECT 'argentina', 2, 'Atlanta', 'atlanta', 65 UNION ALL
  SELECT 'argentina', 2, 'Aldosivi', 'aldosivi', 65 UNION ALL

  SELECT 'franca', 1, 'Paris Saint-Germain', 'paris-saint-germain', 94 UNION ALL
  SELECT 'franca', 1, 'Monaco', 'monaco', 86 UNION ALL
  SELECT 'franca', 1, 'Lille', 'lille', 85 UNION ALL
  SELECT 'franca', 1, 'Olympique de Marseille', 'olympique-de-marseille', 84 UNION ALL
  SELECT 'franca', 1, 'Lyon', 'lyon', 83 UNION ALL
  SELECT 'franca', 1, 'Lens', 'lens', 82 UNION ALL
  SELECT 'franca', 1, 'Nice', 'nice', 82 UNION ALL
  SELECT 'franca', 1, 'Rennes', 'rennes', 81 UNION ALL
  SELECT 'franca', 1, 'Reims', 'reims', 78 UNION ALL
  SELECT 'franca', 1, 'Toulouse', 'toulouse', 77 UNION ALL
  SELECT 'franca', 1, 'Montpellier', 'montpellier', 76 UNION ALL
  SELECT 'franca', 1, 'Strasbourg', 'strasbourg', 76 UNION ALL
  SELECT 'franca', 1, 'Brest', 'brest', 76 UNION ALL
  SELECT 'franca', 1, 'Nantes', 'nantes', 75 UNION ALL
  SELECT 'franca', 1, 'Le Havre', 'le-havre', 73 UNION ALL
  SELECT 'franca', 1, 'Auxerre', 'auxerre', 72 UNION ALL
  SELECT 'franca', 1, 'Angers', 'angers', 71 UNION ALL
  SELECT 'franca', 1, 'Saint-Étienne', 'saint-etienne', 71 UNION ALL
  SELECT 'franca', 2, 'Metz', 'metz', 71 UNION ALL
  SELECT 'franca', 2, 'Lorient', 'lorient', 71 UNION ALL
  SELECT 'franca', 2, 'Clermont', 'clermont', 70 UNION ALL
  SELECT 'franca', 2, 'Rodez', 'rodez', 67 UNION ALL
  SELECT 'franca', 2, 'Paris FC', 'paris-fc', 67 UNION ALL
  SELECT 'franca', 2, 'Caen', 'caen', 66 UNION ALL
  SELECT 'franca', 2, 'Laval', 'laval', 66 UNION ALL
  SELECT 'franca', 2, 'Guingamp', 'guingamp', 66 UNION ALL
  SELECT 'franca', 2, 'Pau FC', 'pau-fc', 65 UNION ALL
  SELECT 'franca', 2, 'Amiens', 'amiens', 65 UNION ALL
  SELECT 'franca', 2, 'Grenoble', 'grenoble', 65 UNION ALL
  SELECT 'franca', 2, 'Bastia', 'bastia', 64 UNION ALL
  SELECT 'franca', 2, 'Ajaccio', 'ajaccio', 64 UNION ALL
  SELECT 'franca', 2, 'Dunkerque', 'dunkerque', 63 UNION ALL
  SELECT 'franca', 2, 'Annecy', 'annecy', 63 UNION ALL
  SELECT 'franca', 2, 'Troyes', 'troyes', 63 UNION ALL
  SELECT 'franca', 2, 'Red Star', 'red-star', 62 UNION ALL
  SELECT 'franca', 2, 'Martigues', 'martigues', 60 UNION ALL

  SELECT 'uruguai', 1, 'Peñarol', 'penarol', 79 UNION ALL
  SELECT 'uruguai', 1, 'Nacional', 'nacional-uruguai', 78 UNION ALL
  SELECT 'uruguai', 1, 'Liverpool Montevideo', 'liverpool-montevideo', 73 UNION ALL
  SELECT 'uruguai', 1, 'Defensor Sporting', 'defensor-sporting', 73 UNION ALL
  SELECT 'uruguai', 1, 'Danubio', 'danubio', 71 UNION ALL
  SELECT 'uruguai', 1, 'Montevideo Wanderers', 'montevideo-wanderers', 70 UNION ALL
  SELECT 'uruguai', 1, 'Cerro Largo', 'cerro-largo', 68 UNION ALL
  SELECT 'uruguai', 1, 'Racing Montevideo', 'racing-montevideo', 68 UNION ALL
  SELECT 'uruguai', 1, 'Progreso', 'progreso', 67 UNION ALL
  SELECT 'uruguai', 1, 'River Plate Montevideo', 'river-plate-montevideo', 67 UNION ALL
  SELECT 'uruguai', 1, 'Cerro', 'cerro', 66 UNION ALL
  SELECT 'uruguai', 1, 'Boston River', 'boston-river', 66 UNION ALL
  SELECT 'uruguai', 1, 'Fénix', 'fenix', 65 UNION ALL
  SELECT 'uruguai', 1, 'Deportivo Maldonado', 'deportivo-maldonado', 65 UNION ALL
  SELECT 'uruguai', 1, 'Rampla Juniors', 'rampla-juniors', 64 UNION ALL
  SELECT 'uruguai', 1, 'Miramar Misiones', 'miramar-misiones', 63 UNION ALL
  SELECT 'uruguai', 2, 'Montevideo City Torque', 'montevideo-city-torque', 67 UNION ALL
  SELECT 'uruguai', 2, 'Plaza Colonia', 'plaza-colonia', 65 UNION ALL
  SELECT 'uruguai', 2, 'Juventud de Las Piedras', 'juventud-de-las-piedras', 64 UNION ALL
  SELECT 'uruguai', 2, 'Rentistas', 'rentistas', 64 UNION ALL
  SELECT 'uruguai', 2, 'Cerrito', 'cerrito', 63 UNION ALL
  SELECT 'uruguai', 2, 'Albion', 'albion', 62 UNION ALL
  SELECT 'uruguai', 2, 'Sud América', 'sud-america', 62 UNION ALL
  SELECT 'uruguai', 2, 'Uruguay Montevideo', 'uruguay-montevideo', 61 UNION ALL
  SELECT 'uruguai', 2, 'Colón FC', 'colon-fc', 60 UNION ALL
  SELECT 'uruguai', 2, 'Atenas de San Carlos', 'atenas-de-san-carlos', 60 UNION ALL
  SELECT 'uruguai', 2, 'La Luz', 'la-luz', 60 UNION ALL
  SELECT 'uruguai', 2, 'Tacuarembó', 'tacuarembo', 59 UNION ALL
  SELECT 'uruguai', 2, 'Oriental', 'oriental', 58 UNION ALL
  SELECT 'uruguai', 2, 'Cooper', 'cooper', 57
) x ON x.league_slug = l.slug AND x.level = d.level;

CREATE OR REPLACE VIEW liga_holanda_primeira AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'holanda' AND d.level = 1;
CREATE OR REPLACE VIEW liga_holanda_segunda AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'holanda' AND d.level = 2;
CREATE OR REPLACE VIEW liga_argentina_primeira AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'argentina' AND d.level = 1;
CREATE OR REPLACE VIEW liga_argentina_segunda AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'argentina' AND d.level = 2;
CREATE OR REPLACE VIEW liga_uruguai_primeira AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'uruguai' AND d.level = 1;
CREATE OR REPLACE VIEW liga_uruguai_segunda AS
  SELECT c.* FROM clubs c JOIN divisions d ON d.id = c.division_id JOIN leagues l ON l.id = d.league_id
  WHERE l.slug = 'uruguai' AND d.level = 2;

COMMIT;