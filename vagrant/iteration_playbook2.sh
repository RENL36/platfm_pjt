#!/bin/bash


for i in {1..11} ; do 
    ansible-playbook -i localhost, /home/vagrant/ansible/playbook2.yml >> /tmp/maj_abonnement.log 2>&1
    sleep 5 
done