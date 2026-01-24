CREATE DATABASE simple_blog;
USE simple_blog;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    role VARCHAR(20),
    email VARCHAR(150),
    user_emails VARCHAR(150),
    password VARCHAR(255),
    profile_image VARCHAR(255),
    profile TEXT
);

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50)
);

-- Posts table
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    category_id INT,
    title VARCHAR(255),
    content TEXT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Comments table
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    user_id INT,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sample users
INSERT INTO users (name, role, email, user_emails, password, profile_image, profile) VALUES
('Admin', 'Admin', 'admin@example.com', 'admin@example.com', '1234', '', ''),
('Editor', 'Editor', 'editor@example.com', 'editor@example.com', '1234', '', ''),
('Author', 'Author', 'author@example.com', 'author@example.com', '1234', '', ''),
('User', 'User', 'user@example.com', 'user@example.com', '1234', '', '');

-- Sample categories
INSERT INTO categories (name) VALUES
('Technology'),('Lifestyle'),('Education'),('Travel');

-- Sample posts
INSERT INTO posts (user_id, category_id, title, content) VALUES
(1,1,'Intro to PHP','PHP is simple and easy to learn.'),
(2,2,'Healthy Living','Eat vegetables and exercise daily.'),
(1,3,'Learn SQL','SQL is for databases.'),
(3,4,'Travel Guide','Visit mountains for peace.');

-- Sample comments
INSERT INTO comments (post_id, user_id, comment) VALUES
(1,2,'Great explanation on PHP!'),
(1,3,'Very helpful.'),
(2,1,'Nice lifestyle tips.'),
(3,3,'SQL is important.'),
(4,2,'Love this guide.');
