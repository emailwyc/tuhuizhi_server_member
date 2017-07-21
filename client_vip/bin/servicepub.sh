cd /usr/www/vip
git reset --hard
git pull
node bin/parsehtmlimg.js
pm2 restart vip
