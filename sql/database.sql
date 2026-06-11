CREATE DATABASE IF NOT EXISTS company_db;
USE company_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    FirstName VARCHAR(50) NOT NULL,
    LastName VARCHAR(50) NOT NULL,
    Username VARCHAR(50) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Position VARCHAR(100),
    Date1 TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    DateLastLogin DATETIME,
    Description TEXT
);

-- درج کاربر تستی
INSERT INTO users (FirstName, LastName, Username, Password, Position, Description) 
VALUES ('مدیر', 'سیستم', 'admin', PASSWORD('123456'), 'مدیر ارشد', 'توضیحات نمونه');