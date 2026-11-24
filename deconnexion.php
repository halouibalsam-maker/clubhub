<?php
/* =====================================================================
   deconnexion.php : déconnecte l'utilisateur (uniquement en POST)
   Une déconnexion modifie la session : elle doit être une action POST,
   déclenchée par le bouton du formulaire de l'en-tête (pas par un simple lien).
   ===================================================================== */
require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');   // ouverte directement dans le navigateur (GET) : on ne fait rien
}

logoutUser();
flash('info', 'Vous êtes déconnecté.');
redirect('index.php');
