<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "smart_mobile_app";


$conn = mysqli_connect('localhost', 'root', '', 'smart_mobile_app');
if (!$conn) {
    die('❌ Connection failed: ' . mysqli_connect_error());
}
?>

<!-- CREATE DATABASE smart_mobile_app;
USE smart_mobile_app;

CREATE TABLE admin_credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 1. Purchased Mobiles
CREATE TABLE purchased_mobiles (
    IMEI VARCHAR(50) PRIMARY KEY,
    seller_name VARCHAR(100) NOT NULL,
    seller_photo VARCHAR(255),
    verification_video VARCHAR(255),
    mobile_name VARCHAR(100) NOT NULL,
    fault_description VARCHAR(255),
    price DECIMAL(10,2),
    purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Sold Mobiles
CREATE TABLE sold_mobiles (
    IMEI VARCHAR(50) PRIMARY KEY,
    buyer_name VARCHAR(100) NOT NULL,
    customer_number VARCHAR(15),
    alternative_number VARCHAR(15),
    buyer_photo VARCHAR(255),
    verification_video VARCHAR(255),
    description VARCHAR(255),
    price DECIMAL(10,2),
    sale_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (IMEI) REFERENCES purchased_mobiles(IMEI)
);

-- 3. Returned Mobiles
CREATE TABLE returned_mobiles (
    IMEI VARCHAR(50) PRIMARY KEY,
    return_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    return_description VARCHAR(255),
    FOREIGN KEY (IMEI) REFERENCES sold_mobiles(IMEI)
); -->

