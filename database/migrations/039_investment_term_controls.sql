ALTER TABLE investment_opportunities
 ADD COLUMN asset_useful_life_days INT UNSIGNED NULL AFTER target_term_months,
 ADD COLUMN maximum_term_days INT UNSIGNED GENERATED ALWAYS AS (asset_useful_life_days) STORED AFTER asset_useful_life_days,
 ADD COLUMN minimum_term_days INT UNSIGNED NULL AFTER maximum_term_days,
 ADD COLUMN target_term_days INT UNSIGNED NULL AFTER minimum_term_days,
 ADD CONSTRAINT investment_minimum_term_check CHECK(minimum_term_days IS NULL OR minimum_term_days>=90),
 ADD CONSTRAINT investment_target_term_check CHECK(target_term_days IS NULL OR minimum_term_days IS NULL OR asset_useful_life_days IS NULL OR (target_term_days>=minimum_term_days AND target_term_days<=asset_useful_life_days));
