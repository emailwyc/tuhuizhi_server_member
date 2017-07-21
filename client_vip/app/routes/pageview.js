var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/pageview', function* (next) {
    yield this.render('pageview', {
      partials: {
        pageviewSideb: 'pageview_sideb',
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'pageview/pageview',
        title: '页面访问量',
      },
      isPageView: true,
    });
  });
};
