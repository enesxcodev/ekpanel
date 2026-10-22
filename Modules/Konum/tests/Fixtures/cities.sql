-- Minimal deterministic source fixture.
INSERT INTO `cities` (`id`, `name`, `plate_code`, `towns`, `lat`, `lon`, `polygons`, `boundingbox`, `is_active`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Adana', '01', '[{"id":1,"name":"Aladağ"}]', '37', '35', 0x000000000103000000010000000400000000000000000000000000000000000000000000000000f03f0000000000000000000000000000f03f000000000000f03f00000000000000000000000000000000, '[0,1,0,1]', 1, '2024-04-01 00:00:00', NULL, NULL);
