-- Friendship request support for the 20260606 database dump.
-- Run once after importing the dump.
-- No foreign key constraints are added here.

ALTER TABLE user_friends
  ADD COLUMN status ENUM('pending','accepted') NOT NULL DEFAULT 'accepted' AFTER friend_id,
  ADD COLUMN requested_by_user_id BIGINT UNSIGNED NULL AFTER status,
  ADD COLUMN requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER requested_by_user_id,
  ADD COLUMN accepted_at TIMESTAMP NULL DEFAULT NULL AFTER requested_at,
  ADD INDEX ix_user_friends_requested_by (requested_by_user_id),
  ADD INDEX ix_user_friends_status_requested_by (status, requested_by_user_id);

UPDATE user_friends
   SET status = 'accepted',
       requested_by_user_id = COALESCE(requested_by_user_id, user_id),
       accepted_at = COALESCE(accepted_at, NOW())
 WHERE status = 'accepted';
