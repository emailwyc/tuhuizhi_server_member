require('shelljs/global');

echo('check html');
cd('.');
if (exec('./node_modules/htmlhint/bin/htmlhint --config .htmlhintrc app/views/*.html').code !== 0) {
  echo('html lint error');
  exit(1);
}

echo('check js');
if (exec('webpack --progress --colors').code !== 0) {
  echo('js lint error');
  exit(1);
}
