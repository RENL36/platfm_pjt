FROM ubuntu:22.04

RUN apt update && apt install -y openssh-server sudo
RUN mkdir /var/run/sshd
#Pour se connecter (seulement admin)
RUN echo 'root:root123' | chpasswd
RUN sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config
EXPOSE 22
CMD ["/usr/sbin/sshd","-D"]
