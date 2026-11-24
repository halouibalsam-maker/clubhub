<?php
/* =====================================================================
   annuler.php : annule une inscription (POST uniquement), puis redirige
   ===================================================================== */
require __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('compte.php');
}

$user = requireLogin();   // on ne fait pas confiance à l'interface

$eventId = inputString($_POST, 'event_id');

// L'id de la personne vient de la SESSION (pas du formulaire) : voir cancelRegistration().
if (ctype_digit($eventId) && cancelRegistration((int) $user['id'], (int) $eventId)) {
    flash('info', 'Inscription annulée.');
} else {
    flash('error', "Aucune inscription à annuler pour cet événement (ou l'événement est déjà passé).");
}

redirect('compte.php');   // Post/Redirect/Get
