SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE activity_log;
TRUNCATE TABLE appointments;
TRUNCATE TABLE appointment_history;
TRUNCATE TABLE appointment_process_logs;
TRUNCATE TABLE appointment_ratings;
TRUNCATE TABLE auth_sessions;
TRUNCATE TABLE notifications;
TRUNCATE TABLE notification_preferences;
TRUNCATE TABLE patient_profiles;
TRUNCATE TABLE provider_availability;
TRUNCATE TABLE provider_profiles;
TRUNCATE TABLE provider_services;
TRUNCATE TABLE recurring_schedules;
TRUNCATE TABLE user_tokens;
TRUNCATE TABLE waitlist;
DELETE FROM users WHERE role != 'admin';
SET FOREIGN_KEY_CHECKS = 1;

SET @current_date = CURDATE();
SET @current_time = CURTIME();
SET @current_datetime = NOW();

-- Insert providers
INSERT INTO users (email, password_hash, first_name, last_name, phone, role, is_active, is_verified, email_verified_at, created_at)
VALUES
('provider@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Jennifer', 'Smith', '(555) 123-4567', 'provider', 1, 1, @current_datetime, @current_datetime),
('provider2@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Michael', 'Johnson', '(555) 234-5678', 'provider', 1, 1, @current_datetime, @current_datetime),
('provider3@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'David', 'Williams', '(555) 345-6789', 'provider', 1, 1, @current_datetime, @current_datetime),
('provider4@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Sarah', 'Brown', '(555) 456-7890', 'provider', 1, 1, @current_datetime, @current_datetime),
('provider5@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'James', 'Davis', '(555) 567-8901', 'provider', 1, 1, @current_datetime, @current_datetime),
('provider6@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Emily', 'Miller', '(555) 678-9012', 'provider', 1, 1, @current_datetime, @current_datetime);

SET @provider1_id = LAST_INSERT_ID();
SET @provider2_id = @provider1_id + 1;
SET @provider3_id = @provider1_id + 2;
SET @provider4_id = @provider1_id + 3;
SET @provider5_id = @provider1_id + 4;
SET @provider6_id = @provider1_id + 5;

-- Insert patients
INSERT INTO users (email, password_hash, first_name, last_name, phone, role, is_active, is_verified, email_verified_at, created_at)
VALUES
('patient@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Robert', 'Anderson', '(555) 789-0123', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient2@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Lisa', 'Taylor', '(555) 890-1234', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient3@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Thomas', 'Moore', '(555) 901-2345', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient4@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Jessica', 'Jackson', '(555) 012-3456', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient5@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Daniel', 'White', '(555) 123-4567', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient6@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Michelle', 'Harris', '(555) 234-5678', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient7@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Kevin', 'Martin', '(555) 345-6789', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient8@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Patricia', 'Thompson', '(555) 456-7890', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient9@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Christopher', 'Garcia', '(555) 567-8901', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient10@example.com', '$2y$10$yw4M0It4rzy2elD4Fa7r3O1/yzYwXK3v8bBTLV5ZJXwIDdg2JXR9C', 'Amanda', 'Martinez', '(555) 678-9012', 'patient', 1, 1, @current_datetime, @current_datetime);

SET @patient1_id = LAST_INSERT_ID();
SET @patient2_id = @patient1_id + 1;
SET @patient3_id = @patient1_id + 2;
SET @patient4_id = @patient1_id + 3;
SET @patient5_id = @patient1_id + 4;
SET @patient6_id = @patient1_id + 5;
SET @patient7_id = @patient1_id + 6;
SET @patient8_id = @patient1_id + 7;
SET @patient9_id = @patient1_id + 8;
SET @patient10_id = @patient1_id + 9;

-- Insert provider profiles
INSERT INTO provider_profiles (provider_id, specialization, title, bio, accepting_new_patients, max_patients_per_day, created_at, updated_at)
VALUES
(@provider1_id, 'Family Medicine', 'MD', 'Dr. Smith is a board-certified family physician with over 15 years of experience.', 1, 20, @current_datetime, @current_datetime),
(@provider2_id, 'Pediatrics', 'MD', 'Dr. Johnson specializes in pediatric care and has been practicing for 10 years.', 1, 15, @current_datetime, @current_datetime),
(@provider3_id, 'Internal Medicine', 'MD', 'Dr. Williams focuses on preventive care and management of chronic conditions.', 1, 18, @current_datetime, @current_datetime),
(@provider4_id, 'Mental Health', 'PhD', 'Dr. Brown provides therapy and counseling services for various mental health conditions.', 1, 12, @current_datetime, @current_datetime),
(@provider5_id, 'Nutrition', 'RD', 'Dr. Davis is a registered dietitian who helps patients develop healthy eating habits.', 1, 15, @current_datetime, @current_datetime),
(@provider6_id, 'General Practice', 'MD', 'Dr. Miller provides comprehensive primary care for patients of all ages.', 1, 20, @current_datetime, @current_datetime);

-- Insert patient profiles
INSERT INTO patient_profiles (user_id, phone, date_of_birth, address, emergency_contact, emergency_contact_phone, medical_conditions, medical_history, insurance_info, created_at, updated_at)
VALUES
(@patient1_id, '(555) 789-0123', '1975-05-15', '123 Main St, Anytown, USA', 'Mary Anderson', '(555) 234-5678', 'Hypertension', 'Appendectomy 2010', 'BlueCross #12345678', @current_datetime, @current_datetime),
(@patient2_id, '(555) 890-1234', '1982-09-22', '456 Oak Ave, Somecity, USA', 'John Taylor', '(555) 345-6789', 'Asthma', 'Broken arm 2015', 'Aetna #23456789', @current_datetime, @current_datetime),
(@patient3_id, '(555) 901-2345', '1968-03-10', '789 Pine Rd, Othertown, USA', 'Susan Moore', '(555) 456-7890', 'Diabetes Type 2', 'Cholecystectomy 2018', 'UnitedHealth #34567890', @current_datetime, @current_datetime),
(@patient4_id, '(555) 012-3456', '1990-11-05', '101 Elm St, Newcity, USA', 'Michael Jackson', '(555) 567-8901', 'None', 'None', 'Cigna #45678901', @current_datetime, @current_datetime),
(@patient5_id, '(555) 123-4567', '1985-07-17', '202 Maple Dr, Oldtown, USA', 'Jennifer White', '(555) 678-9012', 'Allergies - Pollen', 'Tonsillectomy 2005', 'Humana #56789012', @current_datetime, @current_datetime),
(@patient6_id, '(555) 234-5678', '1977-12-25', '303 Cedar Ln, Bigcity, USA', 'William Harris', '(555) 789-0123', 'Migraine', 'None', 'Kaiser #67890123', @current_datetime, @current_datetime),
(@patient7_id, '(555) 345-6789', '1995-01-30', '404 Birch Pl, Smallville, USA', 'Elizabeth Martin', '(555) 890-1234', 'None', 'Appendectomy 2020', 'Medicare #78901234', @current_datetime, @current_datetime),
(@patient8_id, '(555) 456-7890', '1972-08-08', '505 Willow Way, Metropolis, USA', 'Robert Thompson', '(555) 901-2345', 'Hypertension, High Cholesterol', 'Heart bypass 2019', 'Medicaid #89012345', @current_datetime, @current_datetime),
(@patient9_id, '(555) 567-8901', '1988-04-20', '606 Spruce Ct, Gotham, USA', 'Maria Garcia', '(555) 012-3456', 'Anxiety', 'None', 'Anthem #90123456', @current_datetime, @current_datetime),
(@patient10_id, '(555) 678-9012', '1980-06-15', '707 Fir Blvd, Starling, USA', 'David Martinez', '(555) 123-4567', 'Depression', 'None', 'Tricare #01234567', @current_datetime, @current_datetime);

-- Associate providers with services
INSERT INTO provider_services (provider_id, service_id, created_at)
SELECT @provider1_id, service_id, @current_datetime FROM services WHERE service_id IN (35, 36, 37, 41);

INSERT INTO provider_services (provider_id, service_id, created_at)
SELECT @provider2_id, service_id, @current_datetime FROM services WHERE service_id IN (35, 36, 37);

INSERT INTO provider_services (provider_id, service_id, created_at)
SELECT @provider3_id, service_id, @current_datetime FROM services WHERE service_id IN (35, 36, 37, 41, 42);

INSERT INTO provider_services (provider_id, service_id, created_at)
SELECT @provider4_id, service_id, @current_datetime FROM services WHERE service_id IN (38, 39);

INSERT INTO provider_services (provider_id, service_id, created_at)
SELECT @provider5_id, service_id, @current_datetime FROM services WHERE service_id IN (40, 42);

INSERT INTO provider_services (provider_id, service_id, created_at)
SELECT @provider6_id, service_id, @current_datetime FROM services WHERE service_id IN (35, 36, 37, 41, 42, 43);

-- Add specific_date column if it doesn't exist
SET @check_column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'recurring_schedules'
    AND COLUMN_NAME = 'specific_date'
);

SET @alter_statement = IF(@check_column_exists = 0,
    'ALTER TABLE recurring_schedules ADD COLUMN specific_date DATE NULL',
    'SELECT 1');

PREPARE stmt FROM @alter_statement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Insert recurring schedules with improved data
-- Day of week: 0=Sunday, 1=Monday, 2=Tuesday, 3=Wednesday, 4=Thursday, 5=Friday, 6=Saturday
INSERT INTO recurring_schedules (provider_id, day_of_week, start_time, end_time, is_active, schedule_type, effective_from, effective_until, created_at, updated_at, specific_date)
VALUES
-- Provider 1 (Jennifer Smith) - Works Mon, Wed, Fri
(@provider1_id, 1, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider1_id, 3, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider1_id, 5, '08:00:00', '13:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),

-- Provider 2 (Michael Johnson) - Works Mon, Tue, Thu
(@provider2_id, 1, '09:00:00', '18:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider2_id, 2, '09:00:00', '18:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider2_id, 4, '09:00:00', '18:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),

-- Provider 3 (David Williams) - Works Tue, Thu, Fri
(@provider3_id, 2, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider3_id, 4, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider3_id, 5, '08:00:00', '15:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),

-- Provider 4 (Sarah Brown) - Works Mon, Wed, Thu (later hours for mental health)
(@provider4_id, 1, '10:00:00', '19:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider4_id, 3, '10:00:00', '19:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider4_id, 4, '10:00:00', '19:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),

-- Provider 5 (James Davis) - Works Mon, Wed, Fri
(@provider5_id, 1, '08:00:00', '16:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider5_id, 3, '08:00:00', '16:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider5_id, 5, '08:00:00', '16:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),

-- Provider 6 (Emily Miller) - Works Mon-Fri
(@provider6_id, 1, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider6_id, 2, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider6_id, 3, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider6_id, 4, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider6_id, 5, '08:00:00', '13:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL);

-- Add some specific one-time availabilities with specific_date
INSERT INTO recurring_schedules (provider_id, day_of_week, start_time, end_time, is_active, schedule_type, effective_from, effective_until, created_at, updated_at, specific_date)
VALUES
-- Provider 1 working a special Saturday next month
(@provider1_id, 6, '09:00:00', '14:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, DATE_ADD(CURDATE(), INTERVAL 30 DAY)),
-- Provider 3 working a special Sunday next week
(@provider3_id, 0, '10:00:00', '15:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, DATE_ADD(CURDATE(), INTERVAL 7 DAY)),
-- Provider 6 doing after-hours availability on a Thursday
(@provider6_id, 4, '17:00:00', '20:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, DATE_ADD(CURDATE(), INTERVAL 14 DAY));

-- Add some unavailability blocks (schedule_type = 'unavailability')
INSERT INTO recurring_schedules (provider_id, day_of_week, start_time, end_time, is_active, schedule_type, effective_from, effective_until, created_at, updated_at, specific_date)
VALUES
-- Provider 1 has lunch break
(@provider1_id, 1, '12:00:00', '13:00:00', 1, 'unavailability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider1_id, 3, '12:00:00', '13:00:00', 1, 'unavailability', NULL, NULL, @current_datetime, @current_datetime, NULL),
-- Provider 2 has a meeting every Monday
(@provider2_id, 1, '12:00:00', '14:00:00', 1, 'unavailability', NULL, NULL, @current_datetime, @current_datetime, NULL),
-- Provider 4 has regular training on Wednesdays
(@provider4_id, 3, '12:00:00', '14:00:00', 1, 'unavailability', NULL, NULL, @current_datetime, @current_datetime, NULL);

-- Create procedure to generate availability slots
DELIMITER //
CREATE PROCEDURE GenerateAvailabilitySlots()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE provider INT;
    DECLARE provider_service INT;
    DECLARE service_duration INT;
    DECLARE avail_date DATE;
    DECLARE start_t TIME;
    DECLARE end_t TIME;
    DECLARE slot_start TIME;
    DECLARE slot_end TIME;
    DECLARE day_of_week INT;
    DECLARE max_slots INT;
    
    -- Get all active providers
    DECLARE provider_cursor CURSOR FOR 
        SELECT DISTINCT provider_id FROM recurring_schedules WHERE is_active = 1;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    OPEN provider_cursor;
    
    provider_loop: LOOP
        FETCH provider_cursor INTO provider;
        IF done THEN
            LEAVE provider_loop;
        END IF;
        
        SET avail_date = CURDATE();
        
        -- Generate for next 30 days
        WHILE avail_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) DO
            SET day_of_week = WEEKDAY(avail_date); -- MySQL uses 0=Monday, 1=Tuesday, etc.
            SET day_of_week = IF(day_of_week = 6, 0, day_of_week + 1); -- Convert to 0=Sunday, 1=Monday, etc.
            
            -- Look for any schedule for this provider on this day or specific date
            IF EXISTS (
                SELECT 1 FROM recurring_schedules 
                WHERE provider_id = provider 
                AND is_active = 1
                AND schedule_type = 'availability'
                AND (
                    (day_of_week = day_of_week AND specific_date IS NULL) 
                    OR specific_date = avail_date
                )
            ) THEN
                -- Process each separate availability block for this day
                BEGIN
                    DECLARE block_done INT DEFAULT FALSE;
                    DECLARE schedule_id INT;
                    
                    DECLARE block_cursor CURSOR FOR 
                        SELECT rs.schedule_id, rs.start_time, rs.end_time 
                        FROM recurring_schedules rs
                        WHERE rs.provider_id = provider 
                        AND rs.is_active = 1
                        AND rs.schedule_type = 'availability'
                        AND (
                            (rs.day_of_week = day_of_week AND rs.specific_date IS NULL) 
                            OR rs.specific_date = avail_date
                        );
                    
                    DECLARE CONTINUE HANDLER FOR NOT FOUND SET block_done = TRUE;
                    
                    OPEN block_cursor;
                    
                    block_loop: LOOP
                        FETCH block_cursor INTO schedule_id, start_t, end_t;
                        IF block_done THEN
                            LEAVE block_loop;
                        END IF;
                        
                        -- Get the provider's services and create slots for each
                        BEGIN
                            DECLARE service_done INT DEFAULT FALSE;
                            DECLARE service_cursor CURSOR FOR 
                                SELECT ps.service_id, s.duration 
                                FROM provider_services ps
                                JOIN services s ON ps.service_id = s.service_id
                                WHERE ps.provider_id = provider;
                            
                            DECLARE CONTINUE HANDLER FOR NOT FOUND SET service_done = TRUE;
                            
                            OPEN service_cursor;
                            
                            service_loop: LOOP
                                FETCH service_cursor INTO provider_service, service_duration;
                                IF service_done THEN
                                    LEAVE service_loop;
                                END IF;
                                
                                -- Check for unavailability blocks
                                SET slot_start = start_t;
                                
                                -- Generate slots every 30 minutes
                                WHILE TIME_TO_SEC(TIMEDIFF(end_t, slot_start)) >= (service_duration * 60) DO
                                    SET slot_end = ADDTIME(slot_start, SEC_TO_TIME(service_duration * 60));
                                    
                                    -- Check if this slot overlaps with any unavailability
                                    IF NOT EXISTS (
                                        SELECT 1 FROM recurring_schedules 
                                        WHERE provider_id = provider 
                                        AND is_active = 1
                                        AND schedule_type = 'unavailability'
                                        AND (
                                            (day_of_week = day_of_week AND specific_date IS NULL) 
                                            OR specific_date = avail_date
                                        )
                                        AND (
                                            (start_time <= slot_start AND end_time > slot_start) OR
                                            (start_time < slot_end AND end_time >= slot_end) OR
                                            (start_time >= slot_start AND end_time <= slot_end)
                                        )
                                    ) THEN
                                        -- Insert the slot if it doesn't overlap with unavailability
                                        INSERT INTO provider_availability (
                                            provider_id,
                                            availability_date,
                                            start_time,
                                            end_time,
                                            is_available,
                                            schedule_type,
                                            created_at,
                                            is_recurring,
                                            weekdays,
                                            max_appointments,
                                            service_id
                                        )
                                        VALUES (
                                            provider,
                                            avail_date,
                                            slot_start,
                                            slot_end,
                                            1,
                                            'availability',
                                            @current_datetime,
                                            0,
                                            NULL,
                                            1,
                                            provider_service
                                        );
                                    END IF;
                                    
                                    SET slot_start = ADDTIME(slot_start, '00:30:00');
                                END WHILE;
                            END LOOP;
                            
                            CLOSE service_cursor;
                        END;
                    END LOOP;
                    
                    CLOSE block_cursor;
                END;
            END IF;
            
            SET avail_date = DATE_ADD(avail_date, INTERVAL 1 DAY);
        END WHILE;
    END LOOP;
    
    CLOSE provider_cursor;
END //
DELIMITER ;

-- Execute the procedure to generate availability slots
CALL GenerateAvailabilitySlots();
DROP PROCEDURE GenerateAvailabilitySlots;

-- Insert appointments
INSERT INTO appointments (
    patient_id,
    provider_id,
    service_id,
    appointment_date,
    start_time,
    end_time,
    status,
    type,
    notes,
    reason,
    created_at,
    updated_at
)
VALUES
(
    @patient1_id,
    @provider1_id,
    35,
    DATE_SUB(CURDATE(), INTERVAL 7 DAY),
    '09:00:00',
    '09:45:00',
    'completed',
    'in_person',
    'Patient reported ongoing hypertension issues. Medication adjusted.',
    'Annual checkup',
    DATE_SUB(@current_datetime, INTERVAL 14 DAY),
    @current_datetime
),
(
    @patient1_id,
    @provider2_id,
    36,
    DATE_SUB(CURDATE(), INTERVAL 20 DAY),
    '10:00:00',
    '10:30:00',
    'completed',
    'telemedicine',
    'Follow-up on medication adjustment. Patient reporting improved blood pressure.',
    'Follow-up',
    DATE_SUB(@current_datetime, INTERVAL 25 DAY),
    @current_datetime
),
(
    @patient2_id,
    @provider1_id,
    37,
    DATE_SUB(CURDATE(), INTERVAL 5 DAY),
    '11:00:00',
    '11:30:00',
    'completed',
    'in_person',
    'Patient reports occasional wheezing. Inhaler prescription renewed.',
    'Asthma management',
    DATE_SUB(@current_datetime, INTERVAL 10 DAY),
    @current_datetime
),
(
    @patient3_id,
    @provider3_id,
    41,
    DATE_SUB(CURDATE(), INTERVAL 3 DAY),
    '14:00:00',
    '14:45:00',
    'completed',
    'in_person',
    'Blood sugar levels stable. Continuing with current medication regimen.',
    'Diabetes checkup',
    DATE_SUB(@current_datetime, INTERVAL 7 DAY),
    @current_datetime
),
(
    @patient4_id,
    @provider4_id,
    38,
    DATE_SUB(CURDATE(), INTERVAL 2 DAY),
    '15:00:00',
    '16:00:00',
    'completed',
    'telemedicine',
    'Initial consultation for reported anxiety issues.',
    'Mental health consultation',
    DATE_SUB(@current_datetime, INTERVAL 5 DAY),
    @current_datetime
),
(
    @patient5_id,
    @provider5_id,
    40,
    DATE_SUB(CURDATE(), INTERVAL 1 DAY),
    '09:30:00',
    '10:15:00',
    'completed',
    'in_person',
    'Discussed meal planning and allergen avoidance strategies.',
    'Nutrition consultation',
    DATE_SUB(@current_datetime, INTERVAL 3 DAY),
    @current_datetime
),
(
    @patient6_id,
    @provider6_id,
    35,
    CURDATE(),
    '10:00:00',
    '10:45:00',
    'confirmed',  -- Changed from 'scheduled' to 'confirmed'
    'in_person',
    NULL,
    'Annual physical',
    DATE_SUB(@current_datetime, INTERVAL 2 DAY),
    @current_datetime
),
(
    @patient7_id,
    @provider2_id,
    36,
    DATE_ADD(CURDATE(), INTERVAL 1 DAY),
    '14:00:00',
    '14:30:00',
    'confirmed',  -- Changed from 'scheduled' to 'confirmed'
    'telemedicine',
    NULL,
    'Cold symptoms',
    DATE_SUB(@current_datetime, INTERVAL 1 DAY),
    @current_datetime
),
(
    @patient8_id,
    @provider3_id,
    41,
    DATE_ADD(CURDATE(), INTERVAL 2 DAY),
    '11:30:00',
    '12:15:00',
    'confirmed',  -- Changed from 'scheduled' to 'confirmed'
    'in_person',
    NULL,
    'Heart condition follow-up',
    @current_datetime,
    @current_datetime
),
(
    @patient9_id,
    @provider4_id,
    39,
    DATE_ADD(CURDATE(), INTERVAL 3 DAY),
    '16:00:00',
    '17:00:00',
    'confirmed',  -- Changed from 'scheduled' to 'confirmed'
    'in_person',
    NULL,
    'Therapy session',
    @current_datetime,
    @current_datetime
),
(
    @patient10_id,
    @provider5_id,
    40,
    DATE_ADD(CURDATE(), INTERVAL 5 DAY),
    '10:00:00',
    '10:45:00',
    'confirmed',  -- Changed from 'scheduled' to 'confirmed'
    'telemedicine',
    NULL,
    'Diet consultation',
    @current_datetime,
    @current_datetime
);


-- Add some appointment ratings for completed appointments
INSERT INTO appointment_ratings (
    appointment_id,
    patient_id,
    provider_id,
    rating,
    comment,
    created_at
)
VALUES
(1, @patient1_id, @provider1_id, 5, 'Dr. Smith was very thorough and addressed all my concerns.', DATE_SUB(@current_datetime, INTERVAL 7 DAY)),
(2, @patient1_id, @provider2_id, 4, 'Good follow-up appointment. The telemedicine format worked well.', DATE_SUB(@current_datetime, INTERVAL 20 DAY)),
(3, @patient2_id, @provider1_id, 5, 'Very helpful with my asthma management.', DATE_SUB(@current_datetime, INTERVAL 5 DAY)),
(4, @patient3_id, @provider3_id, 4, 'Dr. Williams was knowledgeable about diabetes care.', DATE_SUB(@current_datetime, INTERVAL 3 DAY)),
(5, @patient4_id, @provider4_id, 5, 'Dr. Brown was very understanding and helpful with my anxiety issues.', DATE_SUB(@current_datetime, INTERVAL 2 DAY));

-- Corrected INSERT for notification_preferences matching the table structure
INSERT INTO notification_preferences (
    user_id,
    email_notifications,
    sms_notifications,
    appointment_reminders,
    system_updates,
    reminder_time,
    created_at,
    updated_at
)
SELECT 
    user_id,
    1, -- everyone gets email notifications
    CASE WHEN user_id % 3 = 0 THEN 1 ELSE 0 END, -- every third user gets SMS notifications
    1, -- everyone gets appointment reminders
    CASE WHEN user_id % 2 = 0 THEN 1 ELSE 0 END, -- every other user gets system updates
    '24', -- 24 hours before
    @current_datetime,
    @current_datetime
FROM 
    users 
WHERE 
    role IN ('patient', 'provider');


-- Corrected INSERT for notifications with unique system notifications
INSERT INTO notifications (
    user_id,
    appointment_id,
    subject,
    message,
    type,
    status,
    created_at,
    is_system,
    is_read,
    audience
)
VALUES
-- For patients
(@patient1_id, 
 6, 
 'Appointment Reminder', 
 'Your appointment with Dr. Smith is scheduled for tomorrow at 10:00 AM.', 
 'appointment', 
 'sent', 
 DATE_SUB(@current_datetime, INTERVAL 1 DAY), 
 0, 
 0, 
 'patient'),

(@patient7_id, 
 7, 
 'Appointment Reminder', 
 'Your appointment with Dr. Johnson is scheduled for tomorrow at 2:00 PM.', 
 'appointment', 
 'sent', 
 @current_datetime, 
 0, 
 0, 
 'patient'),

(@patient8_id, 
 8, 
 'Appointment Reminder', 
 'Your appointment with Dr. Williams is scheduled for the day after tomorrow at 11:30 AM.', 
 'appointment', 
 'sent', 
 @current_datetime, 
 0, 
 0, 
 'patient'),

-- For providers
(@provider1_id, 
 6, 
 'New Appointment', 
 'New appointment scheduled with Robert Anderson for tomorrow at 10:00 AM.', 
 'appointment', 
 'sent', 
 DATE_SUB(@current_datetime, INTERVAL 1 DAY), 
 0, 
 0, 
 'provider'),

(@provider2_id, 
 7, 
 'New Appointment', 
 'New appointment scheduled with Kevin Martin for tomorrow at 2:00 PM.', 
 'appointment', 
 'sent', 
 @current_datetime, 
 0, 
 0, 
 'provider'),

(@provider3_id, 
 8, 
 'New Appointment', 
 'New appointment scheduled with Patricia Thompson for the day after tomorrow at 11:30 AM.', 
 'appointment', 
 'sent', 
 @current_datetime, 
 0, 
 0, 
 'provider'),

-- System notifications - make each one unique by adding provider ID or name to the message
(@provider1_id, 
 NULL, 
 'System Update', 
 CONCAT('Your weekly schedule has been updated. Provider ID: ', @provider1_id), 
 'system', 
 'sent', 
 DATE_SUB(@current_datetime, INTERVAL 2 DAY), 
 1, 
 0, 
 'provider'),

(@provider2_id, 
 NULL, 
 'System Update', 
 CONCAT('Your weekly schedule has been updated. Provider ID: ', @provider2_id), 
 'system', 
 'sent', 
 DATE_SUB(@current_datetime, INTERVAL 2 DAY), 
 1, 
 0, 
 'provider'),

(@provider3_id, 
 NULL, 
 'System Update', 
 CONCAT('Your weekly schedule has been updated. Provider ID: ', @provider3_id), 
 'system', 
 'sent', 
 DATE_SUB(@current_datetime, INTERVAL 2 DAY), 
 1, 
 0, 
 'provider'),

(@provider4_id, 
 NULL, 
 'System Update', 
 CONCAT('Your weekly schedule has been updated. Provider ID: ', @provider4_id), 
 'system', 
 'sent', 
 DATE_SUB(@current_datetime, INTERVAL 2 DAY), 
 1, 
 0, 
 'provider'),

(@provider5_id, 
 NULL, 
 'System Update', 
 CONCAT('Your weekly schedule has been updated. Provider ID: ', @provider5_id), 
 'system', 
 'sent', 
 DATE_SUB(@current_datetime, INTERVAL 2 DAY), 
 1, 
 0, 
 'provider'),

(@provider6_id, 
 NULL, 
 'System Update', 
 CONCAT('Your weekly schedule has been updated. Provider ID: ', @provider6_id), 
 'system', 
 'sent', 
 DATE_SUB(@current_datetime, INTERVAL 2 DAY), 
 1, 
 0, 
 'provider');


-- Corrected INSERT for activity_log matching the table structure
INSERT INTO activity_log (
    user_id,
    description,
    category,
    created_at,
    ip_address,
    details,
    related_id,
    related_type
)
VALUES
-- Login activities
(@provider1_id, 
 'User logged in successfully', 
 'login', 
 DATE_SUB(@current_datetime, INTERVAL 1 DAY), 
 '192.168.1.100', 
 JSON_OBJECT('user_agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'), 
 NULL, 
 NULL),

(@provider2_id, 
 'User logged in successfully', 
 'login', 
 DATE_SUB(@current_datetime, INTERVAL 12 HOUR), 
 '192.168.1.101', 
 JSON_OBJECT('user_agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)'), 
 NULL, 
 NULL),

(@patient1_id, 
 'User logged in successfully', 
 'login', 
 DATE_SUB(@current_datetime, INTERVAL 6 HOUR), 
 '192.168.1.102', 
 JSON_OBJECT('user_agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1)'), 
 NULL, 
 NULL),

(@patient2_id, 
 'User logged in successfully', 
 'login', 
 DATE_SUB(@current_datetime, INTERVAL 3 HOUR), 
 '192.168.1.103', 
 JSON_OBJECT('user_agent', 'Mozilla/5.0 (Android 10; Mobile)'), 
 NULL, 
 NULL),

-- Appointment activities
(@provider1_id, 
 'Appointment #6 marked as scheduled', 
 'appointment', 
 DATE_SUB(@current_datetime, INTERVAL 2 DAY), 
 '192.168.1.100', 
 JSON_OBJECT('user_agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'), 
 6, 
 'appointment'),

(@patient1_id, 
 'Appointment #6 booked', 
 'appointment', 
 DATE_SUB(@current_datetime, INTERVAL 2 DAY), 
 '192.168.1.102', 
 JSON_OBJECT('user_agent', 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1)'), 
 6, 
 'appointment'),

(@provider2_id, 
 'Appointment #7 marked as scheduled', 
 'appointment', 
 DATE_SUB(@current_datetime, INTERVAL 1 DAY), 
 '192.168.1.101', 
 JSON_OBJECT('user_agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)'), 
 7, 
 'appointment'),

(@patient7_id, 
 'Appointment #7 booked', 
 'appointment', 
 DATE_SUB(@current_datetime, INTERVAL 1 DAY), 
 '192.168.1.104', 
 JSON_OBJECT('user_agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'), 
 7, 
 'appointment'),

-- Profile update activities
(@provider1_id, 
 'Provider profile updated', 
 'profile', 
 DATE_SUB(@current_datetime, INTERVAL 5 DAY), 
 '192.168.1.100', 
 JSON_OBJECT('user_agent', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'), 
 @provider1_id, 
 'user_profile'),

(@provider3_id, 
 'Provider profile updated', 
 'profile', 
 DATE_SUB(@current_datetime, INTERVAL 4 DAY), 
 '192.168.1.105', 
 JSON_OBJECT('user_agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)'), 
 @provider3_id, 
 'user_profile'),

(@patient3_id, 
 'Patient profile updated', 
 'profile', 
 DATE_SUB(@current_datetime, INTERVAL 3 DAY), 
 '192.168.1.106', 
 JSON_OBJECT('user_agent', 'Mozilla/5.0 (iPad; CPU OS 14_7_1)'), 
 @patient3_id, 
 'user_profile'),

(@patient5_id, 
 'Patient profile updated', 
 'profile', 
 DATE_SUB(@current_datetime, INTERVAL 2 DAY), 
 '192.168.1.107', 
 JSON_OBJECT('user_agent', 'Mozilla/5.0 (Android 11; Mobile)'), 
 @patient5_id, 
 'user_profile');

-- Corrected INSERT for appointment_process_logs matching the table structure
-- First entry: Appointment creation
INSERT INTO appointment_process_logs (
    timestamp,
    user_id,
    user_role,
    ip_address,
    user_agent,
    action,
    entity,
    entity_id,
    additional_data
)
SELECT
    a.created_at,
    a.patient_id,
    'patient',
    '127.0.0.1', -- Default IP since original doesn't have this
    'System', -- Default user agent since original doesn't have this
    'booking',
    'appointment',
    a.appointment_id,
    JSON_OBJECT(
        'message', 'Appointment created successfully',
        'patient_id', a.patient_id,
        'provider_id', a.provider_id,
        'service_id', a.service_id
    )
FROM
    appointments a;

-- Second entry: Appointment completion
INSERT INTO appointment_process_logs (
    timestamp,
    user_id,
    user_role,
    ip_address,
    user_agent,
    action,
    entity,
    entity_id,
    additional_data
)
SELECT
    a.updated_at,
    a.provider_id,
    'provider',
    '127.0.0.1', -- Default IP since original doesn't have this
    'System', -- Default user agent since original doesn't have this
    'completion',
    'appointment',
    a.appointment_id,
    JSON_OBJECT(
        'message', 'Appointment marked as completed',
        'status_changed_by', a.provider_id,
        'completion_time', a.updated_at
    )
FROM
    appointments a
WHERE
    a.status = 'completed';


-- Output completion message
SELECT 'System has been initialized with test data. Providers, patients, appointments, and related data have been created.' AS Result;
