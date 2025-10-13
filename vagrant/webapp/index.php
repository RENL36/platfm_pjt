<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service déploiement de containers</title>
</head>
<body>
    <h1>Connexion</h1>
    <p><form action="index.php" method="post">
  <ul>
    <li>
      <label for="name">Nom :</label>
      <input type="text" id="name" name="user_name" required />
    </li>
    <li>
      <label for="nbcont">Number of containers:</label>
      <input type="number" id="nbcont" name="container_nb" required />
    </li>
    <li>
      <button type="submit">Envoyer</button>
  </ul>
</form></p>

<?php 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

$db_server = "localhost";
$db_user = "root";
$db_pass = "azerty";
$db_name = "manage_clients_db";
$conn = new mysqli($db_server, $db_user, $db_pass, $db_name);
if ($conn) {
    echo "Connecté";
}
else {
    echo "Pas connecté";
}


}


?>

</body>
</html>