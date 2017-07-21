var feather = require('koa-feather');
var app = feather();
var port = require('./conf').port;
if('dev' == process.env.NODE_ENV){
  app.use(function *(){
    require('koa-static')('.');
  });
}
app.proxy = true;
var mongoose = require('mongoose');
mongoose.Promise = global.Promise;
// NODE_ENV="production" production
var mongoURI = process.env.NODE_ENV === 'production' ?
'mongodb://root:RTMAP_YX_911@dds-2zebd96007f184742.mongodb.rds.aliyuncs.com:3717,dds-2zebd96007f184741.mongodb.rds.aliyuncs.com:3717/{db}?replicaSet=mgset-1067899&authSource=admin' :
'mongodb://127.0.0.1:27017/{db}';
global.db = mongoose.createConnection(mongoURI.replace(/\{db\}/gi, 'dt'), { read_secondary: true });
db.on('error', console.error.bind(console, 'connection error:'));
require('./routes/mall')();
require('./routes/user')();
require('./routes/member')();
require('./routes/resource')();
require('./routes/point')();
require('./routes/parking')();
require('./routes/statistics')();
require('./routes/membermanagement')();
require('./routes/pointdonation')();
require('./routes/dt')();
require('./routes/survey')();
require('./routes/micromall')();
require('./routes/taxi')();
require('./routes/message')();
require('./routes/wifi')();
require('./routes/evaluate')();
require('./routes/lookcars')();
require('./routes/coinConfig')();
require('./routes/Yadmin')();
require('./routes/buildManage')();
require('./routes/wxCoupon')();
require('./routes/childAccount')();
require('./routes/pushCoupon')();
require('./routes/bannerManage')();
require('./routes/pageview')();
app.listen(port);
