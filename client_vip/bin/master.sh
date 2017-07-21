echo -e '\r\nbuild vip master...'
git reset --hard
git pull origin master

mkdir -p static/dist/img
node bin/parsehtmlimg.js

echo -e  '\r\n\r\nbuild start...'
rm -rf static/dist
NODE_ENV="production" webpack --progress --colors

echo -e '\r\n\r\n deliver static files...'
cd static/dist
dir=$(ls | grep '[A-Za-z0-9]\{20\}')
echo $dir
lftp ftpuser:e9b0c3cd9c6d43f612234f0deb28d915@123.56.109.162 -e "mirror -R ${dir} /static/dist/;exit"

echo -e '\r\n\r\ngit push static version...'

cd ../../
git add .
git commit -m 'online'
git push origin master

exit 0
