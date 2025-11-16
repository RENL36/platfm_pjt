<?php
// ------------------
// Connexion BDD
// ------------------
$db_server = "localhost";
$db_user = "root";
$db_pass = "azerty";
$db_name = "base";
$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

if (!$conn) {
    die("Erreur DB : " . mysqli_connect_error());
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

    $name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $firstname = mysqli_real_escape_string($conn, $_POST['user_firstname']);

    // Ajouter utilisateur
    $sql = "INSERT INTO users (user_name, user_firstname) VALUES ('$name', '$firstname')";
    mysqli_query($conn, $sql);

    $user_id = mysqli_insert_id($conn);

    // Redirection vers tableau utilisateur
    afficher_page_utilisateur($conn, $user_id);
    exit;
}



// ========================================================================
// 2) TRAITEMENT : CONNEXION
// ========================================================================
if ($_POST['action'] == "connexion") {

    $name = mysqli_real_escape_string($conn, $_POST['user_name']);
    $firstname = mysqli_real_escape_string($conn, $_POST['user_firstname']);

    $sql = "SELECT user_id FROM users 
            WHERE user_name='$name' AND user_firstname='$firstname' LIMIT 1";
    $res = mysqli_query($conn, $sql);

    if (mysqli_num_rows($res) == 0) {
        echo "<h2>Utilisateur introuvable</h2>";
        echo "<a href='index.php'>Retour</a>";
        exit;
    }

    $row = mysqli_fetch_assoc($res);
    $user_id = $row['user_id'];

    afficher_page_utilisateur($conn, $user_id);
    exit;
}



// ========================================================================
// FONCTION : Affichage de la page personnalisée utilisateur
// ========================================================================
function afficher_page_utilisateur($conn, $user_id)
{
    // Récup info utilisateur
    $sql = "SELECT * FROM users WHERE user_id=$user_id";
    $user = mysqli_fetch_assoc(mysqli_query($conn, $sql));

    // Récup liste conteneurs associés
    $sql = "SELECT * FROM containers WHERE container_id IN 
           (SELECT container_id FROM users WHERE user_id=$user_id)";
    $containers = mysqli_query($conn, $sql);

    $nb_containers = mysqli_num_rows($containers);

    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Espace utilisateur</title>
    </head>
    <body>

        <h1>Bonjour <?= $user['user_firstname'] . " " . $user['user_name'] ?></h1>

        <h2>Vos conteneurs : <?= $nb_containers ?></h2>

        <?php if ($nb_containers > 0) { ?>
            <ul>
                <?php while ($c = mysqli_fetch_assoc($containers)) { ?>
                    <li>
                        <?= $c['container_name'] ?> — port <?= $c['container_port'] ?>
                    </li>
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
