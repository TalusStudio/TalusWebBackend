export LC_ALL=en_US.UTF-8
export LANG=en_US.UTF-8
export LANGUAGE=en_US.UTF-8

# save project folder path
PROJECT_FOLDER="/var/www/html/TalusWebBackend"

# update packages and install common packages
sudo apt-get update -y
sudo apt-get upgrade -y
sudo apt-get install software-properties-common -y

# add repo for >= php8.1
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update -y

# install lamp stack
sudo apt-get install tasksel -y
sudo tasksel install lamp-server

# install php8.1 related packages
sudo apt-get install php8.1 -y
sudo apt-get install php8.1-curl php8.1-mysql php8.1-mbstring php8.1-xml php8.1-zip -y
sudo apt-get install zip unzip -y

# install redis
sudo apt-get install redis-server -y

# install composer
cd ~
curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
HASH=`curl -sS https://composer.github.io/installer.sig`
php -r "if (hash_file('SHA384', '/tmp/composer-setup.php') === '$HASH') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
sudo php /tmp/composer-setup.php --install-dir=/usr/bin --filename=composer

# activate apache mod
sudo a2enmod rewrite

# restart related services
sudo service apache2 restart
sudo service cron restart
sudo service redis-server restart
sudo service mysql restart

# create environment file in project folder
cd $PROJECT_FOLDER
if [ ! -f ".env" ]; then
    cp .env.example .env
fi
