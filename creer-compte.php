<?php
/* =====================================================================
   creer-compte.php : formulaire de création de compte
   Tout le PHP est en haut du fichier : header() (redirection) doit être appelé
   avant d'écrire du HTML.
   ===================================================================== */
require __DIR__ . '/includes/bootstrap.php';

// Déjà connecté : inutile de créer un compte
if (currentUser() !== null) {
    redirect('compte.php');
}

$values = ['name' => '', 'email' => ''];   // valeurs réaffichées en cas d'erreur (JAMAIS le mot de passe)
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values['name'] = inputString($_POST, 'name');
    $values['email'] = inputString($_POST, 'email');
    // Mots de passe : inputRaw (sans trim, on ne modifie pas ce que la personne a tapé)
    $password = inputRaw($_POST, 'password');
    $confirm = inputRaw($_POST, 'confirm');

    // Nom : entre 3 et 100 caractères
    $nameLength = mb_strlen($values['name'], 'UTF-8');
    if ($nameLength < 3 || $nameLength > 100) {
        $errors['name'] = 'Le nom doit contenir entre 3 et 100 caractères.';
    }

    // Email : valide ET pas déjà utilisé
    if (filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false) {
        $errors['email'] = "L'email n'est pas valide.";
    } elseif (findUserByEmail($values['email']) !== null) {
        $errors['email'] = 'Cet email est déjà utilisé.';
    }

    // Mot de passe : au moins 8 caractères
    if (mb_strlen($password, 'UTF-8') < 8) {
        $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }

    // Confirmation identique
    if ($confirm !== $password) {
        $errors['confirm'] = 'La confirmation est différente du mot de passe.';
    }

    if (count($errors) === 0) {
        // password_hash : transforme le mot de passe en un hachage à sens unique (bcrypt + sel
        // aléatoire intégré). On ne stocke JAMAIS le mot de passe lui-même.
        addUser($values['name'], $values['email'], password_hash($password, PASSWORD_DEFAULT));

        // Post/Redirect/Get : message flash puis redirection (F5 ne recrée pas le compte)
        flash('success', 'Votre compte est créé. Vous pouvez maintenant vous connecter.');
        redirect('connexion.php');
    }
}

$pageTitle = 'Créer un compte · ClubHub';
$currentPage = 'creer-compte';
require __DIR__ . '/includes/header.php';
?>

  <main class="auth-page">
    <section class="auth-card">
      <h1>Créer un compte</h1>
      <form action="creer-compte.php" method="post" novalidate>
        <p>
          <label for="name">Nom complet</label>
          <input type="text" id="name" name="name" required autocomplete="name" aria-describedby="name-error"
                 value="<?= e($values['name']) ?>"<?= isset($errors['name']) ? ' class="is-invalid" aria-invalid="true"' : '' ?>>
          <small class="field-error" id="name-error"><?= e($errors['name'] ?? '') ?></small>
        </p>
        <p>
          <label for="email">Email</label>
          <input type="email" id="email" name="email" required autocomplete="email" aria-describedby="email-error"
                 value="<?= e($values['email']) ?>"<?= isset($errors['email']) ? ' class="is-invalid" aria-invalid="true"' : '' ?>>
          <small class="field-error" id="email-error"><?= e($errors['email'] ?? '') ?></small>
        </p>
        <p>
          <label for="password">Mot de passe (8 caractères minimum)</label>
          <!-- Pas d'attribut value : le mot de passe n'est jamais réaffiché -->
          <input type="password" id="password" name="password" required autocomplete="new-password" aria-describedby="password-error"
                 <?= isset($errors['password']) ? 'class="is-invalid" aria-invalid="true"' : '' ?>>
          <small class="field-error" id="password-error"><?= e($errors['password'] ?? '') ?></small>
        </p>
        <p>
          <label for="confirm">Confirmation du mot de passe</label>
          <input type="password" id="confirm" name="confirm" required autocomplete="new-password" aria-describedby="confirm-error"
                 <?= isset($errors['confirm']) ? 'class="is-invalid" aria-invalid="true"' : '' ?>>
          <small class="field-error" id="confirm-error"><?= e($errors['confirm'] ?? '') ?></small>
        </p>
        <p><button type="submit">Créer mon compte</button></p>
      </form>
      <p>Déjà un compte ? <a href="connexion.php">Se connecter</a></p>
    </section>
  </main>

<?php require __DIR__ . '/includes/footer.php'; ?>
