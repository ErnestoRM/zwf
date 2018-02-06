  -- Datos de pruebas para la aplicación


-- Login data
-- User: admin
-- Pass: admin


INSERT INTO zfx_user (name, email, password_hash, language, mobile) VALUES ('admin', 'admin@example.com', MD5('admin'), 'en', '123456789');
INSERT INTO zfx_group (name) VALUES ('admins');
INSERT INTO zfx_user_group (id_user, id_group) VALUES (1, 1);
INSERT INTO zfx_permission (code) VALUES ('admin-zone');
INSERT INTO zfx_group_permission (id_group, id_permission) VALUES (1,1);


