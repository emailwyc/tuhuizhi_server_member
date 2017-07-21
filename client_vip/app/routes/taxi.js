var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/taxi', function* (next) {
    yield this.render('taxi', {
      partials: {
				taxiSideb: 'taxi_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'taxi/taxi',
        title: '积分打车',
      },
      // useEditor: true,
      // isMember: true,
      isOrder: true,
      // isRights: true,
    });
  });

  router.get('/taxi/taxiconfig', function* (next) {
    yield this.render('taxi_config', {
      partials: {
				taxiSideb: 'taxi_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'taxi/taxi',
        title: '打车配置',
      },
      // useEditor: true,
      // isMember: true,
      isConFig: true,
      // isRights: true,
    });
  });
};
