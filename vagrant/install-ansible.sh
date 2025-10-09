apt-get update

apt-get install -y software-properties-common gnupg

add-apt-repository --yes --update ppa:ansible/ansible

ansible-playbook -i "localhost," -c local playbook.yml



