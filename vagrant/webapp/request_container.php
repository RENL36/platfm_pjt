<?php 


session_start();
$db_server = "localhost";
$db_user = "root";
$db_pass = "azerty";
$db_name = "base";
$conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
if (!$conn) {
  die("Erreur DB : " . mysqli_connect_error());
}


$sql = "SELECT container_port FROM containers INNER JOIN users ON containers.container_id = users.container_id WHERE users.container_id IS NOT NULL";
$result = mysqli_query($conn, $sql);
$port = mt_rand(2221, 2223);
$used_ports = array();
while ($row = mysqli_fetch_array($result)) {
  array_push($used_ports, $row['container_port']);
}
if (count($used_ports) > 0) {
  while (in_array($port, $used_ports)) {
    $port = mt_rand(2221, 2223);
  }

}

$sql = "SELECT container_id from containers where container_port = $port";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_array($result);
$container_id = $row['container_id'];

$sql = "UPDATE users SET container_id = $container_id WHERE user_id = ".$_SESSION['user_id'];
if (mysqli_query($conn, $sql)) {
  echo "Un conteneur a été attribué. Le port est : " . $port;

} else {
  echo "Erreur lors de l'attribution du conteneur : " . mysqli_error($conn);
}

$sql = "SELECT user_name, user_firstname, users.container_id, container_port FROM users INNER JOIN containers ON users.container_id = containers.container_id where users.user_id = ".$_SESSION['user_id'];
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
  $json_arr = array();
  while ($row = mysqli_fetch_assoc($result)) {
    $json_arr[] = $row;
  }
  file_put_contents('users_containers.json', json_encode($json_arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}




?>