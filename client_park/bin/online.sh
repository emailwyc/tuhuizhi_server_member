git reset --hard
git pull origin master
echo -e '\r\n\r\nrestart park service...'

pm2 restart park

echo -e '\r\n\r\nrunning on:'
echo 'https://h5.rtmap.com/park?key_admin=e4273d13a384168962ee93a953b58ffd'

exit 0
