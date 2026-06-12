CREATE DATABASE pharmafefo;
USE pharmafefo;

CREATE TABLE users(
    id INT PRIMARY KEY AUTO_INCREMENT,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    password VARCHAR(100) NOT NULL,
    role ENUM('pharmacist','preparer','admin')  NOT NULL
    );
CREATE TABLE lossreports(
    id INT PRIMARY KEY AUTO_INCREMENT,
    report_date DATETIME NOT NULL,
    total_loss FLOAT NOT NULL,
    details TEXT NOT NULL
);

CREATE TABLE products(
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    serial_number VARCHAR(100) NOT NULL,
    description TEXT NOT NULL
);

CREATE TABLE stockbatches(
    id INT PRIMARY KEY AUTO_INCREMENT,
    lot_number VARCHAR(100) NOT NULL,
    quantity INT NOT NULL,
    purchase_price FLOAT NOT NULL,
    status ENUM('ok','warning','critical','expired','return_process') DEFAULT 'ok',
    expiration_date DATE NOT NULL,
    created_at DATETIME NOT NULL,
    id_product INT NOT NULL,
    FOREIGN KEY (id_product) REFERENCES products (id)
);
CREATE TABLE notifications(
    id INT PRIMARY KEY AUTO_INCREMENT,
    description VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    is_read BOOLEAN DEFAULT false,
    id_batch INT NOT NULL,
    FOREIGN KEY (id_batch) REFERENCES stockbatches (id)
);
CREATE TABLE stockmovements(
    id INT PRIMARY KEY AUTO_INCREMENT,
    type ENUM('in','out'),
    quantity INT NOT NULL,
    movement_date DATETIME NOT NULL,
    id_batch INT,
    id_user INT,
    FOREIGN KEY (id_batch) REFERENCES stockbatches (id) ON DELETE SET NULL,
    FOREIGN KEY (id_user) REFERENCES users (id) ON DELETE SET NULL
);