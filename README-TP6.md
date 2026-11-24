# TP6 : ClubHub dans MySQL

## Installer la base (3 commandes)

```bash
mysql -u root -p -e "CREATE DATABASE clubhub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER 'clubhub'@'localhost' IDENTIFIED BY 'clubhub'; GRANT ALL PRIVILEGES ON clubhub.* TO 'clubhub'@'localhost';"
mysql -u clubhub -p clubhub < database/schema.sql
mysql -u clubhub -p clubhub < database/seed.sql
```

Puis : `cp config.example.php config.php` (config.php est ignoré par Git).

## Question 5 : test d'injection avec l'email `' OR '1'='1`

**Avec mon code (requête préparée) : rien ne se passe, la connexion échoue** avec le message
« Email ou mot de passe incorrect. ». `findUserByEmail()` envoie à MySQL la requête
`SELECT ... WHERE email = :email` **séparément** de la valeur. MySQL traite donc `' OR '1'='1`
comme un simple *texte* à comparer à la colonne `email` : aucun compte n'a cet email, la
fonction renvoie `null`, et `connexion.php` refuse.

**Avec une requête construite par concaténation** (`"... WHERE email = '$email'"`), la requête
deviendrait `WHERE email = '' OR '1'='1'` : la condition est toujours vraie, la base renvoie
le **premier utilisateur** et l'attaquant se connecte sans connaître ni email ni mot de passe
(si le mot de passe est aussi comparé en SQL). Les apostrophes saisies *changent la structure*
de la requête : c'est une injection SQL. Avec `prepare()` + `execute()`, la structure est fixée
avant que la valeur n'arrive : la valeur ne peut jamais devenir du SQL.
(Ici, même avec une faille, `password_verify` bloquerait la connexion : mais la faille permettrait
d'autres attaques, par exemple lire ou supprimer des tables.)

## Question 9 : annulation sûre

`cancelRegistration()` exécute :

```sql
DELETE r FROM registrations r JOIN events e ON e.id = r.event_id
WHERE r.user_id = :user AND r.event_id = :event AND e.starts_at >= NOW()
```

`:user` vient de la **session** (`requireLogin()`), jamais du formulaire. Si quelqu'un modifie
`event_id` dans les DevTools, la clause `r.user_id = :user` ne laisse supprimer que SES lignes :
l'inscription d'un autre n'est jamais touchée (`rowCount() = 0` → message d'erreur).

## Défis choisis

### La dernière place (transaction + `SELECT ... FOR UPDATE`)

**Scénario :** il reste 1 place. Amine et Sarra cliquent en même temps. Sans protection, les deux
requêtes comptent les inscrits (« 1 place libre »), puis les deux insèrent : on se retrouve avec
une place de trop (surréservation).

**Solution :** `registerUser()` ouvre une transaction et commence par
`SELECT ... FROM events WHERE id = ? FOR UPDATE`, qui **verrouille la ligne de l'événement**.
La 2e requête attend la fin de la 1re (COMMIT), puis compte les inscrits, voit « complet » et
renvoie `'full'`. Le test et l'insertion forment ainsi un bloc indivisible.

### Recherche côté serveur

`getEvents()` filtre avec `LIKE` (requête préparée, jokers `%` et `_` neutralisés, 4 paramètres
`:q1` à `:q4` car un même `:nom` ne peut pas servir deux fois). Le formulaire est un GET : il
marche sans JavaScript. La collation `utf8mb4_unicode_ci` ignore majuscules et accents.

### Événements passés

« Mon compte » sépare « À venir » et « Passés ». `cancelRegistration()` refuse d'annuler un
événement déjà commencé (`e.starts_at >= NOW()`) : vérifié côté serveur, pas seulement en cachant le bouton.
