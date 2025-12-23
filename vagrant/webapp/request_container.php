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


$sql = "SELECT container_port FROM containers WHERE contrat_id IS NOT NULL";
$result = mysqli_query($conn, $sql);

$used_ports = array();
$ports_to_assign = array();
while ($row = mysqli_fetch_array($result)) {
  array_push($used_ports, $row['container_port']);
}
$port = mt_rand(2221, 2223);
for ($i = 0; $i < $_POST['num_containers']; $i++) {
  if (count($used_ports) > 0) {
    while (in_array($port, $used_ports)) {
      $port = mt_rand(2221, 2223);
    }
  }

  array_push($ports_to_assign, $port);
  array_push($used_ports, $port);
  if (!isset($_SESSION['user_id'])) {
    die("Erreur: utilisateur non connecté");
  }
  $user_id = (int)$_SESSION['user_id'];
}

$sql = "INSERT INTO contrats (user_id, date_debut, date_expiration) VALUES ($user_id, NOW(), DATE_ADD(NOW(), INTERVAL 5 MINUTE))";
if (mysqli_query($conn, $sql)) {
  echo " Contrat créé avec succès.";
}

$last_id = mysqli_insert_id($conn);


$ports = implode(",", array_map('intval', $ports_to_assign));
$sql = "UPDATE containers SET contrat_id = $last_id WHERE container_port IN ($ports)";
if (mysqli_query($conn, $sql)) {
  echo "Conteneurs attribués. Les ports sont : " . implode(", ", $ports_to_assign) . "<br>";
} else {
<<<<<<< HEAD
  echo "Erreur lors de l'attribution des conteneurs : " . mysqli_error($conn);
}

$output = shell_exec('RET=`docker run `;echo $RET');
echo $output;
=======
  echo "Erreur lors de l'attribution des conteneurs : " . mysqli_error($conn);}
  
//create count in container for actual user
//ATTENTION : $container_res peut être une liste
$containers_sql = "SELECT container_name FROM containers WHERE container_port IN ($ports)";
$user_sql = "SELECT user_name FROM users WHERE user_id = $user_id";

$containers_res = mysqli_query($conn, $containers_sql);
$user_res = mysqli_query($conn, $user_sql);

$user_row = mysqli_fetch_assoc($user_res);
$user_name = $user_row['user_name'];
//inconvénient : si utilisateur perd son mdp
$user_password = bin2hex(random_bytes(4));

$user_esc = escapeshellarg($user_name);
$pass_esc = escapeshellarg($user_password);

while ($row = mysqli_fetch_assoc($containers_res)) {
    $container = $row['container_name'];
    //$container_esc = escapeshellarg($container);
    $command = "docker exec -u root $container bash -c \"id $user_esc >/dev/null 2>&1 || (useradd -m -s /bin/bash $user_esc && echo '$user_esc:$pass_esc' | chpasswd)\"";    exec($command, $output, $return_var);

  if ($return_var === 0) {
    echo "Accès aux ressources >> connexion SSH<br>";
    echo "identifiant :  $user_name<br>";
    echo "password : $user_password";
  } else {
}}
?>
>>>>>>> 0f6e34e6f6fcd659fc0b61a18a55885d46dd3bf1
