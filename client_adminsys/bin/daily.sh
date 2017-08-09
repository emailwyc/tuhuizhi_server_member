echo -e '\r\nbuild parktest daily...'
git reset --hard
git pull origin daily

node bin/parsehtmlimg.js

echo -e  '\r\n\r\nbuild start...'
rm -rf static/dist
NODE_ENV="pro" webpack --progress --colors

echo -e '\r\n\r\n deliver static files...'
cd static/dist
dir=$(ls | grep '[A-Za-z0-9]\{20\}')
echo $dir
lftp ftpuser:e9b0c3cd9c6d43f612234f0deb28d915@123.56.109.162 -e "mirror -R ${dir} /static/dist/;exit"

echo -e '\r\n\r\ngit push static service...'
pm2 restart dashboardtest

echo -e '\r\n\r\nrunning on:'
echo 'https://dashboard.rtmap.com/dashboardtest'

exit 0
