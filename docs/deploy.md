sh disable.site.sh

sudo certbot certonly --standalone --force-renewal -d topall.megastore.sn -m admin@topall.megastore.sn --agree-tos

# en root ou via sudo
sudo adduser --disabled-password --gecos "" deploy
sudo usermod -aG www-data deploy
 
# colle ta clé publique here
 /home/deploy/.ssh/authorized_keys
# set private in github
$SSH_PRIVATE_KEY

# when errors
php artisan optimize:clear
php artisan key:generate --force
php artisan config:cache

sudo chown www-data:www-data database/database.sqlite
sudo chmod 664 database/database.sqlite


php artisan db:seed --class=DemoUserSeeder


