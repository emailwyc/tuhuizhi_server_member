git reset --hard
git pull origin master
echo -e '\r\n\r\nrestart parking service...'

pm2 restart dashboard

echo -e '\r\n\r\nrunning on:'
echo 'https://dashboard.rtmap.com/dashboard'

exit 0
