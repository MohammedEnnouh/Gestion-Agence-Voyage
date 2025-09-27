SELECT * FROM users WHERE email = 'admin@vogie.com';

INSERT INTO users (full_name, email, password, role, is_active) 
VALUES (
    'Administrator', 
    'admin@vogie.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin', 
    1
) 
ON DUPLICATE KEY UPDATE 
    password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    is_active = 1;

INSERT INTO users (full_name, email, password, role, is_active) 
VALUES (
    'Local Admin', 
    'admin@local.app', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin', 
    1
) 
ON DUPLICATE KEY UPDATE 
    password = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    is_active = 1;

SELECT id, full_name, email, role, is_active FROM users WHERE role = 'admin';