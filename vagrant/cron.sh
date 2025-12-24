#!/bin/bash

#!/bin/bash

cron_maj_abonnement="* * * * * ansible-playbook -i localhost, /home/vagrant/ansible/playbook2.yml"
cron_maj_conteneur="* * * * * /home/vagrant/maj_conteneur.sh"

# Mettre les deux tâches dans une liste
crons=(
  "$cron_maj_abonnement"
  "$cron_maj_conteneur"
)

for cron in "${crons[@]}"; do
  # Échapper les * pour grep
  cron_escaped=$(echo "$cron" | sed 's/\*/\\*/g')

  # Vérifier si la tâche existe déjà
  if crontab -l 2>/dev/null | grep -q "$cron_escaped"; then
    echo "Crontab déjà existant : $cron"
  else
    echo "Ajout du cron : $cron"
    crontab -l 2>/dev/null > mycron
    echo "$cron" >> mycron
    crontab mycron
    rm mycron
  fi
done
