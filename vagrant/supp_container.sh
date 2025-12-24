#!/bin/bash

# Vérification qu'un argument a été passé
if [ -z "$1" ]; then
    echo "Erreur : vous devez fournir le nom du conteneur à supprimer."
    exit 1
fi

CONTAINER_NAME="$1"

# Vérifier si le conteneur existe
if ! docker ps -a --format '{{.Names}}' | grep -wq "$CONTAINER_NAME"; then
    echo "Erreur : le conteneur '$CONTAINER_NAME' n'existe pas."
    exit 1
fi

# Supprimer le conteneur Docker
if docker rm -f "$CONTAINER_NAME"; then
    echo "Conteneur '$CONTAINER_NAME' supprimé avec succès."

    # Connexion à la base
    DB_HOST="localhost"
    DB_USER="root"
    DB_PASS="azerty"
    DB_NAME="base"

    # Récupérer les infos du conteneur avant suppression dans BDD
    SQL1="SELECT container_id, container_name, container_port, contrat_id FROM containers WHERE container_name='${CONTAINER_NAME}';"
    read CONTAINER_ID CONTAINER_NAME_DB PORT CONTRAT_ID <<< $(mysql -N -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "$SQL1")

    # Supprimer la ligne du conteneur
    SQL2="DELETE FROM containers WHERE container_name='${CONTAINER_NAME}';"
    mysql -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "$SQL2"

    if [ -z "$CONTRAT_ID" ] || [ "$CONTRAT_ID" = "NULL" ]; then
        echo "Le conteneur supprimé n'appartient à personne"
    else
        # Récupérer l'utilisateur associé
        SQL3="SELECT user_id FROM contrats WHERE contrat_id=${CONTRAT_ID};"
        USER_ID=$(mysql -N -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "$SQL3")
        SQL4="SELECT user_name FROM users WHERE user_id=${USER_ID};"
        USER_NAME=$(mysql -N -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "$SQL4")

        echo "Utilisateur associé : $USER_NAME"

        # Création d'un nouveau conteneur avec le même port
        echo "Réattribution d'un conteneur..."
        docker run -d --name "$CONTAINER_NAME" -p "$PORT":22 ubuntu-ssh
        echo "Nouveau conteneur attribué"

        # Actualisation de la BDD
        SQL5="INSERT INTO containers(container_name, container_port, contrat_id) VALUES ('$CONTAINER_NAME', $PORT, $CONTRAT_ID);"
        mysql -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "$SQL5"

        # Création de l'utilisateur dans le conteneur si nécessaire
        USER_PASS=$(openssl rand -hex 4)
        docker exec -u root "$CONTAINER_NAME" bash -c "id $USER_NAME >/dev/null 2>&1 || (useradd -m -s /bin/bash $USER_NAME && echo '$USER_NAME:$USER_PASS' | chpasswd)"
        echo "Identifiants : $USER_NAME / $USER_PASS"
    fi

else
    echo "Erreur : impossible de supprimer le conteneur '$CONTAINER_NAME'."
    exit 1
fi
