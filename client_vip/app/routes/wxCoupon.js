var conf = require('../package');
var resource = conf.resource;

module.exports = function() {

  router.get('/wxCoupon/wxCoupon', function* (next) {

    yield this.render('wxCoupon', {
      partials: {
        wxCouponSideb: 'wxCoupon_sideb',
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'wxCoupon/wxCoupon',
        title: '优惠券',
      },
      iswxCoupon: true,
    });
  });

  router.get('/wxCoupon/wxCouponAdd', function* (next) {

    yield this.render('wxCouponAdd', {
      partials: {
        wxCouponSideb: 'wxCoupon_sideb',
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'wxCoupon/wxCouponAdd',
        title: '优惠券',
      },
      iswxCoupon: true,
    });
  });

  router.get('/wxCoupon/wxCouponEdit', function* (next) {

    yield this.render('wxCouponEdit', {
      partials: {
        wxCouponSideb: 'wxCoupon_sideb',
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'wxCoupon/wxCouponEdit',
        title: '优惠券',
      },
      iswxCoupon: true,
    });
  });
  router.get('/wxCoupon/wxCouponAd', function* (next) {

    yield this.render('wxCouponAd', {
      partials: {
        wxCouponSideb: 'wxCoupon_sideb',
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'wxCoupon/wxCouponAd',
        title: '广告',
      },
      iswxCouponAd: true,
    });
  });
  router.get('/wxCoupon/wxCouponPay', function* (next) {

    yield this.render('wxCouponPay', {
      partials: {
        wxCouponSideb: 'wxCoupon_sideb',
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'wxCoupon/wxCouponPay',
        title: '支付明细',
      },
      iswxCouponPay: true,
    });
  });
  router.get('/wxCoupon/wxCouponAttr', function* (next) {
    yield this.render('wxCouponAttr', {
      partials: {
        wxCouponSideb: 'wxCoupon_sideb',
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'wxCoupon/wxCouponAttr',
        title: '属性配置',
      },
      iswxCouponAttr: true,
    });
  });

  router.get('/wxCoupon/wxCouponGift', function* (next) {
    yield this.render('wxCouponGift', {
      partials: {
        wxCouponSideb: 'wxCoupon_sideb',
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'wxCoupon/wxCouponGift',
        title: '优惠劵管理',
      },
      iswxCouponGift: true,
    });
  });
};
