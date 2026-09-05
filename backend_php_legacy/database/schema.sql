CREATE DATABASE IF NOT EXISTS dataconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dataconnect;

CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  phone VARCHAR(20) NOT NULL UNIQUE,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('customer','marketer','staff','admin','dispenser') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE wallets (
  user_id BIGINT UNSIGNED PRIMARY KEY,
  balance DECIMAL(14,2) NOT NULL DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_wallet_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE wallet_ledger (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  type ENUM('credit','debit','refund','share_return','withdrawal') NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  reference VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ledger_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX(user_id, created_at)
);

CREATE TABLE data_orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  network VARCHAR(30) NOT NULL,
  plan_name VARCHAR(80) NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  recipient_phone VARCHAR(20) NOT NULL,
  provider_reference VARCHAR(120) NULL,
  status ENUM('pending','processing','successful','failed','refunded') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX(user_id, created_at)
);

CREATE TABLE airtime_requests (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  network VARCHAR(30) NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  recipient_phone VARCHAR(20) NOT NULL,
  status ENUM('pending','approved','rejected','credited') NOT NULL DEFAULT 'pending',
  dispenser_id BIGINT UNSIGNED NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  approved_at TIMESTAMP NULL,
  CONSTRAINT fk_airtime_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE share_packages (
  id INT AUTO_INCREMENT PRIMARY KEY,
  investment_amount DECIMAL(14,2) NOT NULL,
  daily_amount DECIMAL(14,2) NOT NULL,
  duration_days INT NOT NULL
);

INSERT INTO share_packages (investment_amount,daily_amount,duration_days) VALUES
(10000,250,90),(20000,500,90),(30000,750,90),(40000,1000,90),(50000,1500,90),(60000,1800,92);

CREATE TABLE share_holdings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  package_id INT NOT NULL,
  status ENUM('pending','active','completed','cancelled') NOT NULL DEFAULT 'pending',
  purchased_at TIMESTAMP NULL,
  CONSTRAINT fk_holding_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_holding_package FOREIGN KEY (package_id) REFERENCES share_packages(id)
);

CREATE TABLE withdrawal_requests (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  amount DECIMAL(14,2) NOT NULL,
  status ENUM('pending','approved','rejected','paid') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  reference VARCHAR(100) NULL UNIQUE,
  reviewed_by BIGINT UNSIGNED NULL,
  reviewed_at TIMESTAMP NULL,
  reason VARCHAR(255) NULL,
  CONSTRAINT fk_withdraw_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_withdraw_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE marketers (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL UNIQUE,
  marketer_id VARCHAR(40) NOT NULL UNIQUE,
  name VARCHAR(120) NOT NULL,
  location VARCHAR(120) NOT NULL,
  monthly_pay DECIMAL(14,2) NOT NULL DEFAULT 0,
  picture_path VARCHAR(255) NULL,
  guarantor_name VARCHAR(120) NOT NULL,
  guarantor_phone VARCHAR(20) NOT NULL,
  account_requirement DECIMAL(14,2) NOT NULL DEFAULT 3250,
  minimum_gb DECIMAL(8,2) NOT NULL DEFAULT 12,
  approval_status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  CONSTRAINT fk_marketer_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(150) NOT NULL,
  body VARCHAR(500) NOT NULL,
  is_read BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE staff_messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  staff_id BIGINT UNSIGNED NOT NULL,
  customer_id BIGINT UNSIGNED NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  actor_user_id BIGINT UNSIGNED NOT NULL,
  action VARCHAR(100) NOT NULL,
  entity_type VARCHAR(50) NOT NULL,
  entity_id BIGINT UNSIGNED NULL,
  details JSON NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(actor_user_id,created_at)
);

CREATE TABLE IF NOT EXISTS share_return_ledger (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  holding_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  return_date DATE NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  reference VARCHAR(100) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_holding_day (holding_id, return_date)
);
