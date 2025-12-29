#!/bin/bash

# =========================
# Crons à ajouter avec logs
# =========================
LOG_DIR="/tmp"

cron_maj_abonnement="* * * * * ansible-playbook -i localhost, /home/vagrant/ansible/playbook2.yml >> $LOG_DIR/maj_abonnement.log 2>&1"
cron_maj_conteneur="* * * * * /home/vagrant/maj_conteneur.sh >> $LOG_DIR/maj_conteneur.log 2>&1"

# Liste des crons
crons=(
  "$cron_maj_abonnement"
  "$cron_maj_conteneur"
)

# Utilisateur pour lequel on ajoute les crons
CRON_USER="vagrant"

# Boucle sur chaque cron
for cron in "${crons[@]}"; do
    # Échapper les * pour grep
    cron_escaped=$(echo "$cron" | sed 's/\*/\\*/g')

    # Vérifier si la tâche existe déjà pour l'utilisateur
    if crontab -u "$CRON_USER" -l 2>/dev/null | grep -q "$cron_escaped"; then
        echo "Crontab déjà existant pour $CRON_USER : $cron"
    else
        echo "Ajout du cron pour $CRON_USER : $cron"

        # Récupérer le crontab existant, même si vide
        crontab -u "$CRON_USER" -l 2>/dev/null > /tmp/mycron || true

        # Ajouter la nouvelle ligne
        echo "$cron" >> /tmp/mycron

        # Installer le nouveau crontab
        crontab -u "$CRON_USER" /tmp/mycron

        # Nettoyer
        rm /tmp/mycron
    fi
done

# Afficher le crontab final pour debug
echo "Crontab final pour $CRON_USER :"
crontab -u "$CRON_USER" -l
