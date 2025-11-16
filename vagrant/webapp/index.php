<?php
// ------------------
// Connexion BDD avec PDO
// ------------------
$host = "localhost";
$dbname = "base";
$user = "root";
$pass = "azerty";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur DB : " . $e->getMessage());
}

// ------------------
// PAGE FORMULAIRE : accueil
// ------------------
if (!isset($_POST['action'])) {
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Accueil</title>
</head>
<body>
    <h1>Bienvenue</h1>

    <h2>Inscription</h2>
    <form action="index.php" method="post">
        <input type="hidden" name="action" value="inscription">
        Nom : <input type="text" name="user_name" required><br>
        Prénom : <input type="text" name="user_firstname" required><br>
        <button type="submit">S'inscrire</button>
    </form>

    <h2>Connexion</h2>
    <form action="index.php" method="post">
        <input type="hidden" name="action" value="connexion">
        Nom : <input type="text" name="user_name" required><br>
        Prénom : <input type="text" name="user_firstname" required><br>
        <button type="submit">Se connecter</button>
    </form>

</body>
</html>
<?php
exit;
}

// ========================================================================
// 1) TRAITEMENT : INSCRIPTION
// ========================================================================
if ($_POST['action'] == "inscription") {

    $name = $_POST['user_name'];
    $firstname = $_POST['user_firstname'];

    // Ajouter utilisateur avec requête préparée
    $stmt = $pdo->prepare("INSERT INTO users (user_name, user_firstname) VALUES (:name, :firstname)");
    $stmt->execute([
        ':name' => $name,
        ':firstname' => $firstname
    ]);

    $user_id = $pdo->lastInsertId();

    // Redirection vers tableau utilisateur
    afficher_page_utilisateur($pdo, $user_id);
    exit;
}

// ========================================================================
// 2) TRAITEMENT : CONNEXION
// ========================================================================
if ($_POST['action'] == "connexion") {

    $name = $_POST['user_name'];
    $firstname = $_POST['user_firstname'];

    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_name = :name AND user_firstname = :firstname LIMIT 1");
    $stmt->execute([
        ':name' => $name,
        ':firstname' => $firstname
    ]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "<h2>Utilisateur introuvable</h2>";
        echo "<a href='index.php'>Retour</a>";
        exit;
    }

    $user_id = $user['user_id'];
    afficher_page_utilisateur($pdo, $user_id);
    exit;
}

// ========================================================================
// FONCTION : Affichage de la page personnalisée utilisateur
// ========================================================================
function afficher_page_utilisateur($pdo, $user_id)
{
    // Récup info utilisateur
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Récup liste conteneurs associés
    $stmt = $pdo->prepare("
        SELECT * FROM containers 
        WHERE container_id IN (
            SELECT container_id FROM users WHERE user_id = :id
        )
    ");
    $stmt->execute([':id' => $user_id]);
    $containers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $nb_containers = count($containers);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Espace utilisateur</title>
</head>
<body>

    <h1>Bonjour <?= htmlspecialchars($user['user_firstname'] . " " . $user['user_name']) ?></h1>

    <h2>Vos conteneurs : <?= $nb_containers ?></h2>

    <?php if ($nb_containers > 0) { ?>
        <ul>
            <?php foreach ($containers as $c) { ?>
                <li><?= htmlspecialchars($c['container_name']) ?> — port <?= htmlspecialchars($c['container_port']) ?></li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p>Aucun conteneur pour le moment.</p>
    <?php } ?>

    <form action="request_container.php" method="post">
        <input type="hidden" name="user_id" value="<?= $user_id ?>">
        <button type="submit">Demander un conteneur</button>
    </form>

    <br>
    <a href="index.php">Déconnexion</a>

</body>
</html>
<?php
}
?>
