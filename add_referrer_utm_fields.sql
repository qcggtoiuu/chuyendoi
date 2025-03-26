-- Add referrer and UTM parameters fields to visits table
ALTER TABLE visits ADD COLUMN referrer VARCHAR(2048) NULL AFTER current_page;
ALTER TABLE visits ADD COLUMN utm_source VARCHAR(255) NULL AFTER referrer;
ALTER TABLE visits ADD COLUMN utm_medium VARCHAR(255) NULL AFTER utm_source;
ALTER TABLE visits ADD COLUMN utm_campaign VARCHAR(255) NULL AFTER utm_medium;
ALTER TABLE visits ADD COLUMN utm_term VARCHAR(255) NULL AFTER utm_campaign;
ALTER TABLE visits ADD COLUMN utm_content VARCHAR(255) NULL AFTER utm_term;
