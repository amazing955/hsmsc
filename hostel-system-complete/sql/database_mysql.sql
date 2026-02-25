-- Hostel Management System - MySQL Version

DROP DATABASE IF EXISTS hostel_management;
CREATE DATABASE hostel_management;
USE hostel_management;

-- Users
CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student', 'hostel_owner', 'hotel_owner', 'boda_rider') DEFAULT 'student',
    phone VARCHAR(20),
    hostel_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Hostels
CREATE TABLE IF NOT EXISTS hostels (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    location VARCHAR(255) NOT NULL,
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    description TEXT,
    contact VARCHAR(20),
    owner_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Rooms
CREATE TABLE IF NOT EXISTS rooms (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    hostel_id INT NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    room_type ENUM('Single','Double','Triple') NOT NULL,
    capacity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    availability TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE
);

-- Bookings
CREATE TABLE IF NOT EXISTS bookings (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    room_id INT NOT NULL,
    booking_date DATE NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    status ENUM('pending','confirmed','cancelled','completed') DEFAULT 'pending',
    total_amount DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- Transport
CREATE TABLE IF NOT EXISTS transport (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    rider_name VARCHAR(100),
    cost DECIMAL(10,2) NOT NULL,
    status ENUM('pending','assigned','completed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    rider_id INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Products
CREATE TABLE IF NOT EXISTS products (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    owner_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Riders
CREATE TABLE IF NOT EXISTS riders (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    license_plate VARCHAR(20) NOT NULL,
    bike_type VARCHAR(50) DEFAULT 'Motorcycle',
    phone VARCHAR(20) NOT NULL,
    location VARCHAR(255),
    is_available TINYINT(1) DEFAULT 1,
    rating DECIMAL(2,1) DEFAULT 5.0,
    total_rides INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Orders
CREATE TABLE IF NOT EXISTS orders (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','processing','completed','cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Order Items
CREATE TABLE IF NOT EXISTS order_items (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Feedback
CREATE TABLE IF NOT EXISTS feedback (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    type ENUM('feedback','complaint','suggestion') DEFAULT 'feedback',
    status ENUM('pending','reviewed','resolved') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Room Layout
CREATE TABLE IF NOT EXISTS room_layout (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    layout_name VARCHAR(100),
    layout_json TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample admin user
INSERT INTO users (name,email,password,role) VALUES
('Admin User','admin@hostel.com','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','admin');

-- Sample hostels
INSERT INTO hostels (name,location,latitude,longitude,description,contact) VALUES
('Sunrise Hostel','Kampala, Uganda',0.3476,32.5825,'Modern hostel with great amenities','+256700000001'),
('Moonlight Residence','Kampala, Uganda',0.3500,32.5850,'Affordable and comfortable accommodation','+256700000002'),
('University Hostel','Kampala, Uganda',0.3450,32.5800,'Close to universities and colleges','+256700000003'),
('Green Valley Hostel','Kampala, Uganda',0.3490,32.5870,'Peaceful environment for students','+256700000004');

-- Sample rooms
INSERT INTO rooms (hostel_id,room_number,room_type,capacity,price,availability) VALUES
(1,'R101','Single',1,150000,1),
(1,'R102','Double',2,250000,1),
(1,'R103','Triple',3,350000,1),
(2,'R201','Single',1,140000,1),
(2,'R202','Double',2,240000,1),
(3,'R301','Single',1,160000,1),
(3,'R302','Triple',3,360000,1),
(4,'R401','Double',2,260000,1);

-- Sample products
INSERT INTO products (name,description,price,stock,image) VALUES
('Instant Noodles','Quick and delicious instant noodles',2000,100,'noodles.jpg'),
('Bottled Water','500ml bottled water',1000,200,'water.jpg'),
('Notebook','A4 size notebook for students',3000,50,'notebook.jpg'),
('Pen Set','Pack of 5 blue pens',2500,75,'pens.jpg'),
('Energy Drink','250ml energy drink',3500,80,'energy.jpg'),
('Bread','Fresh bread loaf',4000,30,'bread.jpg');
