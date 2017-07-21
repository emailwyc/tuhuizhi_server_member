var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/parking/order', function* (next) {
    yield this.render('parking_order', {
      partials: {
				parkingSideb: 'Parking_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'parking/order',
        title: '订单管理'
      },
      // useEditor: true,
      // isMember: true,
      isOrder: true,
    });
  });

  router.get('/parking/discount', function* (next) {
    yield this.render('parking_discount', {
      partials: {
				parkingSideb: 'Parking_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'parking/discount',
        title: '优惠标准',
      },
      useEditor: true,
      isDiscount: true,
      // isAdmin: true,
      // isContact: true,
    });
  });

  router.get('/parking/setpark', function* (next) {
    yield this.render('parking_setpark', {
      partials: {
        parkingSideb: 'Parking_sideb'
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'parking/setpark',
        title: '停车缴费-设置',
      },
      isSetpark: true,
      // isAdmin: true,
      // isContact: true,
    });
  });

  router.get('/parking/configurelist', function* (next) {
    yield this.render('parking_configurelist', {
      partials: {
        parkingSideb: 'Parking_sideb'
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'parking/configurelist',
        title: '停车缴费-首页功能配置',
      },
      isConfigurelist: true,
      // isAdmin: true,
      // isContact: true,
    });
  });

  router.get('/parking/configure', function* (next) {
    yield this.render('parking_configure', {
      partials: {
        parkingSideb: 'Parking_sideb'
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'parking/configure',
        title: '停车缴费-首页功能添加',
      },
      isConfigurelist: true,
      id: this.query.id,
    });
  });

  router.get('/parking/associationConfig', function* (next) {
    yield this.render('parking_associationConfig', {
      partials: {
        parkingSideb: 'Parking_sideb'
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'parking/associationConfig',
        title: '车辆关联配置',
      },
      isAssociationConfig: true,
      id: this.query.id,
    });
  });

//   router.get('/member/card', function* (next) {
//     yield this.render('member_card', {
//       partials: {
// 				memberSideb: 'member_sideb'
// 			},
//       page: {
//         staticBase: '//res.rtmap.com',
//         version: resource.static,
//         mode: resource.mode,
//         id: 'member/card',
//         title: '会员卡样',
//       },
//       isMember: true,
//       // isAdmin: true,
//       isCard: true,
//     });
//   });
//
//   router.get('/member/follow', function* (next) {
//     yield this.render('member_followcode', {
//       partials: {
// 				memberSideb: 'member_sideb'
// 			},
//       page: {
//         staticBase: '//res.rtmap.com',
//         version: resource.static,
//         mode: resource.mode,
//         id: 'member/follow',
//         title: '微信关注二维码',
//       },
//       isMember: true,
//       // isAdmin: true,
//       isFollow: true,
//     });
//   });
};
