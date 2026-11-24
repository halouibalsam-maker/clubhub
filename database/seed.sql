-- database/seed.sql : données de test (dates calculées à partir d'aujourd'hui).
-- Les 3 comptes reprennent les hachages de l'ancien users.json (mêmes mots de passe qu'avant).
INSERT INTO users (id, name, email, password_hash, role) VALUES
(1, 'Amine Ben Salah', 'amine@campus.example',    '$2y$12$3zekK2w16KedvhiZ/IGbiu4Q180Pi.zIinsHfcgbLfYw/wb0hQ4Nq', 'student'),
(2, 'Sarra Trabelsi',  'sarra@campus.example',    '$2y$12$Qf3BQ4hcRdlhWEizD9yfhewkf1QDEnxJSUsCIVK0VzIqRmPBMwSgy', 'student'),
(3, 'Club Robotique',  'robotique@campus.example','$2y$12$XR4kwzIiOaevwRAO/KhdiO2aHYm1zz5.Ay79AtN9Qi1VYan60cQ7W', 'organizer');

INSERT INTO events (id, club, title, description, place, starts_at, ends_at, capacity, program, bring) VALUES
(1, 'Club Robotique', 'Atelier Arduino pour débutants',
 'Vous n''avez jamais touché un Arduino ? Cet atelier est fait pour vous. Les cartes et les composants sont fournis par le club.',
 'Salle B204', TIMESTAMP(CURDATE() + INTERVAL 3 DAY, '14:00:00'), TIMESTAMP(CURDATE() + INTERVAL 3 DAY, '17:00:00'), 20,
 'Découvrir la carte Arduino Uno\nFaire clignoter une LED\nLire un capteur de température\nMini projet : un thermomètre à LED',
 'Un ordinateur portable\nUn câble USB (si vous en avez un)'),
(2, 'Club Photo', 'Sortie photo en ville',
 'Une matinée pour photographier la ville : lumière du matin, architecture et scènes de rue. Tous niveaux.',
 'Entrée principale', TIMESTAMP(CURDATE() + INTERVAL 6 DAY, '09:00:00'), TIMESTAMP(CURDATE() + INTERVAL 6 DAY, '12:00:00'), 15,
 'Rendez-vous et conseils de cadrage\nParcours photo en petits groupes\nPause café et partage des premières images',
 'Un appareil photo ou un smartphone\nDes chaussures confortables'),
(3, 'Club IA', 'Conférence : l''IA dans l''industrie',
 'Des ingénieurs présentent comment l''IA est utilisée dans l''industrie, suivi d''un temps de questions.',
 'Amphithéâtre A', TIMESTAMP(CURDATE() + INTERVAL 10 DAY, '10:00:00'), TIMESTAMP(CURDATE() + INTERVAL 10 DAY, '12:00:00'), 120,
 'Introduction par le Club IA\nTrois retours d''expérience d''entreprises\nTable ronde et questions de la salle',
 'De quoi prendre des notes\nVotre curiosité'),
(4, 'Club Échecs', 'Tournoi blitz inter promo',
 'Un tournoi de parties rapides (blitz) entre les promotions. Cinq minutes par partie. Matériel fourni.',
 'Foyer des étudiants', TIMESTAMP(CURDATE() + INTERVAL 13 DAY, '15:00:00'), TIMESTAMP(CURDATE() + INTERVAL 13 DAY, '18:00:00'), 2,
 'Accueil et tirage au sort\nRondes qualificatives\nPhase finale et remise des prix',
 'Votre carte étudiante\nVotre bonne humeur'),
(5, 'Club Entrepreneuriat', 'Hackathon impact social',
 'Une journée pour imaginer, en équipe, un projet qui répond à un problème social concret.',
 'Salle C101', TIMESTAMP(CURDATE() + INTERVAL 20 DAY, '09:00:00'), TIMESTAMP(CURDATE() + INTERVAL 20 DAY, '18:00:00'), 40,
 'Formation des équipes\nTravail sur le projet avec les mentors\nPrésentations devant le jury',
 'Un ordinateur portable\nDes idées'),
(6, 'Club Photo', 'Exposition de rentrée (passée)',
 'Exposition des photos de la rentrée. Cet événement est déjà passé.',
 'Hall principal', TIMESTAMP(CURDATE() - INTERVAL 7 DAY, '10:00:00'), TIMESTAMP(CURDATE() - INTERVAL 7 DAY, '16:00:00'), 50,
 'Visite libre\nRemise des prix du public',
 'Rien de particulier');

INSERT INTO registrations (user_id, event_id, motivation) VALUES
(1, 1, 'Je veux apprendre les bases.'),
(2, 1, ''),
(1, 4, 'Je joue souvent en ligne.'),
(2, 4, ''),   -- l'événement 4 n'a que 2 places : il est complet
(1, 6, 'J''y étais !');   -- événement passé
