var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/pushCoupon', function* (next) {
    yield this.render('pushCoupon', {
      partials: {
				pushCouponSideb: 'pushCoupon_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'pushCoupon/pushCoupon',
        title: '活动劵配置',
      },
      pushCoupon: true,
      // isAdmin: true,
      isPushCoupon: true,
    });
  });

  router.get('/pushCoupon/editCoupon', function* (next) {
    yield this.render('pushCoupon_editCoupon', {
      partials: {
        pushCouponSideb: 'pushCoupon_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'pushCoupon/editCoupon',
        title: '编辑活动劵',
      },
      pushCoupon: true,
      // isAdmin: true,
      isPushCoupon: true,
      classid: this.query.classid,
    });
  });
};
