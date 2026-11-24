<?php
/* =====================================================================
   includes/registrations.php : inscriptions aux événements (table registrations)
   Les inscriptions sont en base : elles SURVIVENT à la déconnexion.
   ===================================================================== */

/** Cette personne est-elle inscrite à cet événement ? */
function isRegistered(int $userId, int $eventId): bool
{
    $stmt = db()->prepare('SELECT 1 FROM registrations WHERE user_id = :user AND event_id = :event LIMIT 1');
    $stmt->execute(['user' => $userId, 'event' => $eventId]);
    return $stmt->fetchColumn() !== false;
}

/**
 * Inscrit une personne. Renvoie :
 *   'ok'      inscription enregistrée
 *   'already' déjà inscrite
 *   'full'    plus de place
 *   'closed'  événement inexistant ou déjà commencé
 *
 * DÉFI "La dernière place" : tout se passe dans UNE transaction, et la première requête
 * est un SELECT ... FOR UPDATE sur la ligne de l'événement. Deux personnes qui s'inscrivent
 * en même temps ne peuvent donc pas être dans la section "compter puis insérer" ensemble :
 * la 2e attend que la 1re ait fini (COMMIT), puis compte et voit que c'est complet.
 */
function registerUser(int $userId, int $eventId, string $motivation): string
{
    $pdo = db();

    try {
        $pdo->beginTransaction();

        // Verrou sur la ligne de l'événement jusqu'au COMMIT / ROLLBACK
        $stmt = $pdo->prepare('SELECT capacity, (starts_at < NOW()) AS is_past FROM events WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $eventId]);
        $event = $stmt->fetch();

        if ($event === false || (bool) $event['is_past']) {
            $pdo->rollBack();
            return 'closed';
        }

        if (isRegistered($userId, $eventId)) {
            $pdo->rollBack();
            return 'already';
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM registrations WHERE event_id = :id');
        $stmt->execute(['id' => $eventId]);
        if ((int) $stmt->fetchColumn() >= (int) $event['capacity']) {
            $pdo->rollBack();
            return 'full';
        }

        $stmt = $pdo->prepare(
            'INSERT INTO registrations (user_id, event_id, motivation) VALUES (:user, :event, :motivation)'
        );
        $stmt->execute(['user' => $userId, 'event' => $eventId, 'motivation' => $motivation]);

        $pdo->commit();
        return 'ok';
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        // 23000 = violation de contrainte : ici la contrainte UNIQUE (user_id, event_id).
        // C'est le filet de sécurité de la base, même si le test isRegistered() a été contourné.
        if ($e->getCode() === '23000') {
            return 'already';
        }
        throw $e;   // autre erreur : on ne la cache pas
    }
}

/**
 * Annule l'inscription de CETTE personne. Renvoie true si une inscription a été supprimée.
 * Sécurité : $userId vient de la session (requireLogin()), jamais du formulaire, et il est
 * dans le WHERE : modifier event_id dans les DevTools ne permet donc de supprimer que ses
 * PROPRES inscriptions. Le JOIN refuse aussi d'annuler un événement déjà passé.
 */
function cancelRegistration(int $userId, int $eventId): bool
{
    $stmt = db()->prepare(
        'DELETE r FROM registrations r
         JOIN events e ON e.id = r.event_id
         WHERE r.user_id = :user AND r.event_id = :event AND e.starts_at >= NOW()'
    );
    $stmt->execute(['user' => $userId, 'event' => $eventId]);
    return $stmt->rowCount() > 0;
}

/**
 * Les inscriptions d'une personne, avec les infos de l'événement (JOIN), triées par date.
 * Chaque ligne = colonnes de events + motivation, registered_at et is_upcoming.
 */
function registrationsOfUser(int $userId): array
{
    $stmt = db()->prepare(
        'SELECT e.id, e.club, e.title, e.place, e.starts_at, e.ends_at,
                r.motivation, r.created_at AS registered_at,
                (e.starts_at >= NOW()) AS is_upcoming
         FROM registrations r
         JOIN events e ON e.id = r.event_id
         WHERE r.user_id = :user
         ORDER BY e.starts_at'
    );
    $stmt->execute(['user' => $userId]);

    return array_map(function (array $row): array {
        $row['id'] = (int) $row['id'];
        $row['is_upcoming'] = (bool) $row['is_upcoming'];
        return $row;
    }, $stmt->fetchAll());
}
