<?php
/* =====================================================================
   evenement.php : détail d'un événement + inscription
   Adresse : evenement.php?id=3
     A. lire l'id dans l'URL et trouver l'événement en base (sinon erreur 404) ;
     B. traiter l'inscription (POST) : connexion exigée, validation, puis registerUser() ;
     C. afficher la page.
   ===================================================================== */
require __DIR__ . '/includes/bootstrap.php';

$currentPage = 'evenement';

/* A. Lire l'id et trouver l'événement */
$idText = inputString($_GET, 'id');
$event = null;
if ($idText !== '' && ctype_digit($idText)) {
    $event = findEvent((int) $idText);   // requête préparée dans includes/events.php
}

if ($event === null) {
    http_response_code(404);
    $pageTitle = 'Événement introuvable · ClubHub';
    require __DIR__ . '/includes/header.php';
    ?>
  <main>
    <section class="event-detail">
      <h1>Événement introuvable</h1>
      <p>Désolé, l'événement demandé n'existe pas ou l'adresse est incorrecte.</p>
      <p><a class="button" href="evenements.php">Voir tous les événements</a></p>
    </section>
  </main>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

/* B. Traitement de l'inscription */
const MOTIVATION_MAX = 300;

$user = currentUser();
$eventId = $event['id'];
$isRegistered = $user !== null && isRegistered((int) $user['id'], $eventId);
$isFull = $event['places_left'] <= 0;
$isPast = $event['is_past'];

$values = ['motivation' => '', 'rules' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cacher le formulaire n'est PAS une protection : seul le serveur décide.
    $user = requireLogin();

    $values['motivation'] = inputString($_POST, 'motivation');
    $values['rules'] = inputString($_POST, 'rules');

    if ($values['rules'] !== '1') {
        $errors['rules'] = 'Vous devez accepter le règlement du club.';
    }
    if (mb_strlen($values['motivation'], 'UTF-8') > MOTIVATION_MAX) {
        $errors['motivation'] = 'La motivation ne doit pas dépasser ' . MOTIVATION_MAX . ' caractères.';
    }

    if (count($errors) === 0) {
        // Le serveur vérifie lui-même : déjà inscrit ? complet ? passé ? (même si le formulaire était caché)
        $result = registerUser((int) $user['id'], $eventId, $values['motivation']);

        switch ($result) {
            case 'ok':
                flash('success', 'Inscription confirmée.');
                break;
            case 'already':
                flash('info', 'Vous êtes déjà inscrit à cet événement.');
                break;
            case 'full':
                flash('error', "Désolé, cet événement est complet : l'inscription est refusée.");
                break;
            default:
                flash('error', "Les inscriptions à cet événement sont fermées.");
        }
        redirect('evenement.php?id=' . $eventId);   // Post/Redirect/Get
    }
}

/* C. Affichage */
$pageTitle = $event['title'] . ' · ClubHub';
require __DIR__ . '/includes/header.php';
?>

  <main class="event-page">
    <article class="event-detail">
      <p class="club"><?= e($event['club']) ?></p>
      <h1><?= e($event['title']) ?></h1>
      <ul class="facts">
        <li><strong>Date :</strong> <time datetime="<?= e(isoDate($event['starts_at'])) ?>"><?= e(formatDate($event['starts_at'])) ?></time>, <?= e(formatTimeRange($event['starts_at'], $event['ends_at'])) ?></li>
        <li><strong>Lieu :</strong> <?= e($event['place']) ?></li>
        <li><strong>Places :</strong> <?= e(placesLabel($event['places_left'])) ?> (sur <?= e($event['capacity']) ?>)</li>
      </ul>

      <p><?= e($event['description']) ?></p>

      <h2>Au programme</h2>
      <ul>
        <?php foreach (lines($event['program']) as $item) : ?>
          <li><?= e($item) ?></li>
        <?php endforeach; ?>
      </ul>

      <h2>À apporter</h2>
      <ul>
        <?php foreach (lines($event['bring']) as $item) : ?>
          <li><?= e($item) ?></li>
        <?php endforeach; ?>
      </ul>
    </article>

    <section class="registration">
      <h2>S'inscrire</h2>

      <?php if ($isRegistered) : ?>
        <p class="form-success" role="status">Vous êtes inscrit à cet événement.</p>
        <p><a href="compte.php">Voir mes inscriptions</a></p>

      <?php elseif ($isPast) : ?>
        <p>Cet événement est terminé : les inscriptions sont closes.</p>

      <?php elseif ($isFull) : ?>
        <!-- Complet : plus de formulaire (et registerUser() refuserait de toute façon) -->
        <p class="form-error" role="status"><strong>Complet</strong> : il n'y a plus de place pour cet événement.</p>

      <?php elseif ($user === null) : ?>
        <p>Vous devez être connecté pour vous inscrire à cet événement.</p>
        <p><a class="button" href="connexion.php?next=<?= e(urlencode('evenement.php?id=' . $eventId)) ?>">Se connecter</a></p>
        <p>Pas encore de compte ? <a href="creer-compte.php">Créer un compte</a></p>

      <?php else : ?>
        <p>Inscription au nom de <strong><?= e($user['name']) ?></strong> (<?= e($user['email']) ?>).</p>
        <form id="registration-form" action="evenement.php?id=<?= e($eventId) ?>" method="post" novalidate>
          <p>
            <label for="motivation">Motivation (facultatif)</label>
            <textarea id="motivation" name="motivation" rows="4" maxlength="300" aria-describedby="motivation-count motivation-error"<?= isset($errors['motivation']) ? ' class="is-invalid" aria-invalid="true"' : '' ?>><?= e($values['motivation']) ?></textarea>
            <small class="counter" id="motivation-count"><?= e(mb_strlen($values['motivation'], 'UTF-8')) ?> / 300</small>
            <small class="field-error" id="motivation-error"><?= e($errors['motivation'] ?? '') ?></small>
          </p>
          <p>
            <label><input type="checkbox" id="rules" name="rules" value="1" required aria-describedby="rules-error"<?= $values['rules'] === '1' ? ' checked' : '' ?><?= isset($errors['rules']) ? ' class="is-invalid" aria-invalid="true"' : '' ?>> J'accepte le règlement du club</label>
            <small class="field-error" id="rules-error"><?= e($errors['rules'] ?? '') ?></small>
          </p>
          <p>
            <button type="submit">S'inscrire</button>
          </p>
        </form>
      <?php endif; ?>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
