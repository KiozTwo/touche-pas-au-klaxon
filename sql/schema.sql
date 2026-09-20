CREATE DATABASE IF NOT EXISTS klaxon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE klaxon;
CREATE TABLE IF NOT EXISTS agencies (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 last_name VARCHAR(100) NOT NULL,
 first_name VARCHAR(100) NOT NULL,
 phone VARCHAR(20) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('employee','admin') NOT NULL DEFAULT 'employee'
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS trips (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 departure_agency_id INT UNSIGNED NOT NULL,
 arrival_agency_id INT UNSIGNED NOT NULL,
 departure_at DATETIME NOT NULL,
 arrival_at DATETIME NOT NULL,
 total_seats TINYINT UNSIGNED NOT NULL,
 available_seats TINYINT UNSIGNED NOT NULL,
 author_id INT UNSIGNED NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_trip_departure FOREIGN KEY (departure_agency_id) REFERENCES agencies(id) ON DELETE RESTRICT,
 CONSTRAINT fk_trip_arrival FOREIGN KEY (arrival_agency_id) REFERENCES agencies(id) ON DELETE RESTRICT,
 CONSTRAINT fk_trip_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE RESTRICT,
 CONSTRAINT chk_trip_agencies CHECK (departure_agency_id <> arrival_agency_id),
 CONSTRAINT chk_trip_dates CHECK (arrival_at > departure_at),
 CONSTRAINT chk_trip_seats CHECK (total_seats BETWEEN 1 AND 50 AND available_seats <= total_seats),
 INDEX idx_trip_departure (departure_at, available_seats),
 INDEX idx_trip_author (author_id)
) ENGINE=InnoDB;
