<?php
/* =====================================================================
   includes/header.php : du <!DOCTYPE html> jusqu'à la fin du <header>
   ---------------------------------------------------------------------
   Chaque page fait, AVANT de l'inclure :
       $pageTitle   = 'Événements · ClubHub';   // titre de l'onglet
       $currentPage = 'evenements';              // 'index', 'evenements' ou 'evenement'
       require __DIR__ . '/includes/header.php';
   Les variables de la page sont visibles ici car require "copie-colle" le fichier.
   ===================================================================== */
$pageTitle = $pageTitle ?? 'ClubHub';       // valeur par défaut si la page l'oublie
$currentPage = $currentPage ?? '';
$headerUser = currentUser();    // utilisateur connecté, ou null
$flash = takeFlash();           // message flash (lu ET supprimé : il ne s'affiche qu'une fois)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="stylesheet" href="css/style.css">
  <!-- Défi "Pas de flash" : appliqué AVANT l'affichage de la page (voir README) -->
  <script>
    try {
      const themeMemorise = localStorage.getItem('clubhub-theme');
      if (themeMemorise === 'dark') {
        document.documentElement.dataset.theme = 'dark';
      }
    } catch (erreur) {
      /* localStorage bloqué : on ignore */
    }
  </script>
</head>
<body>
  <header class="site-header">
    <a class="logo" href="index.php">ClubHub</a>
    <nav>
      <ul>
        <!-- ariaCurrent() ajoute aria-current="page" sur le lien de la page actuelle -->
        <li><a href="index.php"<?= ariaCurrent('index', $currentPage) ?>>Accueil</a></li>
        <li><a href="evenements.php"<?= ariaCurrent('evenements', $currentPage) ?>>Événements</a></li>
        <li><a href="evenement.php?id=1"<?= ariaCurrent('evenement', $currentPage) ?>>S'inscrire</a></li>
        <?php if ($headerUser !== null) : ?>
          <!-- Connecté : "Mon compte" + bouton de déconnexion.
               La déconnexion MODIFIE des données (la session) : elle se fait en POST, pas avec un simple lien. -->
          <li><a href="compte.php"<?= ariaCurrent('compte', $currentPage) ?>>Mon compte</a></li>
          <li>
            <form class="logout-form" action="deconnexion.php" method="post">
              <button type="submit" class="link-button">Déconnexion</button>
            </form>
          </li>
        <?php else : ?>
          <li><a href="connexion.php"<?= ariaCurrent('connexion', $currentPage) ?>>Connexion</a></li>
          <li><a href="creer-compte.php"<?= ariaCurrent('creer-compte', $currentPage) ?>>Créer un compte</a></li>
        <?php endif; ?>
      </ul>
    </nav>
    <button type="button" class="theme-toggle">Mode sombre</button>
  </header>

  <?php if ($flash !== null) : ?>
    <!-- Message flash : le texte passe par e() comme toute donnée affichée -->
    <p class="flash flash-<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></p>
  <?php endif; ?>
