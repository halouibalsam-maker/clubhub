<?php
/* =====================================================================
   includes/bootstrap.php : à inclure EN PREMIER par toutes les pages
   Il démarre la session (une seule fois, ici) puis charge les fonctions.
   IMPORTANT : session_start() et header() doivent être appelés AVANT
   d'écrire le moindre HTML (sinon « headers already sent »).
   ===================================================================== */

// ---- Cookie de session durci (doit précéder session_start) ----------
//  httponly : le JavaScript ne peut pas lire le cookie (limite le vol par XSS)
//  samesite : 'Lax' limite les attaques CSRF
//  secure   : seulement en HTTPS (en local, http://localhost, il serait refusé)
$isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => $isHttps,
]);
// use_strict_mode : PHP refuse un identifiant de session inventé par un attaquant
ini_set('session.use_strict_mode', '1');

session_start();

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/db.php';              // TP6 : connexion MySQL
require_once __DIR__ . '/events.php';          // TP6 : événements en SQL
require_once __DIR__ . '/users.php';
require_once __DIR__ . '/registrations.php';   // TP6 : inscriptions en SQL
require_once __DIR__ . '/auth.php';
