-- Hostel Management System Database Schema

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'student' CHECK (role IN ('admin', 'student', 'hostel_owner', 'hotel_owner', 'boda_rider')),
    phone VARCHAR(20),
    hostel_id INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS hostels (
    id SERIAL PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    location VARCHAR(255) NOT NULL,
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8),
    description TEXT,
    contact VARCHAR(20),
    owner_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rooms (
    id SERIAL PRIMARY KEY,
    hostel_id INTEGER NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    room_type VARCHAR(20) NOT NULL CHECK (room_type IN ('Single', 'Double', 'Triple')),
    capacity INTEGER NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    availability BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hostel_id) REFERENCES hostels(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS bookings (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    room_id INTEGER NOT NULL,
    booking_date DATE NOT NULL,
    check_in DATE NOT NULL,
    check_out DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'confirmed', 'cancelled', 'completed')),
    total_amount DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS transport (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    pickup_location VARCHAR(255) NOT NULL,
    destination VARCHAR(255) NOT NULL,
    rider_name VARCHAR(100),
    cost DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'assigned', 'completed', 'cancelled')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INTEGER DEFAULT 0,
    image VARCHAR(255),
    owner_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS riders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL UNIQUE,
    license_plate VARCHAR(20) NOT NULL,
    bike_type VARCHAR(50) DEFAULT 'Motorcycle',
    phone VARCHAR(20) NOT NULL,
    location VARCHAR(255),
    is_available BOOLEAN DEFAULT TRUE,
    rating DECIMAL(2, 1) DEFAULT 5.0,
    total_rides INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'processing', 'completed', 'cancelled')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_items (
    id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS feedback (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    type VARCHAR(20) DEFAULT 'feedback' CHECK (type IN ('feedback', 'complaint', 'suggestion')),
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'reviewed', 'resolved')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS room_layout (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    layout_name VARCHAR(100),
    layout_json TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@hostel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert sample hostels
INSERT INTO hostels (name, location, latitude, longitude, description, contact) VALUES
('Sunrise Hostel', 'Kampala, Uganda', 0.3476, 32.5825, 'Modern hostel with great amenities', '+256700000001'),
('Moonlight Residence', 'Kampala, Uganda', 0.3500, 32.5850, 'Affordable and comfortable accommodation', '+256700000002'),
('University Hostel', 'Kampala, Uganda', 0.3450, 32.5800, 'Close to universities and colleges', '+256700000003'),
('Green Valley Hostel', 'Kampala, Uganda', 0.3490, 32.5870, 'Peaceful environment for students', '+256700000004');

-- Insert sample rooms for each hostel
INSERT INTO rooms (hostel_id, room_number, room_type, capacity, price, availability) VALUES
(1, 'R101', 'Single', 1, 150000, TRUE),
(1, 'R102', 'Double', 2, 250000, TRUE),
(1, 'R103', 'Triple', 3, 350000, TRUE),
(2, 'R201', 'Single', 1, 140000, TRUE),
(2, 'R202', 'Double', 2, 240000, TRUE),
(3, 'R301', 'Single', 1, 160000, TRUE),
(3, 'R302', 'Triple', 3, 360000, TRUE),
(4, 'R401', 'Double', 2, 260000, TRUE);

-- Insert sample products for Grab Corner
INSERT INTO products (name, description, price, stock, image) VALUES
('Instant Noodles', 'Quick and delicious instant noodles', 2000, 100, 'noodles.jpg'),
('Bottled Water', '500ml bottled water', 1000, 200, 'water.jpg'),
('Notebook', 'A4 size notebook for students', 3000, 50, 'notebook.jpg'),
('Pen Set', 'Pack of 5 blue pens', 2500, 75, 'pens.jpg'),
('Energy Drink', '250ml energy drink', 3500, 80, 'energy.jpg'),
('Bread', 'Fresh bread loaf', 4000, 30, 'bread.jpg');
