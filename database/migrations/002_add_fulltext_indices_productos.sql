-- Add Full-Text index to productos table for improved search performance
-- This is supported in MySQL/MariaDB (InnoDB/MyISAM)
ALTER TABLE productos ADD FULLTEXT INDEX idx_fulltext_search (nombre, categoria);
