-- ===============================================
-- SWIFT BOOK - SAMPLE DATA INSERTION
-- ===============================================

USE swift_book;

-- Insert Sample Users
INSERT INTO Users (username, email, contact, nid_no, address, password, is_admin) VALUES
('admin', 'admin@swiftbook.com', '01700000000', '1234567890123', 'Admin Office, Dhaka', 'admin123', TRUE),
('john_doe', 'john@email.com', '01711111111', '1234567890124', 'Dhanmondi, Dhaka', 'password123', FALSE),
('jane_smith', 'jane@email.com', '01722222222', '1234567890125', 'Gulshan, Dhaka', 'password123', FALSE),
('mike_wilson', 'mike@email.com', '01733333333', '1234567890126', 'Uttara, Dhaka', 'password123', FALSE),
('sarah_ahmed', 'sarah@email.com', '01744444444', '1234567890127', 'Chittagong', 'password123', FALSE);

-- Insert Sample Routes
INSERT INTO Routes (transport_type, origin, destination, departure_time, total_seats, available_seats, price) VALUES
('Bus', 'Dhaka', 'Chittagong', '08:00:00', 40, 35, 450.00),
('Bus', 'Dhaka', 'Sylhet', '09:30:00', 45, 40, 380.00),
('Bus', 'Chittagong', 'Dhaka', '14:00:00', 40, 38, 450.00),
('Train', 'Dhaka', 'Chittagong', '15:30:00', 120, 100, 320.00),
('Train', 'Dhaka', 'Sylhet', '22:00:00', 100, 85, 280.00),
('Airline', 'Dhaka', 'Chittagong', '10:15:00', 180, 150, 4500.00),
('Airline', 'Dhaka', 'Sylhet', '16:45:00', 150, 120, 3800.00),
('Bus', 'Dhaka', 'Rajshahi', '07:00:00', 50, 45, 420.00),
('Train', 'Dhaka', 'Rajshahi', '20:30:00', 110, 95, 300.00),
('Bus', 'Sylhet', 'Dhaka', '06:30:00', 45, 42, 380.00);

-- Insert Sample Bookings
INSERT INTO Bookings (user_id, route_id, seat_number) VALUES
(2, 1, 15),
(3, 1, 20),
(4, 2, 10),
(5, 4, 45),
(2, 6, 12),
(3, 3, 25),
(4, 5, 30),
(5, 7, 8),
(2, 8, 18),
(3, 9, 55);

-- Insert Sample Payments
INSERT INTO Payments (booking_id, amount, payment_method, status) VALUES
(1, 450.00, 'Card', 'Completed'),
(2, 450.00, 'Mobile Banking', 'Completed'),
(3, 380.00, 'Cash', 'Completed'),
(4, 320.00, 'Card', 'Completed'),
(5, 4500.00, 'Card', 'Completed'),
(6, 450.00, 'Mobile Banking', 'Completed'),
(7, 280.00, 'Cash', 'Completed'),
(8, 3800.00, 'Card', 'Completed'),
(9, 420.00, 'Mobile Banking', 'Completed'),
(10, 300.00, 'Cash', 'Completed');
