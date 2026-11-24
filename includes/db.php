<?php
/* =====================================================================
   includes/db.php : connexion à MySQL avec PDO
   ---------------------------------------------------------------------
   db() renvoie TOUJOURS le même objet PDO : la connexion n'est ouverte
   qu'une fois par requête (variable "static"), et seulement quand on en a besoin.
   Les identifiants sont dans config.php (hors de Git).
   ===================================================================== */

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $config = require __DIR__ . '/../config.php';

        // charset=utf8mb4 : sans lui, les accents sont cassés (Ã©)
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'],
            $config['name']
        );

        $pdo = new PDO($dsn, $config['user'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // erreur SQL = exception (pas d'échec silencieux)
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // lignes = tableaux associatifs
            PDO::ATTR_EMULATE_PREPARES => false,                // VRAIES requêtes préparées (envoyées séparément à MySQL)
        ]);
    }

    return $pdo;
}
