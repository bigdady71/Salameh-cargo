-- Create a new admin user with hashed password
-- Note: The password_hash is pre-computed using PHP's password_hash() function
-- Username: hsyn
-- Password: tryu123

INSERT INTO admins
    (username, password_hash, role, is_active, failed_attempts, created_at)
VALUES
    (
        'hsyn',
        '$2y$10$SIUkDvH6cFDHPqRlXfSs6eVgb8PqrgXIgXDHE/ShYDn/1HJbmE.Ym', -- Hashed version of 'tryu123'
        'superadmin',
        1, -- is_active = true
        0, -- failed_attempts = 0
        CURRENT_TIMESTAMP
);
