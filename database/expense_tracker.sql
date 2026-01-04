-- Create database
CREATE DATABASE IF NOT EXISTS expense_tracker;
USE expense_tracker;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(10),
    color VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Expenses table
CREATE TABLE IF NOT EXISTS expenses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    amount DECIMAL(10, 2) NOT NULL,
    category_id INT NOT NULL,
    description TEXT,
    expense_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Insert default categories
INSERT IGNORE INTO categories (id, name, icon, color) VALUES
(1, 'Food & Dining', '🍔', '#ef4444'),
(2, 'Travel & Transport', '✈️', '#3b82f6'),
(3, 'Bills & Utilities', '📄', '#f59e0b'),
(4, 'Shopping', '🛍️', '#ec4899'),
(5, 'Entertainment', '🎬', '#8b5cf6'),
(6, 'Health & Medical', '💊', '#10b981'),
(7, 'Education', '📚', '#06b6d4'),
(8, 'Other', '📦', '#6b7280');

-- Insert sample expenses
INSERT INTO expenses (amount, category_id, description, expense_date) VALUES
(500.00, 1, 'Lunch at restaurant', CURDATE()),
(250.00, 2, 'Uber ride to office', CURDATE()),
(1500.00, 3, 'Electricity bill', DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
(1200.00, 4, 'New shirt and jeans', DATE_SUB(CURDATE(), INTERVAL 2 DAY)),
(800.00, 5, 'Movie tickets', DATE_SUB(CURDATE(), INTERVAL 3 DAY)),
(300.00, 6, 'Medicine', DATE_SUB(CURDATE(), INTERVAL 4 DAY)),
(2000.00, 7, 'Online course', DATE_SUB(CURDATE(), INTERVAL 5 DAY));