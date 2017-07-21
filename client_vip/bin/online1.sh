git reset --hard
git pull origin master
echo -e '\r\n\r\nrestart parking service...'

pm2 restart vip

echo -e '\r\n\r\nrunning on:'
echo 'https://vip.rtmap.com/user/login?key_admin=e4273d13a384168962ee93a953b58ffd'

exit 0
