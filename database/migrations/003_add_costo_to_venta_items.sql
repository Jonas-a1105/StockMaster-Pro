-- Migration: Add costo_unitario_usd to venta_items
-- Created: 2026
-- Description: Ensures we save the product cost at the time of sale for accurate historical profit reports.

ALTER TABLE venta_items ADD COLUMN costo_unitario_usd DECIMAL(10,2) DEFAULT 0.00;
