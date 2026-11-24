<?php
/* =====================================================================
   includes/events.php : lecture des événements (tout le SQL des événements est ICI)
   ---------------------------------------------------------------------
   Chaque événement renvoyé contient les colonnes de la table events, plus :
     registered  : nombre d'inscrits                (LEFT JOIN + COUNT + GROUP BY)
     places_left : places restantes (jamais < 0)
     is_past     : true si l'événement a déjà commencé
   LEFT JOIN (et pas JOIN) : un événement SANS inscription doit quand même apparaître (0 inscrit).
   ===================================================================== */

const EVENT_SELECT = '
    SELECT e.id, e.club, e.title, e.description, e.place, e.starts_at, e.ends_at,
           e.capacity, e.program, e.bring,
           COUNT(r.id) AS registered,
           e.capacity - COUNT(r.id) AS places_left,
           (e.starts_at < NOW()) AS is_past
    FROM events e
    LEFT JOIN registrations r ON r.event_id = e.id';

/** Met les bons types PHP (MySQL peut renvoyer des textes selon la configuration). */
function castEvent(array $event): array
{
    $event['id'] = (int) $event['id'];
    $event['capacity'] = (int) $event['capacity'];
    $event['registered'] = (int) $event['registered'];
    $event['places_left'] = max(0, (int) $event['places_left']);
    $event['is_past'] = (bool) $event['is_past'];
    return $event;
}

/**
 * Événements À VENIR (starts_at >= NOW()), triés par date (ou par places restantes).
 *  $limit : nombre maximum (null = tous)
 *  $query : texte cherché dans le club, le titre, le lieu ou la description (vide = tout)
 *  $club  : nom exact du club (vide = tous)
 *  $sort  : 'date' (défaut) ou 'places'
 */
function getEvents(?int $limit = null, string $query = '', string $club = '', string $sort = 'date'): array
{
    $where = ['e.starts_at >= NOW()'];
    $params = [];

    if ($club !== '') {
        $where[] = 'e.club = :club';
        $params['club'] = $club;
    }
    if ($query !== '') {
        // % et _ sont des jokers de LIKE : on les neutralise pour chercher le texte tel quel.
        // (La collation utf8mb4_unicode_ci ignore déjà majuscules et accents : "echecs" trouve "Échecs".)
        $like = '%' . addcslashes($query, '\\%_') . '%';
        $where[] = '(e.club LIKE :q1 OR e.title LIKE :q2 OR e.place LIKE :q3 OR e.description LIKE :q4)';
        // Un même :nom ne peut pas servir deux fois avec de vraies requêtes préparées : q1..q4
        $params += ['q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like];
    }

    // Le tri ne vient jamais directement de l'utilisateur : on choisit entre deux textes écrits ici.
    $order = $sort === 'places' ? 'places_left DESC, e.starts_at' : 'e.starts_at';

    $sql = EVENT_SELECT . ' WHERE ' . implode(' AND ', $where) . ' GROUP BY e.id ORDER BY ' . $order;
    if ($limit !== null) {
        $sql .= ' LIMIT :limit';
    }

    $stmt = db()->prepare($sql);
    foreach ($params as $name => $value) {
        $stmt->bindValue(':' . $name, $value);
    }
    if ($limit !== null) {
        $stmt->bindValue(':limit', max(0, $limit), PDO::PARAM_INT);   // LIMIT exige un entier
    }
    $stmt->execute();

    return array_map('castEvent', $stmt->fetchAll());
}

/** Un événement (à venir OU passé) par son id, ou null s'il n'existe pas. */
function findEvent(int $id): ?array
{
    $stmt = db()->prepare(EVENT_SELECT . ' WHERE e.id = :id GROUP BY e.id');
    $stmt->execute(['id' => $id]);
    $event = $stmt->fetch();
    return $event === false ? null : castEvent($event);
}
