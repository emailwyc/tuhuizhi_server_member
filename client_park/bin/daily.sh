echo -e '\r\nbuild parktest daily...'
git reset --hard
git pull origin daily

echo -e  '\r\n\r\nbuild start...'
rm -rf static/dist
NODE_ENV="production" webpack --progress --colors

echo -e '\r\n\r\n deliver static files...'
cd static/dist
dir=$(ls | grep '[A-Za-z0-9]\{20\}')
echo $dir
lftp ftpuser:e9b0c3cd9c6d43f612234f0deb28d915@123.56.109.162 -e "mirror -R ${dir} /static/dist/;exit"

echo -e '\r\n\r\nrestart parktest service...'
pm2 restart parktest

echo -e '\r\n\r\nrunning on:'
echo 'https://h2.rtmap.com/park/?key_admin=e4273d13a384168962ee93a953b58ffd'

exit 0
