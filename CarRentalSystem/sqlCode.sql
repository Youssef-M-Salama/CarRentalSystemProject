CREATE DATABASE IF NOT EXISTS car_rental_system;
USE car_rental_system;

-- جدول المستخدمين
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client', 'premium') NOT NULL DEFAULT 'client',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول السيارات
CREATE TABLE IF NOT EXISTS cars (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    model VARCHAR(50) NOT NULL,
    type ENUM('Sedan', 'SUV', 'Crossover') NOT NULL,
    price_per_day DECIMAL(10, 2) NOT NULL,
    status ENUM('available', 'rented', 'maintenance') NOT NULL DEFAULT 'available',
    image VARCHAR(255),
    category ENUM('free', 'premium') NOT NULL DEFAULT 'free',
    description TEXT,
    features TEXT,
    average_rating DECIMAL(3,2) DEFAULT 0.00
);

-- جدول العروض
CREATE TABLE IF NOT EXISTS offers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    discount_percentage DECIMAL(5,2) NOT NULL,
    user_type ENUM('client', 'premium', 'all') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active',
    car_id INT,
    CONSTRAINT fk_offer_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE SET NULL
);

-- جدول طلبات التأجير
CREATE TABLE IF NOT EXISTS rental_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    offer_id INT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rental_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_rental_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    CONSTRAINT fk_rental_offer FOREIGN KEY (offer_id) REFERENCES offers(id) ON DELETE SET NULL
);

-- جدول طلبات تغيير الدور
CREATE TABLE IF NOT EXISTS role_change_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    requested_role ENUM('client', 'premium') NOT NULL,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_role_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


-- جدول التقييمات
CREATE TABLE IF NOT EXISTS rating (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    car_id INT NOT NULL,
    rental_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_rating_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_rating_car FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
    CONSTRAINT fk_rating_rental FOREIGN KEY (rental_id) REFERENCES rental_requests(id) ON DELETE CASCADE
);

INSERT INTO users (username, email, password, role)
VALUES (
    'admin_user',
    'admin@example.com',
    'admin123' , 
    'admin'
);

INSERT INTO users (username, email, password, role)
VALUES (
    'client_user',
    'client@example.com',
    'client123' , 
    'client'
);