-- SQL to ensure notifications table has the required columns for system notifications
ALTER TABLE notifications ADD COLUMN IF NOT EXISTS is_system TINYINT(1) DEFAULT 0;
ALTER TABLE notifications ADD COLUMN IF NOT EXISTS audience VARCHAR(50) DEFAULT NULL;
-- Add indexes to improve performance
CREATE INDEX IF NOT EXISTS idx_notifications_is_system ON notifications(is_system);
CREATE INDEX IF NOT EXISTS idx_notifications_audience ON notifications(audience);