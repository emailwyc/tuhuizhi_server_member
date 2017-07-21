var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/lookcars/lookcars', function* (next) {

    yield this.render('lookcars', {
      partials: {
				lookcarsSide: 'lookcars_side',
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'lookcars/lookcars',
        title: '停车寻车',
      },
      isLookcars: true,
    });
  });
};
