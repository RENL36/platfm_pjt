<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Service déploiement de containers</title>
</head>
<body>
    <h1>Inscription</h1>
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
$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
if ($conn) {
    echo "Connecté";
}
else {
    echo "Pas connecté";
}

$sql = "INSERT INTO users(name, ip_client) VALUES ('".$_POST['user_name']."', '".$_SERVER['REMOTE_ADDR']."')";
$insert = mysqli_query($conn, $sql);


$sql = "SELECT container_port FROM container_client";
$result = mysqli_query($conn, $sql);
$port = mt_rand(1024, 65535);
$used_ports = array();
while ($row = mysqli_fetch_array($result)) {
  array_push($used_ports, $row['container_port']);
}
if (count($used_ports) > 0) {
  while (in_array($port, $used_ports)) {
    $port = mt_rand(1024, 65535);
  }

}
$sql = "INSERT INTO container_client(container_port, id_client) VALUES ($port, LAST_INSERT_ID())";
$insert = mysqli_query($conn, $sql);

$sql = "SELECT users.id_client, users.name, container_client.container_port FROM users INNER JOIN container_client ON users.id_client = container_client.id_client";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
  $json_arr = array();
  while ($row = mysqli_fetch_assoc($result)) {
    $json_arr[] = $row;
  }
  echo json_encode($json_arr);
  file_put_contents('users_containers.json', json_encode($json_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}



$conn->close();

}

?>

</body>
</html>