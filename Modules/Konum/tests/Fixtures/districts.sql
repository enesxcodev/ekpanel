-- Minimal deterministic source fixture.
INSERT INTO `districts` (`id`, `city_id`, `town_id`, `name`, `zip_code`, `neighbourhoods`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Merkez', 1720, '[{"id":1,"name":"Mahalle"}]', 1, '2024-04-01 00:00:00', NULL, NULL);
