-- Add button_style column to sites table
ALTER TABLE sites ADD COLUMN button_style VARCHAR(20) NOT NULL DEFAULT 'fab' AFTER maps;

-- Update existing sites to use the default 'fab' style
UPDATE sites SET button_style = 'fab' WHERE button_style IS NULL OR button_style = '';

-- Add comment to explain the column
ALTER TABLE sites MODIFY COLUMN button_style VARCHAR(20) NOT NULL DEFAULT 'fab' COMMENT 'Button style: fab, bar, or sticky-right';
