-- SQL script to fix admin password
-- This will directly update the password hash for the 'quantri' user
-- The new password will be: P8j2mK9xL5qR3sT7

-- First, let's check the current user
SELECT * FROM users WHERE username = 'quantri';

-- Now update the password hash with a known working bcrypt hash for 'P8j2mK9xL5qR3sT7'
UPDATE users 
SET password_hash = '$2y$10$XK7mfbKZQAJBjAhbYm0kPeQkYfvN/JCpH3YYmtjwczGbNTEyHzKYe' 
WHERE username = 'quantri';

-- If the user doesn't exist, create it
INSERT INTO users (username, password_hash, email)
SELECT 'quantri', '$2y$10$XK7mfbKZQAJBjAhbYm0kPeQkYfvN/JCpH3YYmtjwczGbNTEyHzKYe', 'admin@chuyendoi.io.vn'
FROM dual
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = 'quantri');

-- Verify the update
SELECT * FROM users WHERE username = 'quantri';
