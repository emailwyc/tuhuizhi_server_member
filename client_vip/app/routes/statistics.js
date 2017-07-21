var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/statistics/flow', function* (next) {
    yield this.render('statistics_flow', {
      partials: {
				statisticsSideb: 'statistics_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'statistics/flow',
        title: '流量统计'
      },
      // useEditor: true,
      // isMember: true,
      isFlow: true,
      // isRights: true,
    });
  });

  router.get('/statistics/surface', function* (next) {
    yield this.render('statistics_surface', {
      partials: {
				statisticsSideb: 'statistics_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'statistics/surface',
        title: '会员统计',
      },
      // useEditor: true,
      isSurface: true,
      // isAdmin: true,
      // isContact: true,
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
