var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/coinConfig/coinConfig', function* (next) {

    yield this.render('coinConfig', {
      partials: {
				coinConfigSide: 'coinConfig_sideb',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'coinConfig/coinConfig',
        title: 'Y币配置',
      },
      iscoinConfig: true,
    });
  });
  router.get('/coinConfig/coinManage', function* (next) {

    yield this.render('coinManage', {
      partials: {
				coinManageSide: 'coinConfig_sideb',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'coinConfig/coinManage',
        title: 'Y币管理',
      },
      iscoinManage: true,
    });
  });
  router.get('/coinConfig/coinRecord', function* (next) {

    yield this.render('coinRecord', {
      partials: {
				coinManageSide: 'coinConfig_sideb',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'coinConfig/coinRecord',
        title: 'Y币记录',
      },
      iscoinManage: true,
    });
  });
};
