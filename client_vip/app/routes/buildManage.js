var conf = require('../package');
var resource = conf.resource;

module.exports = function() {

  router.get('/buildManage/buildManage', function* (next) {

    yield this.render('buildManage', {
      partials: {
        buildManageSideb: 'buildManage_sideb',
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'buildManage/buildManage',
        title: '建筑物管理',
      },
      isbuildManage: true,
    });
  });
};
