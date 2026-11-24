<?php
/* =====================================================================
   includes/users.php : lire et écrire les comptes dans MySQL (table users)
   ---------------------------------------------------------------------
   Mêmes fonctions qu'avant (findUserByEmail, findUserById, addUser) : les pages
   n'ont pas changé. Le mot de passe n'est JAMAIS stocké : seulement son hachage.
   Un utilisateur = ['id', 'name', 'email', 'password_hash', 'role', 'created_at']
   ===================================================================== */

const USER_COLUMNS = 'id, name, email, password_hash, role, created_at';

/** Renvoie la ligne avec un id de type int (currentUser() vérifie is_int), ou null. */
function castUser(array|false $user): ?array
{
    if ($user === false) {
        return null;
    }
    $user['id'] = (int) $user['id'];
    return $user;
}

/** Cherche un utilisateur par email. Renvoie null s'il n'existe pas. */
function findUserByEmail(string $email): ?array
{
    $stmt = db()->prepare('SELECT ' . USER_COLUMNS . ' FROM users WHERE email = :email');
    $stmt->execute(['email' => mb_strtolower(trim($email), 'UTF-8')]);
    return castUser($stmt->fetch());
}

/** Cherche un utilisateur par id. Renvoie null s'il n'existe pas. */
function findUserById(int $id): ?array
{
    $stmt = db()->prepare('SELECT ' . USER_COLUMNS . ' FROM users WHERE id = :id');
    $stmt->execute(['id' => $id]);
    return castUser($stmt->fetch());
}

/** Ajoute un utilisateur et le renvoie. $passwordHash est DÉJÀ haché (password_hash). */
function addUser(string $name, string $email, string $passwordHash, string $role = 'student'): array
{
    $stmt = db()->prepare(
        'INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :hash, :role)'
    );
    $stmt->execute([
        'name' => $name,
        'email' => mb_strtolower(trim($email), 'UTF-8'),
        'hash' => $passwordHash,
        'role' => $role,
    ]);
    // lastInsertId() = id donné par AUTO_INCREMENT (plus besoin de "plus grand id + 1")
    return findUserById((int) db()->lastInsertId());
}
