-- Create locations table
CREATE TABLE IF NOT EXISTS locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(100) NOT NULL,
    postal_code VARCHAR(20),
    status ENUM('Active', 'Inactive') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add foreign key to bookings table
ALTER TABLE bookings
ADD COLUMN location_id INT,
ADD FOREIGN KEY (location_id) REFERENCES locations(id);

-- Insert some sample locations
INSERT INTO locations (name, address, city, postal_code, status) VALUES
('QuickLease Main Branch', '123 Main Street', 'Manila', '1000', 'Active'),
('QuickLease North Branch', '456 North Avenue', 'Quezon City', '1100', 'Active'),
('QuickLease South Branch', '789 South Road', 'Makati', '1200', 'Active'),
('QuickLease East Branch', '321 East Boulevard', 'Pasig', '1600', 'Active'); 