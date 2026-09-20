USE klaxon;
-- Fictional employee identities and reserved example.test email addresses; no personal contact data.
-- Initial passwords are unique and strong; demonstration credentials are delivered separately.
INSERT IGNORE INTO agencies (name) VALUES ('Paris'), ('Lyon'), ('Marseille'), ('Toulouse'), ('Nice'), ('Nantes'), ('Strasbourg'), ('Montpellier'), ('Bordeaux'), ('Lille'), ('Rennes'), ('Reims');
INSERT IGNORE INTO users (last_name,first_name,phone,email,password_hash,role) VALUES
('Employe01','Demo01','0000000000','employe01@example.test','$2b$12$99AFcfwuMWq0cf2HNy/V8ufi7/XFPqs76S1AGR.usRG00vEt1k0BS','employee'),
('Employe02','Demo02','0000000000','employe02@example.test','$2b$12$Au8hXRWteruKXiSlM9o2wuJsvMpoQsb2QEHxET44.A4dwufTcgn8i','employee'),
('Employe03','Demo03','0000000000','employe03@example.test','$2b$12$5X.7U9mpKZArcVaaKGCM/.LjktPv8w1hY.ISnrvAb6Q6Du6Q2xVv2','employee'),
('Employe04','Demo04','0000000000','employe04@example.test','$2b$12$EdJH3wKGjipNRPapelI2Q.bSynS6Z48ypdPaXRwWsr0fF5ItH3O32','employee'),
('Employe05','Demo05','0000000000','employe05@example.test','$2b$12$M/khaWlsLBo0iOiuNXYG4O0Jth7C1IR5XTFkbhKfHu95/MncsLSYm','employee'),
('Employe06','Demo06','0000000000','employe06@example.test','$2b$12$GZBf38Vu7dtWI9sX/A73EOZNhBPNiRc9XlOUXfR4BtzvkJeyc7O/O','employee'),
('Employe07','Demo07','0000000000','employe07@example.test','$2b$12$kIRUdetzNAD7WAMtOYsyQO10wuSvQlHYovKNQyoOck0JZJQklReQC','employee'),
('Employe08','Demo08','0000000000','employe08@example.test','$2b$12$4IeqmBq5Iq9VJYkumy.k6.wsjKep6PUxMwAAWDS0IEyfTrtB9984C','employee'),
('Employe09','Demo09','0000000000','employe09@example.test','$2b$12$IJiGEcJ3QYtCBZJGHC8tt.GK741kZhZALcAEpqKUt6GD5LTKssEpy','employee'),
('Employe10','Demo10','0000000000','employe10@example.test','$2b$12$pbscNp7/fJRxmcrKgYKvPODSB6gGLOSpMLvKjujwhYkM5xM0IIlie','employee'),
('Employe11','Demo11','0000000000','employe11@example.test','$2b$12$NPAb1agVCfvPQxCnOuBtLOYafupr63TQyu/zh4Tt2TuTBvcAC45cC','employee'),
('Employe12','Demo12','0000000000','employe12@example.test','$2b$12$mRH/0BlMq3MTvoFWw3vOFeRYgwegMUdQcOr/UCApG/T5bZ.6.ue0e','employee'),
('Employe13','Demo13','0000000000','employe13@example.test','$2b$12$4ULpZrWfSLjQKRl3AdJD/O5wDpdiaYQtZ3DyBR7aNCevzJ7OvCRvu','employee'),
('Employe14','Demo14','0000000000','employe14@example.test','$2b$12$IzUGfbcq.1.SdMFitXLkmewYmpuQHNG5T4voRIdu6c5MPXvZVbGJW','employee'),
('Employe15','Demo15','0000000000','employe15@example.test','$2b$12$nOsTQf7JGBWPDoC/fyi.vOrac6SjQGNrUEpZHNZ4F7YfBdoZtPW86','employee'),
('Employe16','Demo16','0000000000','employe16@example.test','$2b$12$1w/0yNGiglahRTWQh3OWtelXM4A1bIvfeGG2lbCf6lAw9XUwObWMu','employee'),
('Employe17','Demo17','0000000000','employe17@example.test','$2b$12$6mIT4uMD/wMDBWZweKcEjuvp/rhmWUlH47.ECHD5zRKjS7cL94KHq','employee'),
('Employe18','Demo18','0000000000','employe18@example.test','$2b$12$fpq0B2BJcXhtax5NlYdZgeFTHoPxi14i4t/K6BKZ67ApsA6TdJqCu','employee'),
('Employe19','Demo19','0000000000','employe19@example.test','$2b$12$/gMVdIOvGIGYYS4GzFL.ieYOsqBj6l4z32T7R6F.ae.wCdYBPduRS','employee'),
('Employe20','Demo20','0000000000','employe20@example.test','$2b$12$mnMAhqT6Gosekl7Ejner5eIGyt9zaxjRqPl8eMGginXnkkHeEfxs6','employee'),
('Admin','Demo','0000000000','admin@example.test','$2b$12$DZX/A4M8338g5PgDaBwTM.1qFMT1VNk1O/UDfm3pn22LqpkcvdWOC','admin');
-- Example future trips. Import into a fresh database to avoid duplicate trips.
INSERT INTO trips (departure_agency_id,arrival_agency_id,departure_at,arrival_at,total_seats,available_seats,author_id) VALUES
((SELECT id FROM agencies WHERE name='Paris'),(SELECT id FROM agencies WHERE name='Lyon'),DATE_ADD(NOW(), INTERVAL 2 DAY),DATE_ADD(NOW(), INTERVAL 2 DAY)+INTERVAL 2 HOUR,4,3,(SELECT id FROM users WHERE email='employe01@example.test')),
((SELECT id FROM agencies WHERE name='Nantes'),(SELECT id FROM agencies WHERE name='Rennes'),DATE_ADD(NOW(), INTERVAL 4 DAY),DATE_ADD(NOW(), INTERVAL 4 DAY)+INTERVAL 1 HOUR,3,2,(SELECT id FROM users WHERE email='employe02@example.test'));
