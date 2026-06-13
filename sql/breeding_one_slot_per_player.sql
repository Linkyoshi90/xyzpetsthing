-- Enforce one active breeding row per player on an existing database.
-- If the duplicate check returns rows, resolve those sessions before adding the unique key.

SELECT owner_user_id, COUNT(*) AS active_breeding_sessions
  FROM breeding
 GROUP BY owner_user_id
HAVING COUNT(*) > 1;

ALTER TABLE breeding
  DROP INDEX ix_breeding_owner,
  ADD UNIQUE KEY uq_breeding_owner (owner_user_id);
