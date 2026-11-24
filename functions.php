<?php
/* =====================================================================
   includes/functions.php : fonctions communes à toutes les pages
   (TP6 : les fonctions qui lisaient data/events.php ont disparu, remplacées par includes/events.php)
   ===================================================================== */

/**
 * e() = "escape" : protège une valeur avant de l'afficher dans du HTML (anti-XSS).
 * RÈGLE : tout ce qui vient d'un utilisateur ou de la base passe par e() avant d'être affiché.
 */
function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/** Renvoie le prénom d'un nom complet ("Amine Ben Salah" -> "Amine"). */
function firstName(string $fullName): string
{
    $parts = preg_split('/\s+/', trim($fullName));
    return $parts[0] ?? '';
}

/** Lit un champ de $_POST ou $_GET en toute sécurité (texte sans espaces autour, '' si absent). */
function inputString(array $source, string $key): string
{
    $value = $source[$key] ?? '';
    return is_string($value) ? trim($value) : '';
}

/** Comme inputString() mais SANS trim() : pour les mots de passe. */
function inputRaw(array $source, string $key): string
{
    $value = $source[$key] ?? '';
    return is_string($value) ? $value : '';
}

/** Liste des clubs SANS doublon, pour le menu déroulant. */
function getClubs(array $events): array
{
    return array_values(array_unique(array_column($events, 'club')));
}

/** Renvoie ' aria-current="page"' si la page est la page actuelle, sinon rien. */
function ariaCurrent(string $page, string $currentPage): string
{
    return $page === $currentPage ? ' aria-current="page"' : '';
}

/* ---------------------------------------------------------------------
   Affichage des dates, heures et places
   (si votre starter contient déjà ces fonctions, gardez les vôtres et supprimez celles-ci)
   --------------------------------------------------------------------- */

/** "2026-10-14 14:00:00" -> "mercredi 14 octobre" (avec $withYear : "mercredi 14 octobre 2026"). */
function formatDate(string $datetime, bool $withYear = false): string
{
    static $days = ['dimanche', 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi'];
    static $months = ['janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet',
                      'août', 'septembre', 'octobre', 'novembre', 'décembre'];

    $d = new DateTimeImmutable($datetime);
    $text = $days[(int) $d->format('w')] . ' ' . $d->format('j') . ' ' . $months[(int) $d->format('n') - 1];
    return $withYear ? $text . ' ' . $d->format('Y') : $text;
}

/** "2026-10-14 14:00:00" -> "14 h" ; "2026-10-14 14:30:00" -> "14 h 30". */
function formatHour(string $datetime): string
{
    $d = new DateTimeImmutable($datetime);
    $minutes = (int) $d->format('i');
    return $d->format('G') . ' h' . ($minutes > 0 ? ' ' . $d->format('i') : '');
}

/** "14 h – 17 h". */
function formatTimeRange(string $start, string $end): string
{
    return formatHour($start) . ' – ' . formatHour($end);
}

/** "2026-10-14 14:00:00" -> "2026-10-14" (pour l'attribut datetime de <time> et pour trier). */
function isoDate(string $datetime): string
{
    return (new DateTimeImmutable($datetime))->format('Y-m-d');
}

/** 12 -> "12 places restantes" ; 1 -> "1 place restante" ; 0 -> "Complet". */
function placesLabel(int $placesLeft): string
{
    if ($placesLeft <= 0) {
        return 'Complet';
    }
    return $placesLeft === 1 ? '1 place restante' : $placesLeft . ' places restantes';
}

/**
 * Texte avec une ligne par élément -> tableau (lignes vides ignorées).
 * Sert pour les colonnes program et bring.
 */
function lines(string $text): array
{
    $lines = preg_split('/\R/u', $text) ?: [];
    return array_values(array_filter(array_map('trim', $lines), fn(string $line): bool => $line !== ''));
}
