
sudo mysql -e 'CREATE DATABASE IF NOT EXISTS manage_clients_db'
sudo mysql -e 'CREATE TABLE IF NOT EXISTS container_client (
                    container_port int PRIMARY KEY,
                    id_client int,
                    FOREIGN KEY(id_client)
                    REFERENCES users(id_client)  
                    container_id varchar(255)
);'
sudo mysql -e 'CREATE TABLE IF NOT EXISTS users (
                    id_client int INT AUTO_INCREMENT PRIMARY KEY,
                    name varchar(255),
                    ip_client varchar(255),
);'