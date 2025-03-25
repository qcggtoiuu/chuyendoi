-- SQL query to update the admin password
-- This will set the password to "P8j2mK9xL5qR3sT7" using bcrypt hashing

UPDATE users 
SET password_hash = '$2y$10$XK7mfbKZQAJBjAhbYm0kPeQkYfvN/JCpH3YYmtjwczGbNTEyHzKYe' 
WHERE username = 'quantri';

-- Alternatively, if you want to set a different password, you can use:
-- UPDATE users 
-- SET password_hash = '$2y$10$YourNewBcryptHashHere' 
-- WHERE username = 'quantri';
