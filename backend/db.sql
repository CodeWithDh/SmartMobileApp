-- 🔥 Create Database
CREATE DATABASE IF NOT EXISTS smart_mobile_app;
USE smart_mobile_app;

-- 🔥 Table 1: Admin Credentials (Password Encrypted)
CREATE TABLE admin_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,  -- ✅ Stores encrypted hash
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 🔥 Table 2: Purchased Mobiles
CREATE TABLE purchased_mobiles (
    IMEI VARCHAR(50) PRIMARY KEY,

    -- 🔥 Seller Details
    seller_name VARCHAR(100) NOT NULL,
    seller_mobile VARCHAR(20) NOT NULL,
    seller_photo JSON,
    seller_verification_video VARCHAR(255),

    -- 🔥 Mobile Info
    mobile_name VARCHAR(100) NOT NULL,
    fault_description VARCHAR(255),
    price DECIMAL(10,2),

    -- 🔥 Dates
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sell_date TIMESTAMP NULL DEFAULT NULL,
    return_date TIMESTAMP NULL DEFAULT NULL,

    -- 🔥 Buyer Details
    buyer_name VARCHAR(100),
    buyer_mobile VARCHAR(20),
    buyer_photo JSON,
    buyer_verification VARCHAR(255),

    -- 🔥 Return Details
    return_photo JSON,
    return_verification VARCHAR(255),
    return_description VARCHAR(255),

    -- 🔥 Drive Folder & Status
    drive_folder_id VARCHAR(255),
    status ENUM('purchased', 'sold', 'returned') DEFAULT 'purchased'
);
