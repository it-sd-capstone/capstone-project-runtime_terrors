DELIMITER //

CREATE EVENT update_missed_appointments
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_DATE + INTERVAL 1 DAY
COMMENT 'Updates yesterday appointments to no_show status at midnight'
DO
BEGIN
    -- Update appointments from yesterday that weren't completed or canceled
    UPDATE appointments 
    SET 
        status = 'no_show',
        updated_at = NOW()
    WHERE 
        -- Yesterday's appointments
        DATE(appointment_date) = DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY)
        -- Only those that are still scheduled or confirmed
        AND status IN ('scheduled', 'confirmed')
        -- Ensure we have time fields with values
        AND start_time IS NOT NULL
        AND end_time IS NOT NULL;
END //

DELIMITER ;