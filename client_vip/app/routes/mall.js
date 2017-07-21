var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/mall', function* (next) {
    yield this.render('mall', {
      partials: {
				mallSideb: 'mall_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'mall/mall',
        title: '积分商城',
      },
      mall: true,
      // isAdmin: true,
      isClass: true,
    });
  });

  router.get('/mall/campaign', function* (next) {
    yield this.render('mall_campaign', {
      partials: {
				mallSideb: 'mall_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'mall/campaign',
        title: '活动配置',
      },
      mall: true,
      // isAdmin: true,
      isCampaign: true,
    });
  });

  router.get('/mall/gift', function* (next) {
    yield this.render('mall_gift', {
      partials: {
				mallSideb: 'mall_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'mall/gift',
        title: '礼品管理',
      },
      mall: true,
      // isAdmin: true,
      isGift: true,
    });
  });

  router.get('/mall/editGift', function* (next) {
    yield this.render('mall_editGift', {
      partials: {
				mallSideb: 'mall_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'mall/editGift',
        title: '编辑礼品',
      },
      useEditor: true,
      mall: true,
      // isAdmin: true,
      isGift: true,
      id: this.query.id,
      buildid: this.query.buildid,
    });
  });

  router.get('/mall/exchange', function* (next) {
    yield this.render('mall_exchange', {
      partials: {
				mallSideb: 'mall_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'mall/exchange',
        title: '商城配置',
      },
      mall: true,
      // isAdmin: true,
      isExchange: true,
    });
  });

  router.get('/mall/scorelog', function* (next) {
    yield this.render('mall_scorelog', {
      partials: {
				mallSideb: 'mall_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'mall/scorelog',
        title: '兑换记录',
      },
      mall: true,
      // isAdmin: true,
      isScorelog: true,
    });
  });

  router.get('/mall/banner', function* (next) {
    yield this.render('mall_banner', {
      partials: {
				mallSideb: 'mall_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'mall/banner',
        title: 'Banner管理',
      },
      mall: true,
      // isAdmin: true,
      isBanner: true,
    });
  });

  router.get('/mall/addBanner', function* (next) {
    yield this.render('mall_addBanner', {
      partials: {
				mallSideb: 'mall_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'mall/addBanner',
        title: '添加Banner',
      },
      mall: true,
      id: this.query.id,
      // isAdmin: true,
      isBanner: true
    });
  });

//   router.get('/mall/integral', function* (next) {
//     yield this.render('integral', {
//       partials: {
// 				mallintegral: 'mall_integral'
// 			},
//       page: {
//         staticBase: '//res.rtmap.com',
//         version: resource.static,
//         mode: resource.mode,
//         id: 'mall/integral',
//         title: '积分补录',
//       },
//       mall: true,
//       // isGift: true,
//       isInteg: true,
//     });
//   });
};
