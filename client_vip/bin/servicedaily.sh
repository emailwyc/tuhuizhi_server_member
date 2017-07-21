cd /usr/www/daily/vip
git reset --hard
git pull
node bin/parsehtmlimg.js
pm2 restart viptest
