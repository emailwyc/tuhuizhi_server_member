var feather = require('koa-feather');
var conf = require('./conf.json');
var app = feather();
if('dev' == process.env.NODE_ENV){
  app.use(function *(){
    require('koa-static')('.')
  });
}
app.proxy = true;
require('./routes/dashboard')();
require('./routes/dt')();
app.listen(conf.port);
