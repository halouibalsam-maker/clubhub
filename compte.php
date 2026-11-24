<?php
/* =====================================================================
   compte.php : page protégée : infos du compte + "Mes inscriptions" (à venir / passés)
   ===================================================================== */
require __DIR__ . '/includes/bootstrap.php';

$user = requireLogin();

$roles = ['student' => 'Étudiant', 'organizer' => 'Organisateur'];
$roleLabel = $roles[$user['role']] ?? $user['role'];

// Un seul JOIN en SQL, puis on sépare en PHP
$upcoming = [];
$past = [];
foreach (registrationsOfUser((int) $user['id']) as $registration) {
    if ($registration['is_upcoming']) {
        $upcoming[] = $registration;
    } else {
        $past[] = $registration;
    }
}

$pageTitle = 'Mon compte · ClubHub';
$currentPage = 'compte';
require __DIR__ . '/includes/header.php';
?>

  <main>
    <h1>Mon compte</h1>

    <section class="event-detail">
      <ul class="facts">
        <li><strong>Nom :</strong> <?= e($user['name']) ?></li>
        <li><strong>Email :</strong> <?= e($user['email']) ?></li>
        <li><strong>Rôle :</strong> <?= e($roleLabel) ?></li>
        <li><strong>Compte créé le :</strong> <?= e(formatDate($user['created_at'], true)) ?></li>
      </ul>
    </section>

    <section>
      <h2>À venir</h2>
      <?php if (count($upcoming) === 0) : ?>
        <p class="empty-message">Vous n'êtes inscrit à aucun événement à venir. <a href="evenements.php">Voir les événements</a></p>
      <?php else : ?>
        <ul class="my-registrations">
          <?php foreach ($upcoming as $event) : ?>
            <li class="my-registration">
              <div>
                <a href="evenement.php?id=<?= e($event['id']) ?>"><?= e($event['title']) ?></a>
                <p class="meta"><?= e(formatDate($event['starts_at'])) ?>, <?= e(formatTimeRange($event['starts_at'], $event['ends_at'])) ?> · <?= e($event['place']) ?></p>
              </div>
              <form action="annuler.php" method="post">
                <input type="hidden" name="event_id" value="<?= e($event['id']) ?>">
                <button type="submit">Annuler</button>
              </form>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <section>
      <h2>Passés</h2>
      <?php if (count($past) === 0) : ?>
        <p class="empty-message">Aucun événement passé.</p>
      <?php else : ?>
        <ul class="my-registrations">
          <?php foreach ($past as $event) : ?>
            <li class="my-registration">
              <div>
                <a href="evenement.php?id=<?= e($event['id']) ?>"><?= e($event['title']) ?></a>
                <p class="meta"><?= e(formatDate($event['starts_at'])) ?>, <?= e(formatTimeRange($event['starts_at'], $event['ends_at'])) ?> · <?= e($event['place']) ?></p>
              </div>
              <!-- Pas de bouton Annuler : et annuler.php refuse aussi côté serveur -->
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
