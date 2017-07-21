var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/peoplelist/userlist', function* (next) {

    yield this.render('userlist', {
      partials: {
				userSideb: 'user_sideb',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'peoplelist/userlist',
        title: '会员管理',
      },
      // useEditor: true,
      isUserList: true,
      key: this.query.key_admin,
    });
  });
  router.get('/peoplelist/rankconfig', function* (next) {

      yield this.render('rankconfig', {
        partials: {
  				userSideb: 'user_sideb',
  			},
        page: {
          staticBase: '//res.rtmap.com',
          version: resource.static,
          mode: resource.mode,
          id: 'peoplelist/rankconfig',
          title: '会员卡样级别配置',
        },
        // useEditor: true,
        isrankconfig: true,
        key: this.query.key_admin,
      });
    });

    router.get('/peoplelist/presented', function* (next) {

        yield this.render('presented', {
          partials: {
    				userSideb: 'user_sideb',
    			},
          page: {
            staticBase: '//res.rtmap.com',
            version: resource.static,
            mode: resource.mode,
            id: 'peoplelist/presented',
            title: '赠送积分配置',
          },
          // useEditor: true,
          isPresented: true,
          key: this.query.key_admin,
        });
      });

  router.get('/peoplelist/userdetails', function* (next) {

    yield this.render('userdetails', {
      partials: {
				userSideb: 'user_sideb',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'peoplelist/userdetails',
        title: '会员详情',
      },
      isUserList: true,
      cardno: this.query.cardno,
      // id: this.qurey.id,
    });
  });

  router.get('/peoplelist/newmember', function* (next) {

    yield this.render('management_newmember', {
      partials: {
				userSideb: 'user_sideb',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'peoplelist/newmember',
        title: '新建会员',
      },
      isUserList: true,
    });
  });

  router.get('/management/handbook', function* (next) {

    yield this.render('management_handbook', {
      partials: {
				managementSideb: 'management_sideb',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'management/handbook',
        title: '会员手册',
      },
      isHandbook: true,
    });
  });

  router.get('/management/edit', function* (next) {

    yield this.render('management_handbook_edit', {
      partials: {
        managementSideb: 'management_sideb',
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'management/edit',
        title: '会员手册编辑',
      },
      isHandbook: true,
      useEditor: true,
      id: this.query.id,
    });
  });

  router.get('/management/clause', function* (next) {

    yield this.render('management_clause', {
      partials: {
				managementSideb: 'management_sideb',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'management/clause',
        title: '服务条款',
      },
      useEditor: true,
      isClause: true,
    });
  });
  router.get('/management/kefu', function* (next) {

    yield this.render('kefu', {
      partials: {
				managementSideb: 'management_sideb',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'management/kefu',
        title: '客服管理',
      },
      useEditor: true,
      isService: true,
    });
  });

  router.get('/management/reply', function* (next) {

    yield this.render('reply', {
      partials: {
				managementSideb: 'management_sideb',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'management/reply',
        title: '回复',
      },
      useEditor: true,
      isService: true,
      id: this.query.id,
    });
  });

  router.get('/peoplelist/integrallog', function* (next) {

    yield this.render('integrallog', {
      partials: {
				userSideb: 'user_sideb',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'peoplelist/integrallog',
        title: '积分记录',
      },
      useEditor: true,
      isIntegrallog: true,
    });
  });
};
