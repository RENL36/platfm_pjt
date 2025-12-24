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

    # Récupérer le contrat_id avant suppression
    SQL1="SELECT * FROM containers WHERE container_name='${CONTAINER_NAME}';"
    TUPLE=$(mysql -N -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "$SQL1")
    CONTRAT_ID=TUPLE[3]
    PORT=TUPLE[2]


    if CONTRAT_ID == null; then
      echo "Le conteneur supprimé n'appartient à personne"
    else 
      echo "contrat_id associé : $CONTRAT_ID"
      echo "Réattribution d'un conteneur"
      # Identifier les conteneurs non attribués
      SQL2="SELECT * from containers where contrat_id = NULL;"
      TABLE_CONTAINERS=$(mysql -N -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "$SQL2")
      NEW_CONTAINER=TABLE_CONTAINERS[0]
      SQL3="UPDATE containers set container_id=NEW_CONTAINER[0], container_name=NEW_CONTAINER[1], container_port=PORT, container_contrat=CONTRAT_ID where "
      mysql -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "$SQL3"
      SQL4=""
      echo "Nouveau conteneur attribué" + 
      docker exec -u root $container bash -c \"id $user_esc >/dev/null 2>&1 || (useradd -m -s /bin/bash $user_esc && echo '$user_esc:$pass_esc' | chpasswd)
   
    # Supprimer la ligne du conteneur
    SQL5="DELETE FROM containers WHERE container_name='${CONTAINER_NAME}';"
    mysql -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "$SQL5"

    if [ $? -eq 0 ]; then
        echo "Ligne du conteneur supprimée dans la table 'containers'."
    else
        echo "Erreur lors de la suppression dans la table 'containers'."
    fi

else
    echo "Erreur : impossible de supprimer le conteneur '$CONTAINER_NAME'."
    exit 1
fi
