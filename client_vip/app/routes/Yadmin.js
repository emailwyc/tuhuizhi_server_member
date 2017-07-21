var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/Yadmin', function* (next) {
    yield this.render('Y_admin', {
      partials: {
				YadminSideb: 'Y_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'Yadmin/Yadmin',
        title: 'Y币管理',
      },
      isGift: true,
    });
  });

  router.get('/Yadmin/activities', function* (next) {
    yield this.render('Y_activities', {
      partials: {
				YadminSideb: 'Y_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'Yadmin/activities',
        title: '活动ID',
      },
      isActivities: true,
    });
  });

  router.get('/Yadmin/Yexchange', function* (next) {
    yield this.render('Y_exchange', {
      partials: {
				YadminSideb: 'Y_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'Yadmin/Yexchange',
        title: '兑换记录',
      },
      isExchange: true,
    });
  });

  router.get('/Yadmin/banner', function* (next) {
    yield this.render('Y_banner', {
      partials: {
				YadminSideb: 'Y_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'Yadmin/banner',
        title: 'banner管理',
      },
      isBanner: true,
    });
  });

  router.get('/Yadmin/addBanner', function* (next) {
    yield this.render('Y_addBanner', {
      partials: {
				YadminSideb: 'Y_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'Yadmin/addBanner',
        title: '兑换记录',
      },
      isAddBanner: true,
      id: this.query.id,
    });
  });
};
