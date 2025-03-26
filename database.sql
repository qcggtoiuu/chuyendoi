-- Create database if it doesn't exist
-- CREATE DATABASE IF NOT EXISTS chuyendoii_dbdb;
-- USE chuyendoii_dbdb;

-- Sites table - stores information about registered websites
CREATE TABLE IF NOT EXISTS sites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  domain VARCHAR(255) NOT NULL,
  api_key VARCHAR(64) NOT NULL,
  show_buttons BOOLEAN NOT NULL DEFAULT TRUE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (domain),
  UNIQUE KEY (api_key)
);

-- Visits table - stores information about each visit
CREATE TABLE IF NOT EXISTS visits (
  id INT AUTO_INCREMENT PRIMARY KEY,
  site_id INT NOT NULL,
  ip_address VARCHAR(45) NOT NULL,
  browser VARCHAR(255),
  browser_version VARCHAR(50),
  isp VARCHAR(255),
  connection_type VARCHAR(50),
  os VARCHAR(100),
  os_version VARCHAR(50),
  screen_width INT,
  screen_height INT,
  city VARCHAR(100),
  country VARCHAR(100),
  current_page VARCHAR(2048),
  referrer VARCHAR(2048) NULL,
  utm_source VARCHAR(255) NULL,
  utm_medium VARCHAR(255) NULL,
  utm_campaign VARCHAR(255) NULL,
  utm_term VARCHAR(255) NULL,
  utm_content VARCHAR(255) NULL,
  visit_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  time_spent INT DEFAULT 0,
  bot_score FLOAT DEFAULT 0,
  is_bot BOOLEAN DEFAULT FALSE,
  FOREIGN KEY (site_id) REFERENCES sites(id)
);

-- Clicks table - stores information about clicks on specific links
CREATE TABLE IF NOT EXISTS clicks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  visit_id INT NOT NULL,
  click_type ENUM('phone', 'zalo', 'messenger', 'maps') NOT NULL,
  click_url VARCHAR(2048) NOT NULL,
  click_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (visit_id) REFERENCES visits(id)
);

-- Users table - stores admin users
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  role ENUM('admin', 'manager', 'user') NOT NULL DEFAULT 'user',
  is_approved BOOLEAN NOT NULL DEFAULT FALSE,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  last_login DATETIME,
  UNIQUE KEY (username),
  UNIQUE KEY (email)
);

-- Conversion baselines table - stores normal conversion rates
CREATE TABLE IF NOT EXISTS conversion_baselines (
  id INT AUTO_INCREMENT PRIMARY KEY,
  site_id INT NOT NULL,
  segment_type ENUM('overall', 'device', 'browser', 'os', 'location', 'isp') NOT NULL,
  segment_value VARCHAR(255),
  conversion_type ENUM('phone', 'zalo', 'messenger', 'maps', 'all') NOT NULL,
  avg_rate FLOAT,
  std_deviation FLOAT,
  min_rate FLOAT,
  max_rate FLOAT,
  sample_size INT,
  last_updated DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (site_id) REFERENCES sites(id)
);

-- Anomalies table - stores detected anomalies
CREATE TABLE IF NOT EXISTS anomalies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  site_id INT NOT NULL,
  detected_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  end_at DATETIME NULL,
  anomaly_type ENUM('high_cr', 'pattern', 'timing', 'cluster') NOT NULL,
  severity ENUM('low', 'medium', 'high') NOT NULL,
  segment_type ENUM('overall', 'device', 'browser', 'os', 'location', 'isp') NOT NULL,
  segment_value VARCHAR(255),
  expected_value FLOAT,
  actual_value FLOAT,
  deviation_percent FLOAT,
  affected_visits INT,
  description TEXT,
  is_resolved BOOLEAN DEFAULT FALSE,
  resolution_notes TEXT,
  FOREIGN KEY (site_id) REFERENCES sites(id)
);

-- Fraud patterns table - stores confirmed fraud patterns
CREATE TABLE IF NOT EXISTS fraud_patterns (
  id INT AUTO_INCREMENT PRIMARY KEY,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  created_by INT NOT NULL,
  ip_pattern VARCHAR(100) NULL,
  isp_pattern VARCHAR(255) NULL,
  location_pattern VARCHAR(255) NULL,
  device_pattern VARCHAR(255) NULL,
  behavior_pattern VARCHAR(255) NULL,
  similarity_threshold FLOAT DEFAULT 0.85,
  description TEXT,
  is_active BOOLEAN DEFAULT TRUE,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Fraud vectors table - stores feature vectors of fraud
CREATE TABLE IF NOT EXISTS fraud_vectors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fraud_pattern_id INT NOT NULL,
  feature_name VARCHAR(100) NOT NULL,
  feature_value FLOAT NOT NULL,
  FOREIGN KEY (fraud_pattern_id) REFERENCES fraud_patterns(id)
);

-- Button hide logs table - stores history of button hiding
CREATE TABLE IF NOT EXISTS button_hide_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  site_id INT NOT NULL,
  visit_id INT NOT NULL,
  hidden_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  reason VARCHAR(255),
  matching_pattern_id INT NULL,
  similarity_score FLOAT NULL,
  FOREIGN KEY (site_id) REFERENCES sites(id),
  FOREIGN KEY (visit_id) REFERENCES visits(id),
  FOREIGN KEY (matching_pattern_id) REFERENCES fraud_patterns(id)
);

-- No default admin user - first registered user will be admin
