var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/member/rights', function* (next) {
    yield this.render('member_rights', {
      partials: {
				memberSideb: 'member_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'member/rights',
        title: '会员权益',
      },
      useEditor: true,
      // isMember: true,
      isAdmin: true,
      isRights: true,
    });
  });

  router.get('/member/contact', function* (next) {
    yield this.render('member_contact', {
      partials: {
				memberSideb: 'member_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'member/contact',
        title: '联系我们',
      },
      useEditor: true,
      isMember: true,
      // isAdmin: true,
      isContact: true,
    });
  });

  router.get('/member/card', function* (next) {
    yield this.render('member_card', {
      partials: {
				memberSideb: 'member_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'member/card',
        title: '会员卡样',
      },
      isMember: true,
      // isAdmin: true,
      isCard: true,
    });
  });

  router.get('/member/cardedit', function* (next) {
    yield this.render('member_cardedit', {
      partials: {
				memberSideb: 'member_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'member/cardedit',
        title: '会员卡样编辑',
      },
      isMember: true,
      // isAdmin: true,
      isCard: true,
      id: this.query.id,
      imgurl: this.query.imgurl,
    });
  });

  router.get('/member/follow', function* (next) {
    yield this.render('member_followcode', {
      partials: {
				memberSideb: 'member_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'member/follow',
        title: '微信关注二维码',
      },
      isMember: true,
      // isAdmin: true,
      isFollow: true,
    });
  });
  router.get('/member/icons', function* (next) {
    yield this.render('member_icons', {
      partials: {
				memberSideb: 'member_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'member/icons',
        title: '添加ICON',
      },
      isMember: true,
      // isAdmin: true,
      isIcons: true,
      id: this.query.id,
      title: this.query.title,
      logo: this.query.logo,
      url: this.query.url,
      order: this.query.order,
      isverify: this.query.isverify,
    });
  });
  router.get('/member/iconslist', function* (next) {
    yield this.render('member_iconslist', {
      partials: {
				memberSideb: 'member_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'member/iconslist',
        title: 'ICONlist',
      },
      isMember: true,
      // isAdmin: true,
      isList: true,
    });
  });
  router.get('/member/modifyidcard', function* (next) {
    yield this.render('member_modifyidcard', {
      partials: {
        memberSideb: 'member_sideb'
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'member/modifyidcard',
        title: '修改身份证',
      },
      isMember: true,
      // isAdmin: true,
      isModify: true,
    });
  });
  router.get('/member/resource', function* (next) {
    yield this.render('member_resource', {
      partials: {
				resourceSideb: 'resource_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'member/resource',
        title: '静态资源列表',
      },
      resource: true,
      isResource: true,
    });
  });
  router.get('/member/resourceinfo', function* (next) {
    yield this.render('member_resourceinfo', {
      partials: {
        resourceSideb: 'resource_sideb'
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'member/resourceinfo',
        title: '添加静态资源',
      },
      useEditor: true,
      resource: true,
      isResource: true,
    });
  });
  // 配置C端表单
  router.get('/member/formlist', function* (next) {
    yield this.render('member_formlist', {
      partials: {
				memberSideb: 'member_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'member/formlist',
        title: '配置C端表单',
      },
      // useEditor: true,
      isMember: true,
      isAdmin: true,
      isformlist: true,
    });
  });

  router.get('/member/scoreconfig', function* (next) {
    yield this.render('scoreconfig', {
      partials: {
				memberSideb: 'member_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'member/scoreconfig',
        title: '签到积分配置',
      },
      // useEditor: true,
      isMember: true,
      isAdmin: true,
      isScore: true,
    });
  });
};
