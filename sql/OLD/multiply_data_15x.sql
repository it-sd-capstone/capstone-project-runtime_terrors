DELIMITER //

CREATE PROCEDURE MultiplyData15x()
BEGIN
    -- Variables for tracking IDs
    DECLARE base_patient_id INT;
    DECLARE max_patient_id INT;
    DECLARE base_provider_id INT;
    DECLARE max_provider_id INT;
    DECLARE clone_counter INT DEFAULT 0;
    DECLARE i INT;
    
    -- Run the original script first to establish baseline data
    -- You would run your MassDBfill.sql before this procedure to set up the initial data
    
    -- Record the last IDs from the initial load
    SELECT MIN(user_id), MAX(user_id) INTO base_patient_id, max_patient_id 
    FROM users WHERE role = 'patient';
    
    SELECT MIN(user_id), MAX(user_id) INTO base_provider_id, max_provider_id 
    FROM users WHERE role = 'provider';
    
    -- Now multiply the data 14 more times (for a total of 15x)
    WHILE clone_counter < 14 DO
        -- Temporarily disable foreign key checks for faster inserts
        SET FOREIGN_KEY_CHECKS = 0;
        
        -- Clone patients with modified data to ensure uniqueness
        INSERT INTO users (email, password_hash, first_name, last_name, phone, role, is_active, is_verified, email_verified_at, created_at)
        SELECT 
            CONCAT('clone', clone_counter, '_', email),
            password_hash,
            CONCAT(first_name, '_', clone_counter),
            CONCAT(last_name, '_', clone_counter),
            CONCAT(SUBSTRING(phone, 1, 8), LPAD(FLOOR(RAND() * 10000), 4, '0')),
            role,
            is_active,
            is_verified,
            DATE_ADD(email_verified_at, INTERVAL FLOOR(RAND() * 10) DAY),
            DATE_ADD(created_at, INTERVAL FLOOR(RAND() * 10) DAY)
        FROM users
        WHERE role = 'patient' AND user_id BETWEEN base_patient_id AND max_patient_id;
        
        -- Store the range of new patient IDs
        SET @new_patient_start = (SELECT MAX(user_id) - ((max_patient_id - base_patient_id) + 1) + 1 FROM users);
        SET @new_patient_end = (SELECT MAX(user_id) FROM users);
        
        -- Clone providers with modified data to ensure uniqueness
        INSERT INTO users (email, password_hash, first_name, last_name, phone, role, is_active, is_verified, email_verified_at, created_at)
        SELECT 
            CONCAT('clone', clone_counter, '_', email),
            password_hash,
            CONCAT(first_name, '_', clone_counter),
            CONCAT(last_name, '_', clone_counter),
            CONCAT(SUBSTRING(phone, 1, 8), LPAD(FLOOR(RAND() * 10000), 4, '0')),
            role,
            is_active,
            is_verified,
            DATE_ADD(email_verified_at, INTERVAL FLOOR(RAND() * 10) DAY),
            DATE_ADD(created_at, INTERVAL FLOOR(RAND() * 10) DAY)
        FROM users
        WHERE role = 'provider' AND user_id BETWEEN base_provider_id AND max_provider_id;
        
        -- Store the range of new provider IDs
        SET @new_provider_start = (SELECT MAX(user_id) - ((max_provider_id - base_provider_id) + 1) + 1 FROM users);
        SET @new_provider_end = (SELECT MAX(user_id) FROM users);
        
        -- Clone provider profiles
        INSERT INTO provider_profiles (provider_id, specialization, title, bio, accepting_new_patients, max_patients_per_day, created_at, updated_at)
        SELECT
            @new_provider_start + (provider_id - base_provider_id),
            specialization,
            title,
            CONCAT(bio, ' (Clone ', clone_counter, ')'),
            accepting_new_patients,
            max_patients_per_day,
            DATE_ADD(created_at, INTERVAL FLOOR(RAND() * 10) DAY),
            DATE_ADD(updated_at, INTERVAL FLOOR(RAND() * 10) DAY)
        FROM provider_profiles
        WHERE provider_id BETWEEN base_provider_id AND max_provider_id;
        
        -- Clone patient profiles
        INSERT INTO patient_profiles (user_id, phone, date_of_birth, address, emergency_contact, emergency_contact_phone, medical_conditions, medical_history, insurance_info, created_at, updated_at)
        SELECT
            @new_patient_start + (user_id - base_patient_id),
            CONCAT(SUBSTRING(phone, 1, 8), LPAD(FLOOR(RAND() * 10000), 4, '0')),
            DATE_ADD(date_of_birth, INTERVAL FLOOR(RAND() * 365) DAY),
            CONCAT(address, ' Clone ', clone_counter),
            CONCAT(emergency_contact, ' Clone'),
            CONCAT(SUBSTRING(emergency_contact_phone, 1, 8), LPAD(FLOOR(RAND() * 10000), 4, '0')),
            medical_conditions,
            medical_history,
            CONCAT(insurance_info, ' (Clone)'),
            DATE_ADD(created_at, INTERVAL FLOOR(RAND() * 10) DAY),
            DATE_ADD(updated_at, INTERVAL FLOOR(RAND() * 10) DAY)
        FROM patient_profiles
        WHERE user_id BETWEEN base_patient_id AND max_patient_id;
        
        -- Clone provider services
        INSERT INTO provider_services (provider_id, service_id, created_at)
        SELECT
            @new_provider_start + (provider_id - base_provider_id),
            service_id,
            DATE_ADD(created_at, INTERVAL FLOOR(RAND() * 10) DAY)
        FROM provider_services
        WHERE provider_id BETWEEN base_provider_id AND max_provider_id;
        
        -- Clone recurring schedules
        INSERT INTO recurring_schedules (provider_id, day_of_week, start_time, end_time, is_active, schedule_type, effective_from, effective_until, created_at, updated_at, specific_date)
        SELECT
            @new_provider_start + (provider_id - base_provider_id),
            day_of_week,
            start_time,
            end_time,
            is_active,
            schedule_type,
            effective_from,
            effective_until,
            DATE_ADD(created_at, INTERVAL FLOOR(RAND() * 10) DAY),
            DATE_ADD(updated_at, INTERVAL FLOOR(RAND() * 10) DAY),
            IF(specific_date IS NOT NULL, DATE_ADD(specific_date, INTERVAL FLOOR(RAND() * 30) DAY), NULL)
        FROM recurring_schedules
        WHERE provider_id BETWEEN base_provider_id AND max_provider_id;
        
        -- Clone provider availability
        INSERT INTO provider_availability (provider_id, availability_date, start_time, end_time, is_available, schedule_type, created_at, is_recurring, weekdays, max_appointments, service_id)
        SELECT
            @new_provider_start + (provider_id - base_provider_id),
            DATE_ADD(availability_date, INTERVAL (7 + FLOOR(RAND() * 14)) DAY),
            start_time,
            end_time,
            is_available,
            schedule_type,
            DATE_ADD(created_at, INTERVAL FLOOR(RAND() * 10) DAY),
            is_recurring,
            weekdays,
            max_appointments,
            service_id
        FROM provider_availability
        WHERE provider_id BETWEEN base_provider_id AND max_provider_id;
        
        -- Clone appointments
        INSERT INTO appointments (patient_id, provider_id, service_id, appointment_date, start_time, end_time, status, type, notes, reason, created_at, updated_at)
        SELECT
            @new_patient_start + (FLOOR(RAND() * ((@new_patient_end - @new_patient_start) + 1))),
            @new_provider_start + (FLOOR(RAND() * ((@new_provider_end - @new_provider_start) + 1))),
            service_id,
            DATE_ADD(appointment_date, INTERVAL (14 + FLOOR(RAND() * 30)) DAY),
            start_time,
            end_time,
            CASE 
                WHEN FLOOR(RAND() * 10) < 7 THEN 'confirmed'
                WHEN FLOOR(RAND() * 10) < 9 THEN 'completed'
                ELSE 'cancelled'
            END,
            type,
            CASE 
                WHEN notes IS NOT NULL THEN CONCAT(notes, ' (Clone ', clone_counter, ')')
                ELSE NULL
            END,
            reason,
            DATE_ADD(created_at, INTERVAL FLOOR(RAND() * 10) DAY),
            DATE_ADD(updated_at, INTERVAL FLOOR(RAND() * 10) DAY)
        FROM appointments
        LIMIT 100; -- Limit to prevent too many appointments
        
        -- Store the range of new appointment IDs
        SET @new_appt_start = (SELECT MAX(appointment_id) - 99 FROM appointments);
        SET @new_appt_end = (SELECT MAX(appointment_id) FROM appointments);
        
        -- Clone appointment ratings
        INSERT INTO appointment_ratings (appointment_id, patient_id, provider_id, rating, comment, created_at)
        SELECT
            @new_appt_start + FLOOR(RAND() * 100),
            @new_patient_start + (FLOOR(RAND() * ((@new_patient_end - @new_patient_start) + 1))),
            @new_provider_start + (FLOOR(RAND() * ((@new_provider_end - @new_provider_start) + 1))),
            1 + FLOOR(RAND() * 5), -- Random rating 1-5
            CONCAT('Clone feedback #', clone_counter, ': ', 
                   CASE FLOOR(RAND() * 4)
                       WHEN 0 THEN 'Great service!'
                       WHEN 1 THEN 'Doctor was very professional.'
                       WHEN 2 THEN 'Appointment went well.'
                       ELSE 'Satisfactory experience.'
                   END),
            DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY)
        FROM appointment_ratings
        LIMIT 50; -- Limit to prevent too many ratings
        
        -- Clone notification preferences
        INSERT INTO notification_preferences (user_id, email_notifications, sms_notifications, appointment_reminders, system_updates, reminder_time, created_at, updated_at)
        SELECT
            CASE
                WHEN user_id BETWEEN base_patient_id AND max_patient_id 
                    THEN @new_patient_start + (user_id - base_patient_id)
                WHEN user_id BETWEEN base_provider_id AND max_provider_id 
                    THEN @new_provider_start + (user_id - base_provider_id)
                ELSE user_id -- shouldn't happen
            END,
            email_notifications,
            sms_notifications,
            appointment_reminders,
            system_updates,
            reminder_time,
            DATE_ADD(created_at, INTERVAL FLOOR(RAND() * 10) DAY),
            DATE_ADD(updated_at, INTERVAL FLOOR(RAND() * 10) DAY)
        FROM notification_preferences
        WHERE user_id BETWEEN base_patient_id AND max_patient_id
           OR user_id BETWEEN base_provider_id AND max_provider_id;
        
        -- Clone notifications with unique messages
        INSERT INTO notifications (user_id, appointment_id, subject, message, type, status, created_at, is_system, is_read, audience)
        SELECT
            CASE
                WHEN user_id BETWEEN base_patient_id AND max_patient_id 
                    THEN @new_patient_start + (user_id - base_patient_id)
                WHEN user_id BETWEEN base_provider_id AND max_provider_id 
                    THEN @new_provider_start + (user_id - base_provider_id)
                ELSE user_id -- shouldn't happen
            END,
            NULL, -- Not linking to specific appointments for simplicity
            subject,
            CONCAT(message, ' (Clone ', clone_counter, ' - ', FLOOR(RAND() * 1000), ')'),
            type,
            status,
            DATE_ADD(created_at, INTERVAL FLOOR(RAND() * 10) DAY),
            is_system,
            is_read,
            audience
        FROM notifications
        WHERE user_id BETWEEN base_patient_id AND max_patient_id
           OR user_id BETWEEN base_provider_id AND max_provider_id
        LIMIT 100; -- Limit to prevent too many notifications
        
        -- Clone activity logs
        INSERT INTO activity_log (user_id, description, category, created_at, ip_address, details, related_id, related_type)
        SELECT
            CASE
                WHEN user_id BETWEEN base_patient_id AND max_patient_id 
                    THEN @new_patient_start + (user_id - base_patient_id)
                WHEN user_id BETWEEN base_provider_id AND max_provider_id 
                    THEN @new_provider_start + (user_id - base_provider_id)
                ELSE user_id -- shouldn't happen
            END,
            description,
            category,
            DATE_ADD(created_at, INTERVAL FLOOR(RAND() * 10) DAY),
            CONCAT('192.168.', FLOOR(RAND() * 255), '.', FLOOR(RAND() * 255)),
            details,
            NULL, -- Not linking to specific entities for simplicity
            related_type
        FROM activity_log
        LIMIT 100; -- Limit to prevent too many logs
        
        -- Clone appointment process logs
        INSERT INTO appointment_process_logs (timestamp, user_id, user_role, ip_address, user_agent, action, entity, entity_id, additional_data)
        SELECT
            DATE_ADD(timestamp, INTERVAL FLOOR(RAND() * 10) DAY),
            CASE
                WHEN user_id BETWEEN base_patient_id AND max_patient_id 
                    THEN @new_patient_start + (user_id - base_patient_id)
                WHEN user_id BETWEEN base_provider_id AND max_provider_id 
                    THEN @new_provider_start + (user_id - base_provider_id)
                ELSE user_id -- shouldn't happen
            END,
            user_role,
            CONCAT('192.168.', FLOOR(RAND() * 255), '.', FLOOR(RAND() * 255)),
            user_agent,
            action,
            entity,
            null,
            JSON_OBJECT(
                'message', CONCAT('Clone process log ', clone_counter),
                'details', CONCAT('Generated during data multiplication process, clone #', clone_counter)
            )
        FROM appointment_process_logs
        LIMIT 100; -- Limit to prevent too many logs
        
        -- Re-enable foreign key checks
        SET FOREIGN_KEY_CHECKS = 1;
        
        -- Increment clone counter
        SET clone_counter = clone_counter + 1;
        
        -- Output progress
        SELECT CONCAT('Completed clone iteration ', clone_counter, ' of 14') AS Progress;
        
    END WHILE;
    
    -- Final output
    SELECT 'Data multiplication complete. Database now contains 15x the original data.' AS Result;
    
END //

DELIMITER ;

-- How to use this script:
-- 1. First run your MassDBfill.sql script to establish the baseline data (1x)
-- 2. Then run this procedure to multiply it 14 more times (total 15x)
-- 
-- Execute with:
-- CALL MultiplyData15x();
-- 
-- Note: This procedure may take some time to complete depending on your database size and server performance.
-- You may want to adjust the LIMIT clauses if you want more or fewer cloned records in certain tables.