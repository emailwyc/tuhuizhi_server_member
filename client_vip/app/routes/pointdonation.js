var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/donationpoint/record', function* (next) {
    yield this.render('record', {
      partials: {
				pointDonationSideb: 'pointdonation_sideb',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'donationpoint/record',
        title: '积分转赠－－转赠记录',
      },
      // useEditor: true,
      donationSideb: true,
    });
  });

  router.get('/donationpoint/setpoint', function* (next) {
    yield this.render('setpoint', {
      partials: {
				pointDonationSideb: 'pointdonation_sideb',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'donationpoint/setpoint',
        title: '积分转赠－－转赠设置',
      },
      // useEditor: true,
      setPointSideb: true,
    });
  });
};
