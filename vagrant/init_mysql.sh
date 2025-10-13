#Page par défaut de apache à supprimer pour mettre à la place index.php
sudo rm /var/www/html/index.html

#mdp pour root (nécessaire pour se connecter avec mysqli)
if ! sudo mysql -u root -pazerty -e "SELECT User, plugin FROM mysql.user WHERE User='root' AND plugin='mysql_native_password';" | grep -q 'root'; then
    sudo mysql -e "ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'azerty'; FLUSH PRIVILEGES;"
fi

sudo mysql -u root -pazerty -e "
CREATE DATABASE IF NOT EXISTS manage_clients_db;
USE manage_clients_db;
CREATE TABLE IF NOT EXISTS users (
    id_client INT AUTO_INCREMENT PRIMARY KEY,
    name varchar(255),
    ip_client varchar(255)
);
CREATE TABLE IF NOT EXISTS container_client (
    container_port int PRIMARY KEY,
    id_client int,
    FOREIGN KEY(id_client)
        REFERENCES users(id_client) ON DELETE CASCADE
);
"