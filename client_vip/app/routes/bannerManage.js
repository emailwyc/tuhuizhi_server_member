var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/bannerManage', function* (next) {
    yield this.render('bannerManage_list', {
      partials: {
        bannerManageSideb: 'bannerManage_sideb',
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'bannerManage/list',
        title: 'banner广告列表',
      },
      isBannerManage: true,
    });
  });
  router.get('/bannerManage/add', function* (next) {
    yield this.render('bannerManage_add', {
      partials: {
        bannerManageSideb: 'bannerManage_sideb',
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'bannerManage/add',
        title: '添加广告',
      },
      isBannerManage: true,
      useEditor: true,
      id: this.query.id,
    });
  });
};
