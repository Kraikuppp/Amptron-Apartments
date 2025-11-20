-- อัปเดตรหัสผ่าน Admin เป็น "admin"
-- รหัสผ่าน: admin
-- Hash ของ "admin": $2y$10$WxOa2.VP9Y5dHx8k9qJZl.5xXqZJvK8L3mN1pQ7rT9uV2wY6zA4bC

-- วิธี 1: ใช้คำสั่ง SQL นี้ (ถ้ามี password hash ของ "admin" อยู่แล้ว)
-- UPDATE users SET password = '$2y$10$WxOa2.VP9Y5dHx8k9qJZl.5xXqZJvK8L3mN1pQ7rT9uV2wY6zA4bC' WHERE username = 'admin';

-- วิธี 2: ใช้ PHP script ด้านล่างแทน
-- ไปที่ http://localhost/billing/test_password.php

-- วิธี 3: ใช้คำสั่งนี้ใน PHP
-- php -r "echo password_hash('admin', PASSWORD_DEFAULT);"

