<?php
/* index.php : page d'accueil. Les 2 prochains événements viennent de MySQL. */
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'ClubHub : les événements des clubs étudiants';
$currentPage = 'index';
$footerImage = 'evenement.jpg';

$upcoming = getEvents(2);   // LIMIT 2 fait en SQL

require __DIR__ . '/includes/header.php';
?>

  <main>
    <section class="hero">
      <h1>Tous les événements des clubs, au même endroit</h1>
      <p>Ateliers, conférences, sorties, tournois : découvrez ce que font les clubs étudiants et réservez votre place en quelques secondes.</p>
      <a class="button" href="evenements.php">Voir les événements</a>
    </section>

    <section>
      <h2>Prochains événements</h2>
      <div class="event-list">
        <?php foreach ($upcoming as $event) : ?>
          <article class="event-card">
            <p class="club"><?= e($event['club']) ?></p>
            <h3><?= e($event['title']) ?></h3>
            <p class="meta"><time datetime="<?= e(isoDate($event['starts_at'])) ?>"><?= e(formatDate($event['starts_at'])) ?></time>, <?= e(formatTimeRange($event['starts_at'], $event['ends_at'])) ?> · <?= e($event['place']) ?></p>
            <p class="places"><strong><?= e(placesLabel($event['places_left'])) ?></strong></p>
            <a href="evenement.php?id=<?= e($event['id']) ?>">Voir le détail</a>
          </article>
        <?php endforeach; ?>
      </div>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
