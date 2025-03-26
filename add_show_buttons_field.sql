-- Add show_buttons field to sites table
ALTER TABLE sites ADD COLUMN show_buttons BOOLEAN NOT NULL DEFAULT TRUE;
