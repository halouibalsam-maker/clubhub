<?php
/* evenements.php : liste des événements à venir.
   Le filtre fonctionne de 2 façons :
    - SANS JavaScript : le formulaire envoie un GET (?q=...&club=...&sort=...) et MySQL filtre (LIKE) ;
    - AVEC JavaScript : js/app.js filtre en direct, sans recharger la page. */
require __DIR__ . '/includes/bootstrap.php';

$pageTitle = 'Événements · ClubHub';
$currentPage = 'evenements';

$query = inputString($_GET, 'q');
$club = inputString($_GET, 'club');
$sort = inputString($_GET, 'sort');
if ($sort !== 'places') {
    $sort = 'date';
}

// Toutes les cartes sont écrites dans la page (pour que js/app.js puisse les réafficher),
// mais celles qui ne correspondent pas à la recherche reçoivent l'attribut "hidden".
$events = getEvents(null, '', '', $sort);               // tous les événements à venir
$matching = getEvents(null, $query, $club, $sort);      // ceux qui correspondent (filtre en SQL)
$matchingIds = array_column($matching, 'id');
$count = count($matching);

$clubs = getClubs($events);

require __DIR__ . '/includes/header.php';
?>

  <main>
    <h1>Tous les événements</h1>

    <form class="filters" role="search" action="evenements.php" method="get">
      <div class="filter-field">
        <label for="search">Rechercher</label>
        <input type="search" id="search" name="q" value="<?= e($query) ?>" placeholder="Arduino, photo, salle...">
      </div>
      <div class="filter-field">
        <label for="club-filter">Club</label>
        <select id="club-filter" name="club">
          <option value="">Tous les clubs</option>
          <?php foreach ($clubs as $clubName) : ?>
            <option value="<?= e($clubName) ?>"<?= $clubName === $club ? ' selected' : '' ?>><?= e($clubName) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="filter-field">
        <label for="sort">Trier par</label>
        <select id="sort" name="sort">
          <option value="date"<?= $sort === 'date' ? ' selected' : '' ?>>Date (la plus proche d'abord)</option>
          <option value="places"<?= $sort === 'places' ? ' selected' : '' ?>>Nombre de places (du plus grand au plus petit)</option>
        </select>
      </div>
      <div class="filter-field filter-check">
        <input type="checkbox" id="favorites-only" name="favorites-only">
        <label for="favorites-only">Seulement mes favoris</label>
      </div>
      <button type="submit">Filtrer</button>
    </form>

    <p class="results-count" aria-live="polite"><?= $count === 1 ? '1 événement affiché' : e($count) . ' événements affichés' ?></p>
    <p class="empty-message"<?= $count > 0 ? ' hidden' : '' ?>>Aucun événement ne correspond à votre recherche.</p>

    <div class="event-list" id="event-list">
      <?php foreach ($events as $event) : ?>
        <!-- data-places = places RESTANTES : js/app.js s'en sert pour trier -->
        <article class="event-card"
                 data-id="<?= e($event['id']) ?>"
                 data-club="<?= e($event['club']) ?>"
                 data-date="<?= e($event['starts_at']) ?>"
                 data-places="<?= e($event['places_left']) ?>"<?= in_array($event['id'], $matchingIds, true) ? '' : ' hidden' ?>>
          <p class="club"><?= e($event['club']) ?></p>
          <h2><?= e($event['title']) ?></h2>
          <p class="meta"><time datetime="<?= e(isoDate($event['starts_at'])) ?>"><?= e(formatDate($event['starts_at'])) ?></time>, <?= e(formatTimeRange($event['starts_at'], $event['ends_at'])) ?></p>
          <p class="meta"><?= e($event['place']) ?></p>
          <p class="places"><strong><?= e(placesLabel($event['places_left'])) ?></strong></p>
          <a href="evenement.php?id=<?= e($event['id']) ?>">Voir le détail</a>
          <button type="button" class="favorite-btn" data-title="<?= e($event['title']) ?>" aria-pressed="false" aria-label="Ajouter aux favoris : <?= e($event['title']) ?>">☆</button>
        </article>
      <?php endforeach; ?>
    </div>

    <section>
      <h2>Récapitulatif</h2>
      <table>
        <thead>
          <tr><th scope="col">Événement</th><th scope="col">Date</th><th scope="col">Places</th></tr>
        </thead>
        <tbody>
          <?php foreach ($matching as $event) : ?>
            <tr>
              <td><?= e($event['title']) ?></td>
              <td><?= e((new DateTimeImmutable($event['starts_at']))->format('d/m')) ?></td>
              <td><?= e(placesLabel($event['places_left'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
