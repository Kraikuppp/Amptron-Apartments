-- PostgreSQL Database Schema for Billing Rental System
-- แปลงจาก MySQL เป็น PostgreSQL

-- สร้างฐานข้อมูล (ถ้ายังไม่มี - บน Render จะสร้างให้อัตโนมัติ)
-- CREATE DATABASE billing_rental_system;

-- เชื่อมต่อกับฐานข้อมูล
-- \c billing_rental_system;

-- ตารางผู้ใช้
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role VARCHAR(20) DEFAULT 'user' CHECK (role IN ('user', 'business', 'admin')),
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'banned')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ตารางข้อมูลผู้ประกอบการ
CREATE TABLE IF NOT EXISTS business_profiles (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    business_name VARCHAR(200) NOT NULL,
    business_type VARCHAR(20) NOT NULL CHECK (business_type IN ('room_owner', 'product_seller', 'both')),
    address TEXT,
    tax_id VARCHAR(50),
    description TEXT,
    logo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ตารางห้องเช่า
CREATE TABLE IF NOT EXISTS rooms (
    id SERIAL PRIMARY KEY,
    business_id INTEGER NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    deposit DECIMAL(10,2) DEFAULT 0,
    room_type VARCHAR(50),
    area DECIMAL(8,2),
    bedrooms INTEGER DEFAULT 0,
    bathrooms INTEGER DEFAULT 1,
    floor INTEGER,
    address TEXT NOT NULL,
    province VARCHAR(100),
    district VARCHAR(100),
    subdistrict VARCHAR(100),
    postal_code VARCHAR(10),
    latitude DECIMAL(10,8),
    longitude DECIMAL(11,8),
    facilities TEXT,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected', 'rented', 'available')),
    views INTEGER DEFAULT 0,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES business_profiles(id) ON DELETE CASCADE
);

-- ตารางรูปภาพห้อง
CREATE TABLE IF NOT EXISTS room_images (
    id SERIAL PRIMARY KEY,
    room_id INTEGER NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE
);

-- ตารางหมวดหมู่สินค้า
CREATE TABLE IF NOT EXISTS product_categories (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    description TEXT,
    parent_id INTEGER DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL
);

-- ตารางสินค้า
CREATE TABLE IF NOT EXISTS products (
    id SERIAL PRIMARY KEY,
    business_id INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(200) UNIQUE NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    stock INTEGER DEFAULT 0,
    sku VARCHAR(100),
    image VARCHAR(255),
    images TEXT,
    specifications TEXT,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected', 'sold_out')),
    views INTEGER DEFAULT 0,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES business_profiles(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES product_categories(id) ON DELETE RESTRICT
);

-- ตารางโฆษณา
CREATE TABLE IF NOT EXISTS advertisements (
    id SERIAL PRIMARY KEY,
    business_id INTEGER NOT NULL,
    title VARCHAR(200) NOT NULL,
    image VARCHAR(255) NOT NULL,
    link_url VARCHAR(500),
    ad_type VARCHAR(20) DEFAULT 'banner' CHECK (ad_type IN ('banner', 'sidebar', 'popup')),
    position VARCHAR(50),
    start_date DATE,
    end_date DATE,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'active', 'expired', 'rejected')),
    clicks INTEGER DEFAULT 0,
    impressions INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES business_profiles(id) ON DELETE CASCADE
);

-- ตาราง Wishlist
CREATE TABLE IF NOT EXISTS wishlists (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    room_id INTEGER DEFAULT NULL,
    product_id INTEGER DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE (user_id, room_id, product_id)
);

-- ตารางรีวิว
CREATE TABLE IF NOT EXISTS reviews (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    room_id INTEGER DEFAULT NULL,
    product_id INTEGER DEFAULT NULL,
    rating INTEGER NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- ตารางการติดต่อ
CREATE TABLE IF NOT EXISTS contacts (
    id SERIAL PRIMARY KEY,
    user_id INTEGER DEFAULT NULL,
    business_id INTEGER NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    message TEXT NOT NULL,
    subject VARCHAR(200),
    type VARCHAR(20) DEFAULT 'general' CHECK (type IN ('room', 'product', 'general')),
    item_id INTEGER DEFAULT NULL,
    status VARCHAR(20) DEFAULT 'new' CHECK (status IN ('new', 'read', 'replied')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (business_id) REFERENCES business_profiles(id) ON DELETE CASCADE
);

-- ตารางกิจกรรมระบบ
CREATE TABLE IF NOT EXISTS activity_logs (
    id SERIAL PRIMARY KEY,
    user_id INTEGER DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(50),
    record_id INTEGER,
    description TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- สร้าง indexes สำหรับ performance
CREATE INDEX IF NOT EXISTS idx_users_username ON users(username);
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_rooms_business_id ON rooms(business_id);
CREATE INDEX IF NOT EXISTS idx_rooms_status ON rooms(status);
CREATE INDEX IF NOT EXISTS idx_products_business_id ON products(business_id);
CREATE INDEX IF NOT EXISTS idx_products_category_id ON products(category_id);

-- สร้าง function สำหรับ auto-update updated_at
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- สร้าง triggers สำหรับ auto-update updated_at
CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_rooms_updated_at BEFORE UPDATE ON rooms
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_products_updated_at BEFORE UPDATE ON products
    FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- สร้าง Admin เริ่มต้น
-- รหัสผ่าน: password
INSERT INTO users (username, email, password, full_name, role, status) VALUES
('admin', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', 'active')
ON CONFLICT (username) DO NOTHING;

-- เพิ่มหมวดหมู่สินค้าเริ่มต้น
INSERT INTO product_categories (name, slug, description) VALUES
('มิเตอร์ไฟฟ้า', 'electric-meters', 'มิเตอร์ไฟฟ้าทุกประเภท'),
('CT Meter', 'ct-meters', 'มิเตอร์วัดกระแส CT'),
('WebMeter Software', 'webmeter-software', 'โปรแกรมจัดการมิเตอร์ WebMeter'),
('อุปกรณ์ไฟฟ้า', 'electrical-equipment', 'อุปกรณ์ไฟฟ้าต่างๆ')
ON CONFLICT (slug) DO NOTHING;
