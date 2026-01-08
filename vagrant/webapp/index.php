<?php
// ------------------
// Connexion BDD
// ------------------
$db_server = "localhost";
$db_user = "root";
$db_pass = "azerty";
$db_name = "base";
session_start();
$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);

$now = new DateTime();
$mins = $now->getOffset() / 60;
$sgn = ($mins < 0 ? -1 : 1);
$mins = abs($mins);
$hrs = floor($mins / 60);
$mins -= $hrs * 60;
$offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
mysqli_query($conn, "SET SESSION time_zone = '$offset'");


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
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background: white;
            padding: 30px;
            width: 400px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1, h2 {
            text-align: center;
        }
        form {
            margin-top: 20px;
        }
        input {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
        }
        button {
            width: 100%;
            padding: 10px;
            background: #007BFF;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
        hr {
            margin: 30px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Bienvenue</h1>

    <h2>Inscription</h2>
    <form action="index.php" method="post">
        <input type="hidden" name="action" value="inscription">
        Nom :
        <input type="text" name="user_name" required>
        Prénom :
        <input type="text" name="user_firstname" required>
        <button type="submit">S'inscrire</button>
    </form>

    <hr>

    <h2>Connexion</h2>
    <form action="index.php" method="post">
        <input type="hidden" name="action" value="connexion">
        Nom :
        <input type="text" name="user_name" required>
        Prénom :
        <input type="text" name="user_firstname" required>
        <button type="submit">Se connecter</button>
    </form>
</div>

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

    $sql = "INSERT INTO users (user_name, user_firstname, user_password) VALUES ('$name', '$firstname', NULL)";
    mysqli_query($conn, $sql);

    $user_id = mysqli_insert_id($conn);
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_firstname'] = $_POST['user_firstname'];
    $_SESSION['user_name'] = $_POST['user_name'];

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
        echo "<h2 style='text-align:center'>Utilisateur introuvable</h2>";
        echo "<p style='text-align:center'><a href='index.php'>Retour</a></p>";
        exit;
    }

    $row = mysqli_fetch_assoc($res);
    $user_id = $row['user_id'];
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_firstname'] = $_POST['user_firstname'];
    $_SESSION['user_name'] = $_POST['user_name'];
    afficher_page_utilisateur($conn, $user_id);
    exit;
}



// ========================================================================
// FONCTION : Affichage de la page personnalisée utilisateur
// ========================================================================
function afficher_page_utilisateur($conn, $user_id)
{
    $sql = "SELECT * FROM users WHERE user_id=$user_id";
    $user = mysqli_fetch_assoc(mysqli_query($conn, $sql));

    $sql = "SELECT * FROM containers WHERE contrat_id IN 
           (SELECT contrat_id FROM contrats WHERE user_id=$user_id)";
    $containers = mysqli_query($conn, $sql);

    $nb_containers = mysqli_num_rows($containers);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Espace utilisateur</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f5;
            padding: 40px;
        }
        .box {
            background: white;
            max-width: 600px;
            margin: auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h1, h2 {
            text-align: center;
        }
        ul {
            padding-left: 20px;
        }
        form {
            margin-top: 30px;
        }
        input, button {
            padding: 8px;
            width: 100%;
            margin-top: 10px;
        }
        button {
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        a {
            display: block;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="box">
    <h1>Bonjour <?= $user['user_firstname'] . " " . $user['user_name'] ?></h1>

    <h2>Vos conteneurs : <?= $nb_containers ?></h2>

    <?php if ($nb_containers > 0) { ?>
        <ul>
            <?php while ($c = mysqli_fetch_assoc($containers)) { ?>
                <li><?= $c['container_name'] ?> — port <?= $c['container_port'] ?></li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p style="text-align:center">Aucun conteneur pour le moment.</p>
    <?php } ?>

    <form action="request_container.php" method="post">
        <input type="hidden" name="user_id" value="<?= $user_id ?>">
        Nombre de conteneurs souhaités :
        <input type="text" name="num_containers" required>
        <button type="submit">Demander des conteneurs</button>
    </form>

    <a href="index.php">Déconnexion</a>
</div>

</body>
</html>
<?php
}
?>
