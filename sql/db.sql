-- Create database
CREATE DATABASE IF NOT EXISTS lost_and_found;
USE lost_and_found;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Categories table for item types
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Items table for lost and found items
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    item_type ENUM('lost', 'found') NOT NULL,
    location_found VARCHAR(255),
    location_lost VARCHAR(255),
    date_lost DATE,
    date_found DATE,
    status ENUM('active', 'resolved', 'closed') DEFAULT 'active',
    contact_method ENUM('email', 'phone', 'both') DEFAULT 'both',
    image_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Item images table for multiple images per item
CREATE TABLE IF NOT EXISTS item_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE
);

-- User sessions table for login management
CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(191) UNIQUE NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default categories
INSERT INTO categories (name, description) VALUES
('Electronics', 'Phones, laptops, tablets, cameras, and other electronic devices'),
('Clothing', 'Shirts, pants, jackets, shoes, and other clothing items'),
('Accessories', 'Jewelry, watches, bags, wallets, and other accessories'),
('Documents', 'IDs, passports, certificates, and other important documents'),
('Keys', 'House keys, car keys, and other types of keys'),
('Books', 'Textbooks, novels, notebooks, and other reading materials'),
('Sports Equipment', 'Balls, rackets, gym equipment, and other sports items'),
('Toys', 'Children toys, games, and other play items'),
('Other', 'Items that dont fit into other categories');
