var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/gowifi', function* (next) {
    yield this.render('wifi', {
      partials: {
				resourceSideb: 'wifi_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'wifi/wifi',
        title: '一键Wifi',
      },
      // resource: true,
      isWifi: true,
    });
  });

  router.get('/gowifi/wificonfig', function* (next) {
    yield this.render('wifi_config', {
      partials: {
				resourceSideb: 'wifi_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'wifi/wificonfig',
        title: '一键Wifi属性配置',
      },
      // resource: true,
      isWifiConfig: true,
    });
  });
};
