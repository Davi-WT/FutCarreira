USE futcarreira;

ALTER TABLE players
  ADD COLUMN IF NOT EXISTS dominant_foot ENUM('Direita','Esquerda') NOT NULL DEFAULT 'Direita' AFTER nationality,
  ADD COLUMN IF NOT EXISTS height_cm INT NOT NULL DEFAULT 175 AFTER dominant_foot,
  ADD COLUMN IF NOT EXISTS weight_kg INT NOT NULL DEFAULT 70 AFTER height_cm,
  ADD COLUMN IF NOT EXISTS start_country CHAR(3) NOT NULL DEFAULT 'BRA' AFTER weight_kg;
