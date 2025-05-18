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

-- Define password hashes directly in the script
SET @admin_password = '$2y$10$6QBLl04XRuAj/y/bzRkAsuwgcm7pLZwFyBTuVejCB61NQs2TgSQ62'; -- Admin123@
SET @provider_password = '$2y$10$xaSYNovxOA8bQ16f7AEEPuTGdbzucps5N4sKmYlu0EbcV3ltpOUa.'; -- Provider123@
SET @patient_password = '$2y$10$orC5gFnaapRZiP6ZndYCFOmR7diEtkUNprBr4ZWaqZr0CWsK6dSJ2'; -- Patient123@
-- Insert provider users with correct password hash
INSERT INTO users (email, password_hash, first_name, last_name, phone, role, is_active, is_verified, email_verified_at, created_at)
VALUES
('provider@example.com', @provider_password, 'Jennifer', 'Smith', '(555) 123-4567', 'provider', 1, 1, @current_datetime, @current_datetime),
('provider2@example.com', @provider_password, 'Michael', 'Johnson', '(555) 234-5678', 'provider', 1, 1, @current_datetime, @current_datetime),
('provider3@example.com', @provider_password, 'David', 'Williams', '(555) 345-6789', 'provider', 1, 1, @current_datetime, @current_datetime),
('provider4@example.com', @provider_password, 'Sarah', 'Brown', '(555) 456-7890', 'provider', 1, 1, @current_datetime, @current_datetime),
('provider5@example.com', @provider_password, 'James', 'Davis', '(555) 567-8901', 'provider', 1, 1, @current_datetime, @current_datetime),
('provider6@example.com', @provider_password, 'Emily', 'Miller', '(555) 678-9012', 'provider', 1, 1, @current_datetime, @current_datetime);

SET @provider1_id = LAST_INSERT_ID();
SET @provider2_id = @provider1_id + 1;
SET @provider3_id = @provider1_id + 2;
SET @provider4_id = @provider1_id + 3;
SET @provider5_id = @provider1_id + 4;
SET @provider6_id = @provider1_id + 5;

-- Insert patient users with correct password hash
INSERT INTO users (email, password_hash, first_name, last_name, phone, role, is_active, is_verified, email_verified_at, created_at)
VALUES
('patient@example.com', @patient_password, 'Robert', 'Anderson', '(555) 789-0123', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient2@example.com', @patient_password, 'Lisa', 'Taylor', '(555) 890-1234', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient3@example.com', @patient_password, 'Thomas', 'Moore', '(555) 901-2345', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient4@example.com', @patient_password, 'Jessica', 'Jackson', '(555) 012-3456', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient5@example.com', @patient_password, 'Daniel', 'White', '(555) 123-4567', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient6@example.com', @patient_password, 'Michelle', 'Harris', '(555) 234-5678', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient7@example.com', @patient_password, 'Kevin', 'Martin', '(555) 345-6789', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient8@example.com', @patient_password, 'Patricia', 'Thompson', '(555) 456-7890', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient9@example.com', @patient_password, 'Christopher', 'Garcia', '(555) 567-8901', 'patient', 1, 1, @current_datetime, @current_datetime),
('patient10@example.com', @patient_password, 'Amanda', 'Martinez', '(555) 678-9012', 'patient', 1, 1, @current_datetime, @current_datetime);

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

INSERT INTO provider_profiles (provider_id, specialization, title, bio, accepting_new_patients, max_patients_per_day, created_at, updated_at)
VALUES
(@provider1_id, 'Family Medicine', 'MD', 'Dr. Smith is a board-certified family physician with over 15 years of experience.', 1, 20, @current_datetime, @current_datetime),
(@provider2_id, 'Pediatrics', 'MD', 'Dr. Johnson specializes in pediatric care and has been practicing for 10 years.', 1, 15, @current_datetime, @current_datetime),
(@provider3_id, 'Internal Medicine', 'MD', 'Dr. Williams focuses on preventive care and management of chronic conditions.', 1, 18, @current_datetime, @current_datetime),
(@provider4_id, 'Mental Health', 'PhD', 'Dr. Brown provides therapy and counseling services for various mental health conditions.', 1, 12, @current_datetime, @current_datetime),
(@provider5_id, 'Nutrition', 'RD', 'Dr. Davis is a registered dietitian who helps patients develop healthy eating habits.', 1, 15, @current_datetime, @current_datetime),
(@provider6_id, 'General Practice', 'MD', 'Dr. Miller provides comprehensive primary care for patients of all ages.', 1, 20, @current_datetime, @current_datetime);

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
    'SELECT 1'
);

PREPARE stmt FROM @alter_statement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Now insert the records with the specific_date field
INSERT INTO recurring_schedules (provider_id, day_of_week, start_time, end_time, is_active, schedule_type, effective_from, effective_until, created_at, updated_at, specific_date)
VALUES
(@provider1_id, 1, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider1_id, 3, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider1_id, 5, '08:00:00', '12:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider2_id, 1, '09:00:00', '18:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider2_id, 4, '09:00:00', '18:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider3_id, 2, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider3_id, 4, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider4_id, 2, '10:00:00', '19:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider4_id, 5, '10:00:00', '19:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider5_id, 1, '08:00:00', '16:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider5_id, 3, '08:00:00', '16:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider5_id, 5, '08:00:00', '16:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider6_id, 1, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider6_id, 2, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider6_id, 3, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider6_id, 4, '08:00:00', '17:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL),
(@provider6_id, 5, '08:00:00', '12:00:00', 1, 'availability', NULL, NULL, @current_datetime, @current_datetime, NULL);

DELIMITER //

CREATE PROCEDURE GenerateAvailabilitySlots()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE provider INT;
    DECLARE service INT;
    DECLARE avail_date DATE;
    DECLARE start_t TIME;
    DECLARE end_t TIME;
    DECLARE slot_start TIME;
    DECLARE slot_end TIME;
    DECLARE duration INT;
    
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
        
        WHILE avail_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) DO
            SET @day_of_week = WEEKDAY(avail_date) + 1;
            
            IF EXISTS (SELECT 1 FROM recurring_schedules WHERE provider_id = provider AND day_of_week = @day_of_week AND is_active = 1) THEN
                SELECT start_time, end_time INTO start_t, end_t 
                FROM recurring_schedules 
                WHERE provider_id = provider AND day_of_week = @day_of_week AND is_active = 1 
                LIMIT 1;
                
                SELECT service_id INTO service 
                FROM provider_services 
                WHERE provider_id = provider 
                ORDER BY RAND() 
                LIMIT 1;
                
                SELECT duration INTO duration 
                FROM services 
                WHERE service_id = service;
                
                SET slot_start = start_t;
                
                WHILE TIME_TO_SEC(TIMEDIFF(end_t, slot_start)) >= duration * 60 DO
                    SET slot_end = ADDTIME(slot_start, SEC_TO_TIME(duration * 60));
                    
                    INSERT INTO provider_availability (
                        provider_id, 
                        availability_date, 
                        start_time, 
                        end_time, 
                        is_available, 
                        schedule_type, 
                        created_at, 
                        is_recurring, 
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
                        NOW(), 
                        0, 
                        1, 
                        service
                    );
                    
                    SET slot_start = ADDTIME(slot_start, '00:30:00');
                END WHILE;
            END IF;
            
            SET avail_date = DATE_ADD(avail_date, INTERVAL 1 DAY);
        END WHILE;
    END LOOP;
    
    CLOSE provider_cursor;
END //

DELIMITER ;

CALL GenerateAvailabilitySlots();
DROP PROCEDURE GenerateAvailabilitySlots;

-- Directly populate provider_availability with practical slots for the next 30 days
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
SELECT 
    p.user_id,  -- Changed from p.provider_id to p.user_id
    DATE_ADD(CURDATE(), INTERVAL seq.n DAY), -- Date (current date + 0 to 29 days)
    -- Start time based on provider's recurring schedule for that day of week
    rs.start_time,
    -- End time from recurring schedule 
    rs.end_time,
    1, -- is_available
    'availability', -- schedule_type
    NOW(), -- created_at
    1, -- is_recurring (1 for master recurring entries)
    rs.day_of_week, -- Weekday as string
    3, -- max_appointments
    NULL -- service_id (NULL for master recurring entries)
FROM 
    users p
    -- This generates numbers 0 to 29 for our date range
    CROSS JOIN (
        SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9
        UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14
        UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19
        UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24
        UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
    ) seq
    -- Join with recurring_schedules to get appropriate time slots
    INNER JOIN recurring_schedules rs ON p.user_id = rs.provider_id
WHERE 
    p.role = 'provider'
    -- Only include if day of week in recurring_schedules matches the generated date's day of week
    AND rs.day_of_week = WEEKDAY(DATE_ADD(CURDATE(), INTERVAL seq.n DAY)) + 1
    AND rs.is_active = 1;

-- Now add specific service slots (non-recurring) for each provider
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
SELECT 
    rs.provider_id,
    DATE_ADD(CURDATE(), INTERVAL seq.n DAY),
    -- Generate 30-minute slots starting from recurring schedule start time
    ADDTIME(rs.start_time, SEC_TO_TIME(slot.slot_num * 1800)), -- 1800 seconds = 30 minutes
    ADDTIME(rs.start_time, SEC_TO_TIME((slot.slot_num + 1) * 1800)),
    1,
    'availability',
    NOW(),
    0, -- not recurring for specific service slots
    CAST(rs.day_of_week AS CHAR), -- Explicitly cast to string
    1, -- one appointment per slot
    ps.service_id
FROM 
    recurring_schedules rs
    -- Generate numbers 0 to 29 for dates
    CROSS JOIN (
        SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 
        UNION SELECT 5 UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9
        UNION SELECT 10 UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14
        UNION SELECT 15 UNION SELECT 16 UNION SELECT 17 UNION SELECT 18 UNION SELECT 19
        UNION SELECT 20 UNION SELECT 21 UNION SELECT 22 UNION SELECT 23 UNION SELECT 24
        UNION SELECT 25 UNION SELECT 26 UNION SELECT 27 UNION SELECT 28 UNION SELECT 29
    ) seq
    -- Generate slot numbers (0-15 covers a standard workday with 30-min slots)
    CROSS JOIN (
        SELECT 0 AS slot_num UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 
        UNION SELECT 4 UNION SELECT 5 UNION SELECT 6 UNION SELECT 7
        UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 UNION SELECT 11
        UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15
    ) slot
    -- Join with provider_services to get service IDs
    INNER JOIN provider_services ps ON rs.provider_id = ps.provider_id
WHERE 
    rs.is_active = 1
    -- Only include if day of week matches the generated date's day of week
    AND rs.day_of_week = WEEKDAY(DATE_ADD(CURDATE(), INTERVAL seq.n DAY)) + 1
    -- Only include slots that fit within the provider's schedule
    AND ADDTIME(rs.start_time, SEC_TO_TIME((slot.slot_num + 1) * 1800)) <= rs.end_time;

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
    'canceled', 
    'in_person', 
    'Patient called to cancel due to illness.', 
    'Follow-up appointment', 
    DATE_SUB(@current_datetime, INTERVAL 25 DAY), 
    DATE_SUB(@current_datetime, INTERVAL 21 DAY)
),
(
    @patient1_id, 
    @provider3_id, 
    37, 
    DATE_SUB(CURDATE(), INTERVAL 14 DAY), 
    '13:15:00', 
    '13:45:00', 
    'completed', 
    'virtual', 
    'Patient responded well to treatment. No concerns.', 
    'Urgent care follow-up', 
    DATE_SUB(@current_datetime, INTERVAL 21 DAY), 
    DATE_SUB(@current_datetime, INTERVAL 14 DAY)
),
(
    @patient2_id, 
    @provider2_id, 
    36, 
    DATE_SUB(CURDATE(), INTERVAL 5 DAY), 
    '10:00:00', 
    '10:30:00', 
    'completed', 
    'in_person', 
    'Patient\'s asthma well-controlled. No changes to current treatment plan.', 
    'Follow-up appointment', 
    DATE_SUB(@current_datetime, INTERVAL 10 DAY), 
    @current_datetime
),
(
    @patient3_id, 
    @provider3_id, 
    35, 
    DATE_SUB(CURDATE(), INTERVAL 3 DAY), 
    '14:00:00', 
    '14:45:00', 
    'no_show', 
    'in_person', 
    NULL, 
    'Diabetes management', 
    DATE_SUB(@current_datetime, INTERVAL 7 DAY), 
    @current_datetime
),
(
    @patient4_id, 
    @provider4_id, 
    38, 
    DATE_SUB(CURDATE(), INTERVAL 2 DAY), 
    '11:00:00', 
    '12:00:00', 
    'canceled', 
    'virtual', 
    'Patient called to reschedule.', 
    'Initial consultation', 
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
    'Nutritional plan created for patient\'s allergy concerns.', 
    'Dietary consultation', 
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
    'confirmed', 
    'in_person', 
    NULL, 
    'New patient visit', 
    DATE_SUB(@current_datetime, INTERVAL 2 DAY), 
    @current_datetime
),
(
    @patient7_id, 
    @provider1_id, 
    36, 
    DATE_ADD(CURDATE(), INTERVAL 1 DAY), 
    '11:30:00', 
    '12:00:00', 
    'confirmed', 
    'in_person', 
    NULL, 
    'Routine checkup', 
    DATE_SUB(@current_datetime, INTERVAL 1 DAY), 
    @current_datetime
),
(
    @patient8_id, 
    @provider3_id, 
    42, 
    DATE_ADD(CURDATE(), INTERVAL 2 DAY), 
    '13:00:00', 
    '13:40:00', 
    'confirmed', 
    'in_person', 
    NULL, 
    'Health screening', 
    @current_datetime, 
    @current_datetime
),
(
    @patient9_id, 
    @provider4_id, 
    39, 
    DATE_ADD(CURDATE(), INTERVAL 3 DAY), 
    '15:00:00', 
    '15:50:00', 
    'confirmed', 
    'virtual', 
    NULL, 
    'Therapy session', 
    @current_datetime, 
    @current_datetime
),
(
    @patient10_id, 
    @provider6_id, 
    43, 
    DATE_ADD(CURDATE(), INTERVAL 5 DAY), 
    '09:00:00', 
    '10:00:00', 
    'confirmed', 
    'in_person', 
    NULL, 
    'Allergy testing', 
    @current_datetime, 
    @current_datetime
),
(
    @patient1_id, 
    @provider1_id, 
    37, 
    DATE_SUB(CURDATE(), INTERVAL 10 DAY), 
    '14:30:00', 
    '15:00:00', 
    'completed', 
    'in_person', 
    'Patient had elevated blood pressure. Urgent care provided.', 
    'High blood pressure', 
    DATE_SUB(@current_datetime, INTERVAL 11 DAY), 
    @current_datetime
),
(
    @patient3_id, 
    @provider3_id, 
    36, 
    DATE_SUB(CURDATE(), INTERVAL 14 DAY), 
    '10:30:00', 
    '11:00:00', 
    'completed', 
    'in_person', 
    'Diabetes well-controlled. Continue current medication.', 
    'Follow-up appointment', 
    DATE_SUB(@current_datetime, INTERVAL 20 DAY), 
    @current_datetime
),
(
    @patient5_id, 
    @provider2_id, 
    36, 
    DATE_SUB(CURDATE(), INTERVAL 8 DAY), 
    '09:00:00', 
    '09:30:00', 
    'no_show', 
    'in_person', 
    NULL, 
    'Allergy follow-up', 
    DATE_SUB(@current_datetime, INTERVAL 15 DAY), 
    @current_datetime
),
(
    @patient7_id, 
    @provider5_id, 
    40, 
    DATE_SUB(CURDATE(), INTERVAL 6 DAY), 
    '13:00:00', 
    '13:45:00', 
    'canceled', 
    'virtual', 
    'Patient requested cancellation.', 
    'Nutritional guidance', 
    DATE_SUB(@current_datetime, INTERVAL 8 DAY), 
    @current_datetime
),
(
    @patient9_id, 
    @provider4_id, 
    39, 
    DATE_SUB(CURDATE(), INTERVAL 21 DAY), 
    '16:00:00', 
    '16:50:00', 
    'completed', 
    'virtual', 
    'Patient making good progress with anxiety management techniques.', 
    'Therapy session', 
    DATE_SUB(@current_datetime, INTERVAL 28 DAY), 
    @current_datetime
),
(
    @patient2_id, 
    @provider3_id, 
    35, 
    DATE_SUB(CURDATE(), INTERVAL 4 DAY), 
    '11:00:00', 
    '11:45:00', 
    'completed', 
    'in_person', 
    'Routine checkup completed. All vitals normal.', 
    'Annual physical', 
    DATE_SUB(@current_datetime, INTERVAL 10 DAY), 
    @current_datetime
),
(
    @patient4_id, 
    @provider5_id, 
    40, 
    DATE_SUB(CURDATE(), INTERVAL 3 DAY), 
    '14:30:00', 
    '15:15:00', 
    'completed', 
    'in_person', 
    'Nutritional plan established. Follow-up in 3 months.', 
    'Initial nutrition consultation', 
    DATE_SUB(@current_datetime, INTERVAL 7 DAY), 
    @current_datetime
),
(
    @patient2_id, 
    @provider1_id, 
    36, 
    DATE_ADD(CURDATE(), INTERVAL 7 DAY), 
    '09:15:00', 
    '09:45:00', 
    'confirmed', 
    'in_person', 
    NULL, 
    'Follow-up appointment', 
    DATE_SUB(@current_datetime, INTERVAL 2 DAY), 
    @current_datetime
),
(
    @patient3_id, 
    @provider2_id, 
    37, 
    DATE_ADD(CURDATE(), INTERVAL 6 DAY), 
    '13:30:00', 
    '14:00:00', 
    'confirmed', 
    'in_person', 
    NULL, 
    'Urgent care follow-up', 
    DATE_SUB(@current_datetime, INTERVAL 3 DAY), 
    @current_datetime
);

INSERT INTO appointment_ratings (
    appointment_id, 
    patient_id, 
    provider_id, 
    rating, 
    comment, 
    created_at
)
VALUES
(1, @patient1_id, @provider1_id, 5, 'Dr. Smith was very thorough and took time to explain everything.', DATE_SUB(@current_datetime, INTERVAL 6 DAY)),
(3, @patient1_id, @provider3_id, 4, 'Good virtual appointment experience. Dr. Williams was helpful.', DATE_SUB(@current_datetime, INTERVAL 13 DAY)),
(4, @patient2_id, @provider2_id, 4, 'Good experience overall. Short waiting time.', DATE_SUB(@current_datetime, INTERVAL 4 DAY)),
(5, @patient5_id, @provider5_id, 5, 'Very helpful nutritional advice. The plan is easy to follow.', @current_datetime),
(11, @patient1_id, @provider1_id, 5, 'Excellent urgent care. Dr. Smith addressed my concerns immediately.', DATE_SUB(@current_datetime, INTERVAL 9 DAY)),
(12, @patient3_id, @provider3_id, 4, 'Dr. Williams is always professional and attentive.', DATE_SUB(@current_datetime, INTERVAL 13 DAY)),
(15, @patient9_id, @provider4_id, 5, 'Dr. Brown has been extremely helpful with my anxiety issues.', DATE_SUB(@current_datetime, INTERVAL 20 DAY));

INSERT INTO activity_log (
    user_id, 
    description, 
    category, 
    created_at, 
    ip_address
)
VALUES
(@patient1_id, 'Auth: login_success', 'authentication', DATE_SUB(@current_datetime, INTERVAL 14 DAY), '192.168.1.100'),
(@patient1_id, 'Auth: logout', 'authentication', DATE_SUB(@current_datetime, INTERVAL 14 DAY), '192.168.1.100'),
(@provider1_id, 'Auth: login_success', 'authentication', DATE_SUB(@current_datetime, INTERVAL 7 DAY), '192.168.1.101'),
(@provider1_id, 'Appointment: notes_updated (ID: 1)', 'appointment', DATE_SUB(@current_datetime, INTERVAL 7 DAY), '192.168.1.101'),
(@provider1_id, 'Auth: logout', 'authentication', DATE_SUB(@current_datetime, INTERVAL 7 DAY), '192.168.1.101'),
(@patient3_id, 'Auth: login_success', 'authentication', DATE_SUB(@current_datetime, INTERVAL 5 DAY), '192.168.1.102'),
(@patient3_id, 'Auth: logout', 'authentication', DATE_SUB(@current_datetime, INTERVAL 5 DAY), '192.168.1.102'),
(@provider2_id, 'Auth: login_success', 'authentication', DATE_SUB(@current_datetime, INTERVAL 5 DAY), '192.168.1.103'),
(@provider2_id, 'Appointment: notes_updated (ID: 2)', 'appointment', DATE_SUB(@current_datetime, INTERVAL 5 DAY), '192.168.1.103'),
(@provider2_id, 'Auth: logout', 'authentication', DATE_SUB(@current_datetime, INTERVAL 5 DAY), '192.168.1.103'),
(@patient4_id, 'Auth: login_success', 'authentication', DATE_SUB(@current_datetime, INTERVAL 3 DAY), '192.168.1.104'),
(@patient4_id, 'Appointment: canceled (ID: 4)', 'appointment', DATE_SUB(@current_datetime, INTERVAL 3 DAY), '192.168.1.104'),
(@patient4_id, 'Auth: logout', 'authentication', DATE_SUB(@current_datetime, INTERVAL 3 DAY), '192.168.1.104'),
(@provider5_id, 'Auth: login_success', 'authentication', DATE_SUB(@current_datetime, INTERVAL 1 DAY), '192.168.1.105'),
(@provider5_id, 'Appointment: notes_updated (ID: 5)', 'appointment', DATE_SUB(@current_datetime, INTERVAL 1 DAY), '192.168.1.105'),
(@provider5_id, 'Auth: logout', 'authentication', DATE_SUB(@current_datetime, INTERVAL 1 DAY), '192.168.1.105'),
(@patient6_id, 'Auth: login_success', 'authentication', @current_datetime, '192.168.1.106'),
(@patient6_id, 'Auth: logout', 'authentication', @current_datetime, '192.168.1.106');

INSERT INTO notifications (
    user_id, 
    appointment_id, 
    subject, 
    message, 
    type, 
    status, 
    scheduled_for,
    sent_at,
    created_at, 
    is_system,
    is_read,
    audience
)
VALUES
(
    @patient6_id, 
    6, 
    'Appointment Reminder', 
    'Reminder: You have an appointment today at 10:00 AM', 
    'email', 
    'sent', 
    @current_datetime,
    @current_datetime,
    @current_datetime, 
    0,
    0,
    NULL
),
(
    @provider6_id, 
    6, 
    'New Appointment', 
    'New appointment scheduled today at 10:00 AM', 
    'app', 
    'sent', 
    @current_datetime,
    @current_datetime,
    @current_datetime, 
    0,
    1,
    NULL
),
(
    @patient7_id, 
    7, 
    'Appointment Reminder', 
    'Reminder: You have an appointment tomorrow at 11:30 AM', 
    'email', 
    'sent', 
    DATE_ADD(@current_datetime, INTERVAL 1 DAY),
    @current_datetime,
    @current_datetime, 
    0,
    0,
    NULL
),
(
    @provider1_id, 
    7, 
    'New Appointment', 
    'New appointment scheduled tomorrow at 11:30 AM', 
    'app', 
    'sent', 
    DATE_ADD(@current_datetime, INTERVAL 1 DAY),
    @current_datetime,
    @current_datetime, 
    0,
    1,
    NULL
),
(
    @patient9_id, 
    9, 
    'Appointment Confirmed', 
    CONCAT('Your virtual appointment has been scheduled for ', DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 3 DAY), '%m/%d/%Y'), ' at 3:00 PM'), 
    'email', 
    'sent', 
    DATE_ADD(@current_datetime, INTERVAL 3 DAY),
    @current_datetime,
    @current_datetime, 
    0,
    0,
    NULL
),
(
    @patient4_id, 
    4, 
    'Appointment Cancellation', 
    'Your appointment has been canceled as requested', 
    'email', 
    'sent', 
    NULL,
    DATE_SUB(@current_datetime, INTERVAL 2 DAY),
    DATE_SUB(@current_datetime, INTERVAL 2 DAY), 
    0,
    1,
    NULL
),
(
    3, -- Admin user ID is always 3 based on your database
    NULL, 
    'System Update', 
    'Database has been repopulated with sample data', 
    'app', 
    'sent', 
    NULL,
    @current_datetime,
    @current_datetime, 
    1,
    0,
    'admin'
);