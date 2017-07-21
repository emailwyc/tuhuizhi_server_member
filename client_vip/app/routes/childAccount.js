var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/childAccount', function* (next) {
    yield this.render('childAccount', {
      partials: {
				childAccountSideb: 'childAccount_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'childAccount/childAccount',
        title: '子账号管理',
      },
      isChildAccount: true,
    });
  });
  router.get('/childAccount/addChildAccount', function* (next) {
    yield this.render('add_childAccount', {
      partials: {
				childAccountSideb: 'childAccount_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'childAccount/addChildAccount',
        title: '子账号管理',
      },
      isChildAccount: true,
      id: this.query.id,
    });
  });
};
