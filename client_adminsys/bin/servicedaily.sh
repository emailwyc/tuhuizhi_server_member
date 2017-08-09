cd /home/wangxiang/services/adminsys
git reset --hard
git pull
node bin/parsehtmlimg.js

rm -rf static/dist
NODE_ENV="pro" webpack --progress --colors
cd static/dist
echo 'lftp upload static'
dir=$(ls | grep '[A-Za-z0-9]\{20\}')
echo $dir
lftp ftpuser:e9b0c3cd9c6d43f612234f0deb28d915@123.56.109.162 -e "mirror -R ${dir} /static/dist/;exit"
pm2 restart dashboardtest
