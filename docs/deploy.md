sh disable.site.sh

sudo certbot certonly --standalone --force-renewal -d topall.megastore.sn -m admin@topall.megastore.sn --agree-tos

# en root ou via sudo
sudo adduser --disabled-password --gecos "" deploy
sudo usermod -aG www-data deploy
 
# colle ta clé publique here
 /home/deploy/.ssh/authorized_keys
# set private in github
$SSH_PRIVATE_KEY

