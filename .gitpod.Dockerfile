FROM gitpod/workspace-full

USER root

RUN apt-get update && apt-get -y install apache2 mysql-server php libapache2-mod-php php-mysql php-curl php-gd php-mbstring php-xml php-xmlrpc

RUN echo "include /workspace/lamp/apache/apache.conf" > /etc/apache2/apache2.conf
RUN echo ". /workspace/lamp/apache/envvars" > /etc/apache2/envvars

RUN echo "!include /workspace/lamp/mysql/mysql.cnf" > /etc/mysql/my.cnf

RUN mkdir /var/run/mysqld
RUN chown gitpod:gitpod /var/run/apache2 /var/lock/apache2 /var/run/mysqld

RUN sudo sed -i 's/^bind-address.*/#&/' /etc/mysql/mysql.conf.d/mysqld.cnf

RUN addgroup gitpod www-data
