var feather = require('koa-feather');
var port = require('./app.json');
var app = feather();
if('dev' == process.env.NODE_ENV){
  app.use(require('koa-static')('.'));
}
app.proxy = true;
require('./routers/park')();
app.listen(port);
