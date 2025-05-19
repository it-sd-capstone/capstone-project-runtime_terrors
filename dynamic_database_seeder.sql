-- Initialize current datetime
SET @current_datetime = NOW();

-- Define standard password hashes
SET @admin_password = '$2y$10$6QBLl04XRuAj/y/bzRkAsuwgcm7pLZwFyBTuVejCB61NQs2TgSQ62'; -- Admin123@
SET @provider_password = '$2y$10$xaSYNovxOA8bQ16f7AEEPuTGdbzucps5N4sKmYlu0EbcV3ltpOUa.'; -- Provider123@
SET @patient_password = '$2y$10$orC5gFnaapRZiP6ZndYCFOmR7diEtkUNprBr4ZWaqZr0CWsK6dSJ2'; -- Patient123@

-- Function to generate phone numbers - create only if it doesn't exist
DROP FUNCTION IF EXISTS generate_phone;
DELIMITER //
CREATE FUNCTION generate_phone()
RETURNS VARCHAR(20)
DETERMINISTIC
BEGIN
    DECLARE area_code VARCHAR(3);
    DECLARE exchange VARCHAR(3);
    DECLARE subscriber VARCHAR(4);
    
    SET area_code = LPAD(FLOOR(RAND() * 900) + 100, 3, '0');
    SET exchange = LPAD(FLOOR(RAND() * 900) + 100, 3, '0');
    SET subscriber = LPAD(FLOOR(RAND() * 9000) + 1000, 4, '0');
    
    RETURN CONCAT('(', area_code, ') ', exchange, '-', subscriber);
END //
DELIMITER ;

-- Check and fix the waitlist foreign key constraint
SET @table_check = (SELECT COUNT(*) FROM information_schema.tables 
                    WHERE table_schema = DATABASE() 
                    AND table_name = 'services_old');

-- If services_old doesn't exist, we need to update the foreign key
SET @constraint_exists = (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE 
                         WHERE TABLE_SCHEMA = DATABASE() 
                         AND TABLE_NAME = 'waitlist' 
                         AND REFERENCED_TABLE_NAME = 'services_old' 
                         AND CONSTRAINT_NAME = 'waitlist_ibfk_3');

-- Drop and recreate the constraint if needed
SET @alter_statement = IF(@constraint_exists > 0, 
                        'ALTER TABLE waitlist DROP FOREIGN KEY waitlist_ibfk_3; 
                         ALTER TABLE waitlist ADD CONSTRAINT waitlist_ibfk_3 
                         FOREIGN KEY (service_id) REFERENCES services(service_id)',
                        'SELECT "Foreign key is already correct" AS message');

-- Only execute if the constraint exists and needs fixing
PREPARE stmt FROM @alter_statement;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Provider insertion with checks for existence
DROP PROCEDURE IF EXISTS InsertProviderUsersIfNotExist;
DELIMITER //
CREATE PROCEDURE InsertProviderUsersIfNotExist()
BEGIN
    DECLARE provider_count INT;
    DECLARE provider_emails VARCHAR(1000);
    
    -- Define the provider emails to insert
    SET provider_emails = 'provider@example.com,provider2@example.com,provider3@example.com,provider4@example.com,provider5@example.com,provider6@example.com';
    
    -- Check if these specific providers already exist
    SELECT COUNT(*) INTO provider_count FROM users 
    WHERE email IN ('provider@example.com','provider2@example.com','provider3@example.com',
                   'provider4@example.com','provider5@example.com','provider6@example.com');
    
    IF provider_count < 6 THEN
        -- Insert providers that don't exist
        INSERT INTO users (email, password_hash, first_name, last_name, phone, role, is_active, is_verified, email_verified_at, created_at)
        SELECT 
            email_value, 
            @provider_password, 
            CASE 
                WHEN email_value = 'provider@example.com' THEN 'Jennifer'
                WHEN email_value = 'provider2@example.com' THEN 'Michael'
                WHEN email_value = 'provider3@example.com' THEN 'David'
                WHEN email_value = 'provider4@example.com' THEN 'Sarah'
                WHEN email_value = 'provider5@example.com' THEN 'James'
                WHEN email_value = 'provider6@example.com' THEN 'Emily'
            END,
            CASE 
                WHEN email_value = 'provider@example.com' THEN 'Smith'
                WHEN email_value = 'provider2@example.com' THEN 'Johnson'
                WHEN email_value = 'provider3@example.com' THEN 'Williams'
                WHEN email_value = 'provider4@example.com' THEN 'Brown'
                WHEN email_value = 'provider5@example.com' THEN 'Davis'
                WHEN email_value = 'provider6@example.com' THEN 'Miller'
            END,
            generate_phone(), 
            'provider', 
            1, 
            1, 
            @current_datetime, 
            @current_datetime
        FROM (
            SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(@provider_emails, ',', numbers.n), ',', -1) AS email_value
            FROM (
                SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION SELECT 6
            ) numbers
            WHERE numbers.n <= 1 + LENGTH(@provider_emails) - LENGTH(REPLACE(@provider_emails, ',', ''))
        ) email_list
        WHERE email_value NOT IN (SELECT email FROM users);
               
        SELECT CONCAT('Inserted ', ROW_COUNT(), ' missing provider users') AS message;
    ELSE
        SELECT 'All provider users already exist' AS message;
    END IF;
END //
DELIMITER ;

-- Patient insertion with checks for existence
DROP PROCEDURE IF EXISTS InsertPatientUsersIfNotExist;
DELIMITER //
CREATE PROCEDURE InsertPatientUsersIfNotExist()
BEGIN
    DECLARE patient_count INT;
    DECLARE patient_emails VARCHAR(2000);
    
    -- Define the patient emails to insert
    SET patient_emails = 'patient@example.com,patient2@example.com,patient3@example.com,patient4@example.com,patient5@example.com,patient6@example.com,patient7@example.com,patient8@example.com,patient9@example.com,patient10@example.com';
    
    -- Check if these specific patients already exist
    SELECT COUNT(*) INTO patient_count FROM users 
    WHERE email IN ('patient@example.com','patient2@example.com','patient3@example.com',
                  'patient4@example.com','patient5@example.com','patient6@example.com',
                  'patient7@example.com','patient8@example.com','patient9@example.com',
                  'patient10@example.com');
    
    IF patient_count < 10 THEN
        -- Insert patients that don't exist
        INSERT INTO users (email, password_hash, first_name, last_name, phone, role, is_active, is_verified, email_verified_at, created_at)
        SELECT 
            email_value, 
            @patient_password, 
            CASE 
                WHEN email_value = 'patient@example.com' THEN 'Robert'
                WHEN email_value = 'patient2@example.com' THEN 'Lisa'
                WHEN email_value = 'patient3@example.com' THEN 'Thomas'
                WHEN email_value = 'patient4@example.com' THEN 'Jessica'
                WHEN email_value = 'patient5@example.com' THEN 'Daniel'
                WHEN email_value = 'patient6@example.com' THEN 'Michelle'
                WHEN email_value = 'patient7@example.com' THEN 'Kevin'
                WHEN email_value = 'patient8@example.com' THEN 'Patricia'
                WHEN email_value = 'patient9@example.com' THEN 'Christopher'
                WHEN email_value = 'patient10@example.com' THEN 'Amanda'
            END,
            CASE 
                WHEN email_value = 'patient@example.com' THEN 'Anderson'
                WHEN email_value = 'patient2@example.com' THEN 'Taylor'
                WHEN email_value = 'patient3@example.com' THEN 'Moore'
                WHEN email_value = 'patient4@example.com' THEN 'Jackson'
                WHEN email_value = 'patient5@example.com' THEN 'White'
                WHEN email_value = 'patient6@example.com' THEN 'Harris'
                WHEN email_value = 'patient7@example.com' THEN 'Martin'
                WHEN email_value = 'patient8@example.com' THEN 'Thompson'
                WHEN email_value = 'patient9@example.com' THEN 'Garcia'
                WHEN email_value = 'patient10@example.com' THEN 'Martinez'
            END,
            generate_phone(), 
            'patient', 
            1, 
            1, 
            @current_datetime, 
            @current_datetime
        FROM (
            SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(@patient_emails, ',', numbers.n), ',', -1) AS email_value
            FROM (
                SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 
                UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10
            ) numbers
            WHERE numbers.n <= 1 + LENGTH(@patient_emails) - LENGTH(REPLACE(@patient_emails, ',', ''))
        ) email_list
        WHERE email_value NOT IN (SELECT email FROM users);
        
        SELECT CONCAT('Inserted ', ROW_COUNT(), ' missing patient users') AS message;
    ELSE
        SELECT 'All patient users already exist' AS message;
    END IF;
END //
DELIMITER ;

-- Call the procedures to insert users if they don't exist
CALL InsertProviderUsersIfNotExist();
CALL InsertPatientUsersIfNotExist();

-- Dynamically set provider and patient IDs for reference
-- Find the provider IDs
SELECT user_id INTO @provider1_id FROM users WHERE email = 'provider@example.com';
SELECT user_id INTO @provider2_id FROM users WHERE email = 'provider2@example.com';
SELECT user_id INTO @provider3_id FROM users WHERE email = 'provider3@example.com';
SELECT user_id INTO @provider4_id FROM users WHERE email = 'provider4@example.com';
SELECT user_id INTO @provider5_id FROM users WHERE email = 'provider5@example.com';
SELECT user_id INTO @provider6_id FROM users WHERE email = 'provider6@example.com';

-- Find the patient IDs
SELECT user_id INTO @patient1_id FROM users WHERE email = 'patient@example.com';
SELECT user_id INTO @patient2_id FROM users WHERE email = 'patient2@example.com';
SELECT user_id INTO @patient3_id FROM users WHERE email = 'patient3@example.com';
SELECT user_id INTO @patient4_id FROM users WHERE email = 'patient4@example.com';
SELECT user_id INTO @patient5_id FROM users WHERE email = 'patient5@example.com';
SELECT user_id INTO @patient6_id FROM users WHERE email = 'patient6@example.com';
SELECT user_id INTO @patient7_id FROM users WHERE email = 'patient7@example.com';
SELECT user_id INTO @patient8_id FROM users WHERE email = 'patient8@example.com';
SELECT user_id INTO @patient9_id FROM users WHERE email = 'patient9@example.com';
SELECT user_id INTO @patient10_id FROM users WHERE email = 'patient10@example.com';

-- This script will populate the appointment system database with test data
-- It uses existing users from the database and generates all related data
-- Clear existing data first while preserving user data
SET FOREIGN_KEY_CHECKS=0;
TRUNCATE TABLE activity_log;
TRUNCATE TABLE appointment_history;
TRUNCATE TABLE appointment_process_logs;
TRUNCATE TABLE appointment_ratings;
TRUNCATE TABLE appointments;
TRUNCATE TABLE auth_sessions;
TRUNCATE TABLE notifications;
TRUNCATE TABLE notification_preferences;
TRUNCATE TABLE patient_profiles;
TRUNCATE TABLE provider_availability;
TRUNCATE TABLE provider_profiles;
TRUNCATE TABLE provider_services;
TRUNCATE TABLE recurring_schedules;
TRUNCATE TABLE services;
TRUNCATE TABLE settings;
TRUNCATE TABLE user_tokens;
TRUNCATE TABLE waitlist;
SET FOREIGN_KEY_CHECKS=1;

-- Add services
INSERT INTO services (name, description, duration, price, is_active) VALUES
('Initial Consultation', 'First-time patient assessment and consultation', 60, 150.00, 1),
('Follow-up Visit', 'Regular check-up for existing patients', 30, 75.00, 1),
('Comprehensive Examination', 'Detailed physical and medical examination', 90, 250.00, 1),
('Emergency Consultation', 'Urgent care appointment', 45, 200.00, 1),
('Telehealth Visit', 'Virtual consultation via video call', 30, 65.00, 1),
('Specialized Assessment', 'Focused examination for specific conditions', 60, 175.00, 1),
('Preventive Care Visit', 'Health maintenance and disease prevention', 45, 125.00, 1),
('Chronic Disease Management', 'Ongoing care for long-term conditions', 45, 150.00, 1),
('Mental Health Consultation', 'Psychological assessment and support', 60, 160.00, 1),
('Pediatric Check-up', 'Health assessment for children', 30, 85.00, 1),
('Senior Health Evaluation', 'Comprehensive assessment for elderly patients', 60, 165.00, 1),
('Medication Review', 'Evaluation of current medications and adjustments', 30, 70.00, 1),
('Physical Therapy Session', 'Therapeutic physical rehabilitation exercises', 45, 120.00, 1),
('Nutritional Counseling', 'Dietary guidance and nutritional planning', 45, 100.00, 1),
('Health Screening', 'Basic health measurements and prevention', 30, 60.00, 1);

-- Create provider profiles for existing users
INSERT INTO provider_profiles (provider_id, specialization, title, bio, accepting_new_patients)
SELECT user_id, 
       CASE MOD(user_id, 5)
         WHEN 0 THEN 'Family Medicine'
         WHEN 1 THEN 'Internal Medicine'
         WHEN 2 THEN 'Pediatrics'
         WHEN 3 THEN 'Mental Health'
         WHEN 4 THEN 'Preventive Care'
       END,
       CASE MOD(user_id, 3)
         WHEN 0 THEN 'MD'
         WHEN 1 THEN 'DO'
         WHEN 2 THEN 'NP'
       END,
       CONCAT('Experienced healthcare provider with expertise in ', 
              CASE MOD(user_id, 5)
                WHEN 0 THEN 'family medicine and primary care.'
                WHEN 1 THEN 'internal medicine and chronic disease management.'
                WHEN 2 THEN 'pediatric care and childhood development.'
                WHEN 3 THEN 'mental health counseling and therapy.'
                WHEN 4 THEN 'preventive care and wellness programs.'
              END),
       1
FROM users 
WHERE role = 'provider' AND user_id NOT IN (SELECT provider_id FROM provider_profiles);

-- Create patient profiles for existing patients
INSERT INTO patient_profiles (user_id, phone, date_of_birth, address, emergency_contact, emergency_contact_phone)
SELECT user_id, 
       phone,
       DATE_SUB(CURRENT_DATE, INTERVAL (20 + MOD(user_id, 50)) YEAR),
       CONCAT(100 + MOD(user_id, 900), ' Main St, Anytown, State ', 10000 + MOD(user_id, 90000)),
       CONCAT('Emergency Contact for ', first_name),
       CONCAT('(', 100 + MOD(user_id, 900), ') ', 100 + MOD(user_id, 900), '-', 1000 + MOD(user_id, 9000))
FROM users
WHERE role = 'patient' AND user_id NOT IN (SELECT user_id FROM patient_profiles);

-- Assign services to providers (each provider offers multiple services)
INSERT INTO provider_services (provider_id, service_id, custom_duration)
WITH provider_service_mapping AS (
    SELECT 
        u.user_id as provider_id,
        s.service_id,
        ROW_NUMBER() OVER (PARTITION BY u.user_id ORDER BY RAND()) as service_rank
    FROM users u
    CROSS JOIN services s
    WHERE u.role = 'provider'
)
SELECT 
    psm.provider_id,     
    psm.service_id,      -- Explicitly qualify service_id with the psm table alias
    CASE WHEN RAND() < 0.3 THEN s.duration + (15 * FLOOR(RAND() * 3)) ELSE NULL END as custom_duration
FROM provider_service_mapping psm
JOIN services s ON psm.service_id = s.service_id
WHERE service_rank <= 5 + FLOOR(RAND() * 5); -- Each provider gets 5-10 services

-- Create recurring schedules for providers
INSERT INTO recurring_schedules (provider_id, day_of_week, start_time, end_time, is_active, effective_from)
WITH days AS (
    SELECT 1 as day_num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5
),
providers AS (
    SELECT user_id FROM users WHERE role = 'provider'
)
SELECT 
    p.user_id,
    d.day_num,
    CASE 
        WHEN MOD(p.user_id, 2) = 0 THEN '08:00:00'
        ELSE '09:00:00'
    END as start_time,
    CASE 
        WHEN MOD(p.user_id, 2) = 0 THEN '17:00:00'
        ELSE '18:00:00'
    END as end_time,
    1,
    DATE_SUB(CURRENT_DATE, INTERVAL 30 DAY)
FROM providers p
CROSS JOIN days d
WHERE (p.user_id + d.day_num) % 7 != 0; -- Each provider works 5 days with different patterns

-- Generate availability slots based on recurring schedules and service durations
-- This is the key part that ensures availability slots match service durations
-- and fit within recurring schedules
DELIMITER //
CREATE PROCEDURE GenerateAvailabilitySlots()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE p_id INT;
    DECLARE curr_date DATE;
    DECLARE days_to_generate INT DEFAULT 30;
    
    -- Get all providers
    DECLARE provider_cursor CURSOR FOR 
        SELECT DISTINCT provider_id FROM recurring_schedules;
    
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    SET curr_date = CURRENT_DATE;
    
    OPEN provider_cursor;
    
    provider_loop: LOOP
        FETCH provider_cursor INTO p_id;
        IF done THEN
            LEAVE provider_loop;
        END IF;
        
        -- For each day in the range
        SET @day_counter = 0;
        WHILE @day_counter < days_to_generate DO
            SET @current_check_date = DATE_ADD(curr_date, INTERVAL @day_counter DAY);
            SET @day_of_week = WEEKDAY(@current_check_date) + 1; -- 1=Monday, 7=Sunday
            
            -- Check if provider has a schedule for this day
            IF EXISTS (SELECT 1 FROM recurring_schedules 
                       WHERE provider_id = p_id 
                       AND day_of_week = @day_of_week
                       AND is_active = 1) THEN
                
                -- Get the schedule for this day
                SELECT start_time, end_time 
                INTO @day_start, @day_end
                FROM recurring_schedules
                WHERE provider_id = p_id 
                AND day_of_week = @day_of_week
                AND is_active = 1
                LIMIT 1;
                
                -- Create availability slots for each hour
                SET @slot_start = @day_start;
                
                WHILE TIME_TO_SEC(@slot_start) < TIME_TO_SEC(@day_end) DO
                    -- Calculate slot end (1 hour from start)
                    SET @slot_end = ADDTIME(@slot_start, '01:00:00');
                    
                    -- Make sure the end time doesn't exceed the schedule end time
                    IF TIME_TO_SEC(@slot_end) > TIME_TO_SEC(@day_end) THEN
                        SET @slot_end = @day_end;
                    END IF;
                    
                    -- Create slots for each service this provider offers
                    INSERT INTO provider_availability 
                        (provider_id, availability_date, start_time, end_time, is_available, service_id)
                    SELECT 
                        p_id,
                        @current_check_date,
                        @slot_start,
                        @slot_end,
                        1,
                        ps.service_id
                    FROM provider_services ps
                    WHERE ps.provider_id = p_id
                    -- Randomize availability (70% chance of being available)
                    AND RAND() < 0.7;
                    
                    -- Move to next hour
                    SET @slot_start = @slot_end;
                END WHILE;
            END IF;
            
            SET @day_counter = @day_counter + 1;
        END WHILE;
    END LOOP;
    
    CLOSE provider_cursor;
END //
DELIMITER ;

-- Execute the stored procedure
CALL GenerateAvailabilitySlots();

-- Drop the procedure after use
DROP PROCEDURE IF EXISTS GenerateAvailabilitySlots;

-- Create more FUTURE appointments (confirmed/pending status)
INSERT INTO appointments (patient_id, provider_id, service_id, appointment_date,
                         start_time, end_time, status, type, notes, reason)
SELECT
    p.user_id AS patient_id,
    pa.provider_id,
    pa.service_id,
    pa.availability_date,
    pa.start_time,
    pa.end_time,
    CASE
        WHEN RAND() < 0.9 THEN 'confirmed'
        ELSE 'pending'
    END as status,
    ELT(1 + FLOOR(RAND() * 3), 'in_person', 'virtual', 'phone') as type,
    CASE
        WHEN RAND() < 0.7 THEN CONCAT('Notes for appointment with patient ', p.first_name, ' ', p.last_name)
        ELSE NULL
    END as notes,
    CONCAT('Patient requesting ', s.name) as reason
FROM (
    SELECT
        u.user_id,
        u.first_name,
        u.last_name,
        ROW_NUMBER() OVER (ORDER BY RAND()) as row_num
    FROM users u
    WHERE u.role = 'patient'
) p
JOIN (
    SELECT
        pa.*,
        ROW_NUMBER() OVER (ORDER BY RAND()) as row_num
    FROM provider_availability pa
    WHERE 
        pa.availability_date >= CURRENT_DATE
        AND NOT EXISTS (
            SELECT 1 FROM appointments a
            WHERE a.provider_id = pa.provider_id
            AND a.appointment_date = pa.availability_date
            AND a.start_time = pa.start_time
        )
) pa ON p.row_num % 1000 = pa.row_num % 1000
JOIN services s ON pa.service_id = s.service_id
LIMIT 400; -- Create 400 future appointments

-- Create more past appointments for historical data
INSERT INTO appointments (patient_id, provider_id, service_id, appointment_date,
                        start_time, end_time, status, type, notes, reason)
SELECT
    p.user_id AS patient_id,
    pp.provider_id,
    ps.service_id,
    DATE_SUB(CURRENT_DATE, INTERVAL (1 + FLOOR(RAND() * 120)) DAY) AS appointment_date,
    ADDTIME('08:00:00', SEC_TO_TIME(FLOOR(RAND() * 9) * 3600)) AS start_time,
    ADDTIME('08:00:00', SEC_TO_TIME((FLOOR(RAND() * 9) + 1) * 3600)) AS end_time,
    CASE 
        WHEN RAND() < 0.5 THEN 'completed'  -- 50% completed
        WHEN RAND() < 0.8 THEN 'canceled'   -- 30% canceled
        ELSE 'no_show'                      -- 20% no-show
    END as status,
    ELT(1 + FLOOR(RAND() * 3), 'in_person', 'virtual', 'phone') as type,
    CASE
        WHEN RAND() < 0.7 THEN CONCAT('Historical appointment with patient ', u.first_name, ' ', u.last_name)
        ELSE NULL
    END as notes,
    CONCAT('Historical ', s.name, ' appointment') as reason
FROM 
    (SELECT user_id FROM users WHERE role = 'patient' ORDER BY RAND() LIMIT 100) p
JOIN 
    users u ON p.user_id = u.user_id
JOIN 
    provider_profiles pp ON pp.accepting_new_patients = 1
JOIN 
    provider_services ps ON ps.provider_id = pp.provider_id
JOIN 
    services s ON ps.service_id = s.service_id
WHERE
    DAYOFWEEK(DATE_SUB(CURRENT_DATE, INTERVAL (1 + FLOOR(RAND() * 120)) DAY)) BETWEEN 2 AND 6
GROUP BY 
    p.user_id, pp.provider_id, ps.service_id
LIMIT 300; -- Additional 300 historical appointments

-- Create more detailed appointment history for past appointments
INSERT INTO appointment_history (appointment_id, action, changed_fields, user_id, created_at)
SELECT
    a.appointment_id,
    'created',
    NULL,
    a.patient_id,
    DATE_SUB(a.appointment_date, INTERVAL (3 + FLOOR(RAND() * 14)) DAY)
FROM appointments a
WHERE a.appointment_id NOT IN (SELECT appointment_id FROM appointment_history);

-- Add confirmation history
INSERT INTO appointment_history (appointment_id, action, changed_fields, user_id, created_at)
SELECT
    a.appointment_id,
    'confirmed',
    '{"status":"confirmed"}',
    CASE WHEN RAND() < 0.7 THEN a.patient_id ELSE a.provider_id END,
    DATE_SUB(a.appointment_date, INTERVAL (1 + FLOOR(RAND() * 7)) DAY)
FROM appointments a
WHERE a.status IN ('confirmed', 'completed', 'canceled', 'no_show')
AND a.appointment_id NOT IN (
    SELECT appointment_id FROM appointment_history WHERE action = 'confirmed'
);

-- Add completion/cancellation/no-show history
INSERT INTO appointment_history (appointment_id, action, changed_fields, user_id, created_at)
SELECT
    a.appointment_id,
    a.status,
    CONCAT('{"status":"', a.status, '"}'),
    CASE 
        WHEN a.status = 'canceled' AND RAND() < 0.8 THEN a.patient_id
        ELSE a.provider_id
    END,
    CASE
        WHEN a.status = 'completed' THEN DATE_ADD(a.appointment_date, INTERVAL FLOOR(RAND() * 3) HOUR)
        WHEN a.status = 'canceled' THEN DATE_SUB(a.appointment_date, INTERVAL FLOOR(RAND() * 24) HOUR)
        WHEN a.status = 'no_show' THEN DATE_ADD(a.appointment_date, INTERVAL (15 + FLOOR(RAND() * 30)) MINUTE)
        ELSE a.appointment_date
    END
FROM appointments a
WHERE a.status IN ('completed', 'canceled', 'no_show')
AND a.appointment_id NOT IN (
    SELECT appointment_id FROM appointment_history WHERE action IN ('completed', 'canceled', 'no_show')
);

-- Add appointment ratings for completed appointments (make sure all get rated)
INSERT INTO appointment_ratings (appointment_id, patient_id, provider_id, rating, comment, created_at)
SELECT
    a.appointment_id,
    a.patient_id,
    a.provider_id,
    3 + FLOOR(RAND() * 3) as rating, -- Ratings from 3-5
    CASE 
        WHEN RAND() < 0.7 THEN CONCAT('Great experience with Dr. ', 
                                      (SELECT last_name FROM users WHERE user_id = a.provider_id), 
                                      '. ', 
                                      ELT(1 + FLOOR(RAND() * 3), 
                                          'Very professional and helpful.', 
                                          'Took time to explain everything clearly.', 
                                          'Would recommend to others.'))
        ELSE NULL
    END as comment,
    DATE_ADD(a.appointment_date, INTERVAL 1 DAY)
FROM appointments a
WHERE a.status = 'completed'
AND RAND() < 0.8; -- 80% of completed appointments get rated

-- Generate notification preferences for all users
INSERT INTO notification_preferences (user_id, email_notifications, sms_notifications, appointment_reminders, system_updates, reminder_time)
SELECT 
    user_id,
    1, -- Email on by default
    CASE WHEN RAND() < 0.6 THEN 1 ELSE 0 END, -- 60% enable SMS
    1, -- Appointment reminders on by default
    CASE WHEN RAND() < 0.8 THEN 1 ELSE 0 END, -- 80% enable system updates
    CASE 
        WHEN RAND() < 0.3 THEN 12
        WHEN RAND() < 0.6 THEN 24
        WHEN RAND() < 0.8 THEN 48
        ELSE 72
    END -- Different reminder times
FROM users
WHERE user_id NOT IN (SELECT user_id FROM notification_preferences);

-- Create notifications for appointments - with guaranteed uniqueness
INSERT INTO notifications (user_id, appointment_id, subject, message, type, status, sent_at, is_read)
SELECT 
    a.patient_id,
    a.appointment_id,
    CONCAT('Appointment #', a.appointment_id, ' with Dr. ', u.last_name, ' on ', DATE_FORMAT(a.appointment_date, '%b %d')),
    CONCAT('You have an appointment (#', a.appointment_id, ') scheduled with Dr. ', u.first_name, ' ', u.last_name, 
           ' on ', DATE_FORMAT(a.appointment_date, '%b %d, %Y'), ' at ', 
           DATE_FORMAT(a.start_time, '%h:%i %p'), ' for ', s.name, '.'),
    'appointment',
    CASE 
        WHEN a.appointment_date < CURRENT_DATE THEN 'sent'
        ELSE 'pending'
    END,
    CASE 
        WHEN a.appointment_date < CURRENT_DATE THEN DATE_SUB(a.appointment_date, INTERVAL 1 DAY)
        ELSE NULL
    END,
    CASE 
        WHEN a.appointment_date < CURRENT_DATE THEN 1
        ELSE 0
    END
FROM appointments a
JOIN users u ON a.provider_id = u.user_id
JOIN services s ON a.service_id = s.service_id
WHERE RAND() < 0.9; -- 90% of appointments get notifications

-- Create system notifications
INSERT INTO notifications (user_id, subject, message, type, status, sent_at, is_read, is_system, audience)
WITH system_notifications AS (
    SELECT
        'System Maintenance Scheduled' as subject,
        'The system will be undergoing maintenance on Sunday from 2am-4am EST.' as message,
        'all' as audience
    UNION ALL
    SELECT
        'New Feature: Video Consultations' as subject,
        'We have added support for video consultations. Update your preferences to enable this feature.' as message,
        'all' as audience
    UNION ALL
    SELECT
        'COVID-19 Protocol Update' as subject,
        'We have updated our COVID-19 protocols. Please review the new guidelines before your next visit.' as message,
        'all' as audience
    UNION ALL
    SELECT
        'Holiday Hours' as subject,
        'Please note our modified holiday hours for the upcoming season.' as message,
        'all' as audience
    UNION ALL
    SELECT
        'Insurance Update Required' as subject,
        'Please update your insurance information before your next appointment.' as message,
        'patient' as audience
    UNION ALL
    SELECT
        'Provider Training Available' as subject,
        'New training modules are available for the updated electronic health record system.' as message,
        'provider' as audience
    UNION ALL
    SELECT
        'Patient Satisfaction Survey Results' as subject,
        'The quarterly patient satisfaction survey results are now available for review.' as message,
        'provider' as audience
    UNION ALL
    SELECT
        'System Security Update' as subject,
        'A security update has been applied to all systems. No action is required.' as message,
        'admin' as audience
)
SELECT
    u.user_id,
    CONCAT(sn.subject, ' [', u.user_id, ']'),  -- Make the subject unique per user
    sn.message,
    'system',
    'sent',
    DATE_SUB(CURRENT_DATE, INTERVAL FLOOR(RAND() * 30) DAY),
    CASE WHEN RAND() < 0.7 THEN 1 ELSE 0 END,
    1,
    sn.audience
FROM system_notifications sn
JOIN users u ON
    (sn.audience = 'all') OR
    (sn.audience = 'admin' AND u.role = 'admin') OR
    (sn.audience = 'provider' AND u.role = 'provider') OR
    (sn.audience = 'patient' AND u.role = 'patient')
GROUP BY u.user_id, sn.subject, sn.message, sn.audience; -- Include message in GROUP BY

-- Create activity logs
INSERT INTO activity_log (user_id, description, category, ip_address, details, related_id, related_type)
SELECT 
    CASE 
        WHEN a.patient_id = a.provider_id THEN NULL -- System action
        WHEN RAND() < 0.5 THEN a.patient_id
        ELSE a.provider_id
    END,
    CASE 
        WHEN a.status = 'scheduled' THEN 'Appointment scheduled'
        WHEN a.status = 'confirmed' THEN 'Appointment confirmed'
        WHEN a.status = 'completed' THEN 'Appointment completed'
        WHEN a.status = 'canceled' THEN 'Appointment canceled'
        WHEN a.status = 'no_show' THEN 'Patient no-show recorded'
    END,
    'appointment',
    CONCAT('192.168.', FLOOR(RAND() * 255), '.', FLOOR(RAND() * 255)),
    CONCAT('{"appointment_id":', a.appointment_id, ',"patient_id":', a.patient_id, ',"provider_id":', a.provider_id, '}'),
    a.appointment_id,
    'appointment'
FROM appointments a;

-- Add login activity
INSERT INTO activity_log (user_id, description, category, ip_address, created_at)
WITH login_data AS (
    SELECT 
        u.user_id,
        CASE WHEN u.role = 'patient' THEN 3 ELSE 10 END as login_count
    FROM users u
)
SELECT 
    ld.user_id,
    'User login',
    'auth',
    CONCAT('192.168.', FLOOR(RAND() * 255), '.', FLOOR(RAND() * 255)),
    DATE_SUB(CURRENT_TIMESTAMP, INTERVAL FLOOR(RAND() * 30) DAY)
FROM login_data ld
JOIN (
    SELECT 1 as num UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 UNION
    SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10
) numbers ON numbers.num <= ld.login_count;

-- Add some profile update logs
INSERT INTO activity_log (user_id, description, category, ip_address, details, related_id, related_type)
SELECT 
    user_id,
    'Profile updated',
    'user',
    CONCAT('192.168.', FLOOR(RAND() * 255), '.', FLOOR(RAND() * 255)),
    '{"fields_updated":["phone","address"]}',
    user_id,
    'user'
FROM users
WHERE RAND() < 0.3; -- 30% of users updated their profile

-- Fix the waitlist insertion by using proper foreign key references
DROP PROCEDURE IF EXISTS CreateWaitlistEntries;
DELIMITER //
CREATE PROCEDURE CreateWaitlistEntries()
BEGIN
    -- Check if we have any services to use
    DECLARE service_count INT;
    SELECT COUNT(*) INTO service_count FROM services;
    
    IF service_count = 0 THEN
        SELECT 'No services available to create waitlist entries' AS message;
    ELSE
        -- Create waitlist entries with proper service_id references
        INSERT INTO waitlist (patient_id, provider_id, service_id, preferred_date, preferred_time, flexibility, created_at)
        SELECT
            u.user_id,
            p.user_id,
            s.service_id,
            DATE_ADD(CURRENT_DATE, INTERVAL (1 + FLOOR(RAND() * 30)) DAY),
            ELT(1 + FLOOR(RAND() * 3), '09:00:00', '13:00:00', '16:00:00'),
            ELT(1 + FLOOR(RAND() * 4), 'strict', 'flexible_time', 'flexible_day', 'flexible_provider'),
            DATE_SUB(CURRENT_TIMESTAMP, INTERVAL FLOOR(RAND() * 10) DAY)
        FROM
            users u,
            (SELECT user_id FROM users WHERE role = 'provider' ORDER BY RAND() LIMIT 3) p,
            (SELECT service_id FROM services ORDER BY RAND() LIMIT 3) s
        WHERE
            u.role = 'patient'
            AND u.user_id NOT IN (SELECT patient_id FROM waitlist)
        ORDER BY RAND()
        LIMIT 10;
        
        SELECT CONCAT('Created ', ROW_COUNT(), ' waitlist entries') AS message;
    END IF;
END //
DELIMITER ;

-- Create auth sessions
INSERT INTO auth_sessions (user_id, token, ip_address, user_agent, expires_at, last_active)
SELECT 
    user_id,
    MD5(CONCAT(user_id, RAND())),
    CONCAT('192.168.', FLOOR(RAND() * 255), '.', FLOOR(RAND() * 255)),
    ELT(1 + FLOOR(RAND() * 3), 
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1'),
    DATE_ADD(CURRENT_TIMESTAMP, INTERVAL (1 + FLOOR(RAND() * 7)) DAY),
    DATE_SUB(CURRENT_TIMESTAMP, INTERVAL FLOOR(RAND() * 2) HOUR)
FROM users
WHERE last_login IS NOT NULL  -- Only create sessions for users who have logged in
ORDER BY RAND()
LIMIT 30; -- Create 30 active sessions

-- Create user tokens
INSERT INTO user_tokens (user_id, selector, token, expires, created_at)
SELECT 
    user_id,
    SUBSTRING(MD5(RAND()), 1, 16),
    SUBSTRING(SHA1(CONCAT(user_id, RAND())), 1, 64),
    DATE_ADD(CURRENT_TIMESTAMP, INTERVAL (7 + FLOOR(RAND() * 30)) DAY),
    DATE_SUB(CURRENT_TIMESTAMP, INTERVAL FLOOR(RAND() * 30) DAY)
FROM users
WHERE is_active = 1
ORDER BY RAND()
LIMIT 40; -- Create 40 tokens

-- Add system settings
INSERT INTO settings (setting_key, setting_value, description)
VALUES
('system_name', 'Health Appointment System', 'Name of the appointment system'),
('appointment_buffer', '15', 'Buffer time in minutes between appointments'),
('email_sender', 'appointments@example.com', 'Email address used for sending notifications'),
('max_daily_appointments', '20', 'Maximum appointments per provider per day'),
('maintenance_mode', '0', 'System maintenance mode (0=off, 1=on)'),
('appointment_confirmation_required', '1', 'Require confirmation for appointments'),
('default_reminder_time', '24', 'Default reminder time in hours before appointment'),
('cancellation_window', '24', 'Hours before appointment that cancellation is allowed without penalty');

-- Add appointment process logs
INSERT INTO appointment_process_logs (timestamp, user_id, user_role, ip_address, user_agent, action, entity, entity_id)
SELECT
    CASE
        WHEN a.status IN ('completed', 'canceled', 'no_show') THEN DATE_ADD(a.appointment_date, INTERVAL -1 DAY)
        ELSE a.created_at
    END,
    CASE
        WHEN RAND() < 0.7 THEN a.patient_id
        ELSE a.provider_id
    END,
    CASE
        WHEN RAND() < 0.7 THEN 'patient'
        ELSE 'provider'
    END,
    CONCAT('192.168.', FLOOR(RAND() * 255), '.', FLOOR(RAND() * 255)),
    ELT(1 + FLOOR(RAND() * 3),
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.1.1 Safari/605.1.15',
        'Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1'),
    CASE a.status
        WHEN 'confirmed' THEN 'confirm'  -- Changed from 'scheduled' to 'confirmed'
        WHEN 'completed' THEN 'complete'
        WHEN 'canceled' THEN 'cancel'
        WHEN 'no_show' THEN 'mark_no_show'
    END,
    'appointment',
    a.appointment_id
FROM appointments a;

-- Mark some patients with deleted accounts but preserved data
UPDATE users 
SET is_active = 0, 
    email = CONCAT('deleted_', user_id, '@deleted.example.com'),
    password_hash = ''
WHERE role = 'patient' 
AND RAND() < 0.05; -- 5% of patients have deactivated accounts

-- Output completion message
SELECT 'Database population complete. Created records:' as Message;
SELECT 
    (SELECT COUNT(*) FROM services) as Services,
    (SELECT COUNT(*) FROM provider_profiles) as Provider_Profiles,
    (SELECT COUNT(*) FROM patient_profiles) as Patient_Profiles,
    (SELECT COUNT(*) FROM provider_services) as Provider_Services,
    (SELECT COUNT(*) FROM recurring_schedules) as Recurring_Schedules,
    (SELECT COUNT(*) FROM provider_availability) as Provider_Availability_Slots,
    (SELECT COUNT(*) FROM appointments) as Appointments,
    (SELECT COUNT(*) FROM appointment_ratings) as Ratings,
    (SELECT COUNT(*) FROM notifications) as Notifications,
    (SELECT COUNT(*) FROM activity_log) as Activity_Logs,
    (SELECT COUNT(*) FROM waitlist) as Waitlist_Entries;

-- Clean up procedures after use
CALL CreateWaitlistEntries();
DROP PROCEDURE IF EXISTS InsertProviderUsersIfNotExist;
DROP PROCEDURE IF EXISTS InsertPatientUsersIfNotExist;
DROP PROCEDURE IF EXISTS CreateWaitlistEntries;
