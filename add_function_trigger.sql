-- ===============================================
-- SWIFT BOOK - FUNCTION AND TRIGGER ADDITIONS
-- ===============================================

USE swift_book;

-- Function: Calculate booking revenue for a specific route
DELIMITER //
CREATE FUNCTION CalculateRouteRevenue(route_id INT) 
RETURNS DECIMAL(10,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE total_revenue DECIMAL(10,2) DEFAULT 0.00;
    
    SELECT COALESCE(SUM(p.amount), 0.00) INTO total_revenue
    FROM Bookings b
    JOIN Payments p ON b.booking_id = p.booking_id
    WHERE b.route_id = route_id 
    AND b.status = 'Confirmed' 
    AND p.status = 'Completed';
    
    RETURN total_revenue;
END //
DELIMITER ;

-- Trigger: Auto-update route status when seats are full
DELIMITER //
CREATE TRIGGER UpdateRouteStatus
AFTER UPDATE ON Routes
FOR EACH ROW
BEGIN
    IF NEW.available_seats = 0 AND OLD.available_seats > 0 THEN
        UPDATE Routes 
        SET status = 'Full' 
        WHERE route_id = NEW.route_id;
    ELSEIF NEW.available_seats > 0 AND OLD.available_seats = 0 THEN
        UPDATE Routes 
        SET status = 'Active' 
        WHERE route_id = NEW.route_id;
    END IF;
END //
DELIMITER ;
