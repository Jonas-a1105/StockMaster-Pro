-- Migration: 001_add_bio_to_usuarios.sql
-- Description: Adds a biography column to the users table for testing the migration system.

ALTER TABLE usuarios ADD COLUMN bio TEXT;
