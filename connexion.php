<?php
/* =====================================================================
   connexion.php : formulaire de connexion
   ===================================================================== */
require __DIR__ . '/includes/bootstrap.php';

// ?next=page.php : page à retrouver après la connexion (défi "Retour à la page demandée").
// Il peut arriver par l'URL (GET) ou par le champ caché du formulaire (POST).
// safeNext() refuse tout ce qui n'est pas une page locale simple (voir auth.php).
$nextRaw = inputString($_POST, 'next') !== '' ? inputString($_POST, 'next') : inputString($_GET, 'next');
$next = safeNext($nextRaw, 'compte.php');

// Déjà connecté : on envoie directement à la bonne page
if (currentUser() !== null) {
    redirect($next);
}

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = inputString($_POST, 'email');
    $password = inputRaw($_POST, 'password');

    $blocked = loginBlockedSeconds();
    if ($blocked > 0) {
        // Trop d'échecs : on n'examine même pas le mot de passe
        $error = 'Trop de tentatives. Réessayez dans ' . $blocked . ' secondes.';
    } else {
        $user = findUserByEmail($email);

        // password_verify compare le mot de passe tapé avec le hachage enregistré.
        // Si l'email n'existe pas, on vérifie quand même contre un hachage factice :
        // le temps de réponse est le même, et on ne révèle pas si le compte existe.
        $hash = $user['password_hash'] ?? DUMMY_PASSWORD_HASH;
        $passwordOk = password_verify($password, $hash);

        if ($user !== null && $passwordOk) {
            loginUser($user);
            flash('success', 'Bonjour ' . firstName($user['name']) . ' !');
            redirect($next);   // PRG : redirection après le POST
        }

        // Échec : UN SEUL message, sans dire si c'est l'email ou le mot de passe qui est faux
        registerLoginFailure();
        $error = 'Email ou mot de passe incorrect.';
        if (loginBlockedSeconds() > 0) {
            $error = 'Trop de tentatives. Réessayez dans ' . loginBlockedSeconds() . ' secondes.';
        }
    }
}

$pageTitle = 'Connexion · ClubHub';
$currentPage = 'connexion';
require __DIR__ . '/includes/header.php';
?>

  <main class="auth-page">
    <section class="auth-card">
      <h1>Connexion</h1>

      <?php if ($error !== '') : ?>
        <p class="flash flash-error" role="alert"><?= e($error) ?></p>
      <?php endif; ?>

      <form action="connexion.php" method="post">
        <?php if ($next !== 'compte.php' || $nextRaw !== '') : ?>
          <!-- On garde la page demandée pendant l'envoi du formulaire -->
          <input type="hidden" name="next" value="<?= e($next) ?>">
        <?php endif; ?>
        <p>
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required autocomplete="email" value="<?= e($email) ?>">
        </p>
        <p>
          <label for="password">Mot de passe</label>
          <input type="password" id="password" name="password" required autocomplete="current-password">
        </p>
        <p><button type="submit">Se connecter</button></p>
      </form>
      <p>Pas encore de compte ? <a href="creer-compte.php">Créer un compte</a></p>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
