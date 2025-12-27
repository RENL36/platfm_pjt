#!/bin/bash

# =========================
# CONFIG BDD
# =========================
DB_HOST="localhost"
DB_USER="root"
DB_PASS="azerty"
DB_NAME="base"

echo "Vérification des conteneurs en base..."

NB_CONTAINERS_SUPP=0

# Liste des conteneurs Docker existants
DOCKER_CONTAINERS=$(docker ps --format '{{.Names}}')

# Liste des conteneurs présents en base
DB_CONTAINERS=$(mysql -N -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" \
    -e "SELECT container_name FROM containers;")

for DB_CONTAINER in $DB_CONTAINERS; do
    FOUND=false

    # Vérifier existence côté Docker
    for DOCKER_CONTAINER in $DOCKER_CONTAINERS; do
        if [[ "$DB_CONTAINER" == "$DOCKER_CONTAINER" ]]; then
            FOUND=true
            break
        fi
    done

    # Si le conteneur n'existe plus
    if [[ "$FOUND" == false ]]; then
        echo "$DB_CONTAINER n'existe plus"
        
        # Infos BDD
        SQL1="SELECT container_port, contrat_id FROM containers WHERE container_name='${DB_CONTAINER}';"
        read PORT CONTRAT_ID <<< $(
            mysql -N -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "$SQL1"
        )

        # Si aucun contrat
        if [[ -z "$CONTRAT_ID" || "$CONTRAT_ID" == "NULL" ]]; then
            echo "Le conteneur supprimé n'appartenait à personne"
            continue
        fi

        echo "Réattribution d'un conteneur au client..."
        # Récupérer l'utilisateur
        SQL3="SELECT user_id FROM contrats WHERE contrat_id=${CONTRAT_ID};"
        USER_ID=$(mysql -N -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "$SQL3")

        SQL4="SELECT user_name, user_password FROM users WHERE user_id=${USER_ID};"
        read USER_NAME USER_PASS <<< $(
            mysql -N -u "$DB_USER" -p"$DB_PASS" -D "$DB_NAME" -e "$SQL4"
        )

        echo "Utilisateur associé : $USER_NAME"

        # Création du nouveau conteneur
        NEW_CONTAINER_NAME="${DB_CONTAINER}_bis"
        docker run -d --name "$NEW_CONTAINER_NAME" -p "$PORT":22 ubuntu-ssh
        echo "Nouveau conteneur créé : $NEW_CONTAINER_NAME"

        # Création utilisateur dans le conteneur
        docker exec -u root "$NEW_CONTAINER_NAME" bash -c "
            id $USER_NAME >/dev/null 2>&1 || (
                useradd -m -s /bin/bash $USER_NAME &&
                echo '$USER_NAME:$USER_PASS' | chpasswd
            )
        "

        echo "Identifiants : $USER_NAME / $USER_PASS"

        ((NB_CONTAINERS_SUPP++))
    fi
done

if [ "$NB_CONTAINERS_SUPP" -eq 0 ]; then
    echo "Aucun conteneur n'a été supprimé"
elif [ "$NB_CONTAINERS_SUPP" -eq 1 ]; then
    echo "1 conteneur a été supprimé"
else
    echo "$NB_CONTAINERS_SUPP conteneurs ont été supprimés"
fi

echo "OK : vérification terminée"
