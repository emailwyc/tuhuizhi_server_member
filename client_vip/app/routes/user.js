var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/user/login', function* (next) {

    yield this.render('user_login', {
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'user/login',
        title: '登录',
      },
      isLoginPage: true,
    });
  });
  router.get('/user/admin', function* (next) {

    yield this.render('user_admin', {
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'user/admin',
        title: '用户管理',
      },
      isAdmin: true,
    });
  });
  router.get('/welcome', function* (next) {

    yield this.render('welcome', {
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'user/welcome',
        title: '用户管理',
      },
      isAdmin: true,
    });
  });
};
