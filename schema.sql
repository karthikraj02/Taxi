CREATE DATABASE taxi_service;

USE taxi_service;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  role ENUM('user', 'admin') DEFAULT 'user'
);

CREATE TABLE bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  pickup VARCHAR(100),
  dropoff VARCHAR(100),
  datetime DATETIME,
  status VARCHAR(50) DEFAULT 'Pending',
  FOREIGN KEY(user_id) REFERENCES users(id)
);

CREATE TABLE drivers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  phone VARCHAR(15),
  status ENUM('Available', 'On Ride', 'Offline') DEFAULT 'Available',
  location VARCHAR(100)
);
