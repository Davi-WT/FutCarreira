USE futcarreira;

ALTER TABLE players
  ADD COLUMN IF NOT EXISTS pace INT NOT NULL DEFAULT 60,
  ADD COLUMN IF NOT EXISTS dribbling INT NOT NULL DEFAULT 60,
  ADD COLUMN IF NOT EXISTS finishing INT NOT NULL DEFAULT 60,
  ADD COLUMN IF NOT EXISTS defending INT NOT NULL DEFAULT 60,
  ADD COLUMN IF NOT EXISTS passing INT NOT NULL DEFAULT 60,
  ADD COLUMN IF NOT EXISTS physical INT NOT NULL DEFAULT 60;

UPDATE players
SET
  pace = IF(pace = 60, GREATEST(35, LEAST(99, overall + 2)), pace),
  dribbling = IF(dribbling = 60, GREATEST(35, LEAST(99, overall + 1)), dribbling),
  finishing = IF(finishing = 60, GREATEST(35, LEAST(99, overall)), finishing),
  defending = IF(defending = 60, GREATEST(35, LEAST(99, overall - 2)), defending),
  passing = IF(passing = 60, GREATEST(35, LEAST(99, overall + 1)), passing),
  physical = IF(physical = 60, GREATEST(35, LEAST(99, overall + 3)), physical);
