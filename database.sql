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
INSERT INTO posts (user_id, category_id, title, content, image) VALUES
(1,1,'Intro to PHP','PHP is a popular general-purpose scripting language that is especially suited to web development. Fast, flexible and pragmatic, PHP powers everything from your personal blog to the world\'s most popular websites. In this introduction, we will cover basic syntax, variables, database integration, and how you can start building dynamic pages.','php_intro.png'),
(2,2,'Healthy Living','Maintaining a healthy lifestyle doesn\'t have to be complicated. By focusing on simple daily habits like eating fresh organic vegetables, staying hydrated, getting 7-8 hours of restful sleep, and incorporating light physical exercise into your routine, you can significantly boost your energy, focus, and long-term physical well-being.','healthy_living.png'),
(1,3,'Learn SQL','Structured Query Language (SQL) is the standard language for relational database management systems. Whether you are building web apps, analyzing massive datasets, or managing user authentication, understanding how to write optimized SELECT, INSERT, UPDATE, and JOIN queries is a fundamental skill for any full-stack software engineer.','learn_sql.png'),
(3,4,'Travel Guide','Embark on an unforgettable journey to explore majestic snow-capped mountain peaks and peaceful evergreen pine forests. Traveling helps reduce stress, expands your worldview, and creates lifelong memories. In this guide, we share the top mountain trails, essential packing lists, and tips for sustainable, eco-friendly travel.','travel_guide.png'),
(2,1,'Web Design Trends 2026','Modern web design is evolving rapidly. From glassmorphic overlays and dark mode aesthetics to immersive grid systems and micro-interactions, developers are focusing heavily on creating fast, responsive, and user-centric interfaces. In this article, we explore the top design systems, typography choices, and CSS frameworks shaping the future of the web.','web_design.png');

-- Sample comments
INSERT INTO comments (post_id, user_id, comment) VALUES
(1,2,'Great explanation on PHP!'),
(1,3,'Very helpful.'),
(2,1,'Nice lifestyle tips.'),
(3,3,'SQL is important.'),
(4,2,'Love this guide.');
