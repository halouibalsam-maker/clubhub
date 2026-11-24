<?php
/* =====================================================================
   includes/auth.php : connexion, déconnexion, protection des pages, flash
   ---------------------------------------------------------------------
   Principe d'une session PHP :
     - le serveur garde les données dans $_SESSION (côté SERVEUR) ;
     - le navigateur ne garde qu'un cookie (PHPSESSID) = un numéro de session ;
     - à chaque requête, le navigateur renvoie le cookie, PHP retrouve $_SESSION.
   On ne met dans la session que l'ID de l'utilisateur : jamais le mot de passe.
   ===================================================================== */

/**
 * Hachage d'un mot de passe FACTICE (voir connexion.php) : sert à faire le même travail
 * de vérification quand l'email n'existe pas, pour ne pas révéler par le temps de réponse
 * si un compte existe.
 */
const DUMMY_PASSWORD_HASH = '$2y$12$1Uv.ZvFT.pcgc6Yump76XOHDaIxx9J/oF4DIsElF82Rv0Pa0SYgtG';

/** Nombre d'échecs de connexion autorisés, puis durée du blocage (défi "Limiter les tentatives") */
const MAX_LOGIN_ATTEMPTS = 5;
const LOGIN_BLOCK_SECONDS = 60;

/* ---------------------------------------------------------------------
   Fonctions données : redirect() et safeNext()
   --------------------------------------------------------------------- */

/** Redirige vers une adresse puis STOPPE le script (exit : sinon PHP continuerait à s'exécuter). */
function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Vérifie une adresse "next" avant de rediriger vers elle (défi "Retour à la page demandée").
 * POURQUOI C'EST NÉCESSAIRE : sans ce contrôle, un pirate peut envoyer un lien comme
 *   connexion.php?next=https://site-pirate.example
 * La victime se connecte sur le VRAI site, puis est renvoyée vers le site pirate qui imite
 * ClubHub et lui redemande son mot de passe : c'est une faille « open redirect ».
 * On n'accepte donc qu'une page locale simple : "nom-de-page.php" avec éventuellement "?parametres".
 * Tout le reste (http://..., //site, \site, /chemin...) est refusé et remplacé par $default.
 */
function safeNext(?string $next, string $default = 'compte.php'): string
{
    if ($next === null || $next === '') {
        return $default;
    }
    if (preg_match('/^[A-Za-z0-9_-]+\.php(\?[A-Za-z0-9_=&%.-]*)?$/', $next) === 1) {
        return $next;
    }
    return $default;
}

/* ---------------------------------------------------------------------
   Utilisateur connecté
   --------------------------------------------------------------------- */

/**
 * Renvoie l'utilisateur connecté (tableau) ou null si personne n'est connecté.
 * On relit l'utilisateur dans le fichier à chaque fois à partir de l'ID de la session :
 * si le compte a été supprimé entre-temps, la personne n'est plus considérée connectée.
 */
function currentUser(): ?array
{
    $id = $_SESSION['user_id'] ?? null;
    if (!is_int($id)) {
        return null;
    }
    $user = findUserById($id);
    if ($user === null) {
        unset($_SESSION['user_id']);
    }
    return $user;
}

/**
 * À appeler en haut de CHAQUE page protégée. Si personne n'est connecté :
 * message flash + redirection vers connexion.php en mémorisant la page demandée (?next=).
 */
function requireLogin(): array
{
    $user = currentUser();
    if ($user !== null) {
        return $user;
    }

    // Page demandée = nom du fichier + paramètres, ex. "evenement.php?id=2"
    $path = basename((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH));
    $query = (string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_QUERY);
    $next = $query === '' ? $path : $path . '?' . $query;

    flash('info', 'Connectez-vous pour accéder à cette page.');
    redirect('connexion.php?next=' . urlencode($next));
}

/**
 * Connecte un utilisateur.
 * session_regenerate_id(true) : on change le numéro de session à la connexion. Ainsi un pirate
 * qui aurait fait connaître son numéro de session à la victime (« fixation de session »)
 * ne peut pas l'utiliser. Le "true" supprime l'ancienne session.
 * On vide aussi $_SESSION : rien de l'ancienne session (ex. inscriptions d'un autre compte
 * resté connecté) ne doit passer au nouvel utilisateur.
 */
function loginUser(array $user): void
{
    $_SESSION = [];
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];   // seulement l'ID, jamais le mot de passe
}

/**
 * Déconnecte l'utilisateur : toutes les données de session sont effacées (y compris les
 * inscriptions, voir reponses.md) et le numéro de session change. On garde la même session
 * (au lieu de session_destroy puis un 2e session_start) pour pouvoir y poser le message flash.
 */
function logoutUser(): void
{
    $_SESSION = [];
    session_regenerate_id(true);
}

/* ---------------------------------------------------------------------
   Messages flash : un message affiché UNE seule fois après une redirection
   --------------------------------------------------------------------- */

/** Prépare un message pour la prochaine page affichée. $type : 'success', 'info' ou 'error'. */
function flash(string $type, string $message): void
{
    if (!in_array($type, ['success', 'info', 'error'], true)) {
        $type = 'info';
    }
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Lit le message flash ET le supprime (donc il ne s'affiche qu'une fois). Null s'il n'y en a pas. */
function takeFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return is_array($flash) ? $flash : null;
}

/* ---------------------------------------------------------------------
   Limiter les tentatives de connexion (défi)
   --------------------------------------------------------------------- */

/** Nombre de secondes restantes de blocage (0 = pas bloqué). */
function loginBlockedSeconds(): int
{
    $until = $_SESSION['login_blocked_until'] ?? 0;
    return max(0, (int) $until - time());
}

/** À appeler après un échec : compte les échecs et bloque à partir de 5. */
function registerLoginFailure(): void
{
    $_SESSION['login_failures'] = (int) ($_SESSION['login_failures'] ?? 0) + 1;
    if ($_SESSION['login_failures'] >= MAX_LOGIN_ATTEMPTS) {
        $_SESSION['login_blocked_until'] = time() + LOGIN_BLOCK_SECONDS;
        $_SESSION['login_failures'] = 0;
    }
}
