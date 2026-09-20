USE klaxon;
-- Fictional employee sample data supplied in the assignment appendix (users.txt).
-- Initial passwords are unique and strong; demonstration credentials are delivered separately.
INSERT IGNORE INTO agencies (name) VALUES ('Paris'), ('Lyon'), ('Marseille'), ('Toulouse'), ('Nice'), ('Nantes'), ('Strasbourg'), ('Montpellier'), ('Bordeaux'), ('Lille'), ('Rennes'), ('Reims');
INSERT IGNORE INTO users (last_name,first_name,phone,email,password_hash,role) VALUES
('Martin','Alexandre','0612345678','alexandre.martin@email.fr','$2b$12$99AFcfwuMWq0cf2HNy/V8ufi7/XFPqs76S1AGR.usRG00vEt1k0BS','employee'),
('Dubois','Sophie','0698765432','sophie.dubois@email.fr','$2b$12$Au8hXRWteruKXiSlM9o2wuJsvMpoQsb2QEHxET44.A4dwufTcgn8i','employee'),
('Bernard','Julien','0622446688','julien.bernard@email.fr','$2b$12$5X.7U9mpKZArcVaaKGCM/.LjktPv8w1hY.ISnrvAb6Q6Du6Q2xVv2','employee'),
('Moreau','Camille','0611223344','camille.moreau@email.fr','$2b$12$EdJH3wKGjipNRPapelI2Q.bSynS6Z48ypdPaXRwWsr0fF5ItH3O32','employee'),
('Lefèvre','Lucie','0777889900','lucie.lefevre@email.fr','$2b$12$M/khaWlsLBo0iOiuNXYG4O0Jth7C1IR5XTFkbhKfHu95/MncsLSYm','employee'),
('Leroy','Thomas','0655443322','thomas.leroy@email.fr','$2b$12$GZBf38Vu7dtWI9sX/A73EOZNhBPNiRc9XlOUXfR4BtzvkJeyc7O/O','employee'),
('Roux','Chloé','0633221199','chloe.roux@email.fr','$2b$12$kIRUdetzNAD7WAMtOYsyQO10wuSvQlHYovKNQyoOck0JZJQklReQC','employee'),
('Petit','Maxime','0766778899','maxime.petit@email.fr','$2b$12$4IeqmBq5Iq9VJYkumy.k6.wsjKep6PUxMwAAWDS0IEyfTrtB9984C','employee'),
('Garnier','Laura','0688776655','laura.garnier@email.fr','$2b$12$IJiGEcJ3QYtCBZJGHC8tt.GK741kZhZALcAEpqKUt6GD5LTKssEpy','employee'),
('Dupuis','Antoine','0744556677','antoine.dupuis@email.fr','$2b$12$pbscNp7/fJRxmcrKgYKvPODSB6gGLOSpMLvKjujwhYkM5xM0IIlie','employee'),
('Lefebvre','Emma','0699887766','emma.lefebvre@email.fr','$2b$12$NPAb1agVCfvPQxCnOuBtLOYafupr63TQyu/zh4Tt2TuTBvcAC45cC','employee'),
('Fontaine','Louis','0655667788','louis.fontaine@email.fr','$2b$12$mRH/0BlMq3MTvoFWw3vOFeRYgwegMUdQcOr/UCApG/T5bZ.6.ue0e','employee'),
('Chevalier','Clara','0788990011','clara.chevalier@email.fr','$2b$12$4ULpZrWfSLjQKRl3AdJD/O5wDpdiaYQtZ3DyBR7aNCevzJ7OvCRvu','employee'),
('Robin','Nicolas','0644332211','nicolas.robin@email.fr','$2b$12$IzUGfbcq.1.SdMFitXLkmewYmpuQHNG5T4voRIdu6c5MPXvZVbGJW','employee'),
('Gauthier','Marine','0677889922','marine.gauthier@email.fr','$2b$12$nOsTQf7JGBWPDoC/fyi.vOrac6SjQGNrUEpZHNZ4F7YfBdoZtPW86','employee'),
('Fournier','Pierre','0722334455','pierre.fournier@email.fr','$2b$12$1w/0yNGiglahRTWQh3OWtelXM4A1bIvfeGG2lbCf6lAw9XUwObWMu','employee'),
('Girard','Sarah','0688665544','sarah.girard@email.fr','$2b$12$6mIT4uMD/wMDBWZweKcEjuvp/rhmWUlH47.ECHD5zRKjS7cL94KHq','employee'),
('Lambert','Hugo','0611223366','hugo.lambert@email.fr','$2b$12$fpq0B2BJcXhtax5NlYdZgeFTHoPxi14i4t/K6BKZ67ApsA6TdJqCu','employee'),
('Masson','Julie','0733445566','julie.masson@email.fr','$2b$12$/gMVdIOvGIGYYS4GzFL.ieYOsqBj6l4z32T7R6F.ae.wCdYBPduRS','employee'),
('Henry','Arthur','0666554433','arthur.henry@email.fr','$2b$12$mnMAhqT6Gosekl7Ejner5eIGyt9zaxjRqPl8eMGginXnkkHeEfxs6','employee'),
('Admin','Demo','0000000000','admin@example.test','$2b$12$DZX/A4M8338g5PgDaBwTM.1qFMT1VNk1O/UDfm3pn22LqpkcvdWOC','admin');
-- Example future trips. Import into a fresh database to avoid duplicate trips.
INSERT INTO trips (departure_agency_id,arrival_agency_id,departure_at,arrival_at,total_seats,available_seats,author_id) VALUES
((SELECT id FROM agencies WHERE name='Paris'),(SELECT id FROM agencies WHERE name='Lyon'),DATE_ADD(NOW(), INTERVAL 2 DAY),DATE_ADD(NOW(), INTERVAL 2 DAY)+INTERVAL 2 HOUR,4,3,(SELECT id FROM users WHERE email='alexandre.martin@email.fr')),
((SELECT id FROM agencies WHERE name='Nantes'),(SELECT id FROM agencies WHERE name='Rennes'),DATE_ADD(NOW(), INTERVAL 4 DAY),DATE_ADD(NOW(), INTERVAL 4 DAY)+INTERVAL 1 HOUR,3,2,(SELECT id FROM users WHERE email='sophie.dubois@email.fr'));
