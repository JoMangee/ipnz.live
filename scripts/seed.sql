-- Minimal seed data for local/dev testing
INSERT INTO ipnz_members (uuid, referral_code, name, email, phone, join_type, additional_request, avatar_url, has_custom_avatar, status, email_verified, privacy_consent)
VALUES (UUID(), 'TESTMEM', 'Test Member', 'test@example.com', '028-25578835', 'early_access', 'Fixture', 'https://models.readyplayer.me/64bfa15f0e72c63d7c3934a6.png', 0, 'active', 1, 1);
