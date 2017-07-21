
const conf = require('../package');
const resource = conf.resource;

module.exports = function() {
  router.get('/point/integral', function* (next) {
    yield this.render('point', {
      partials: {
        pointSideb: 'point_sideb'
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'point/point',
        title: '积分补录',
      },
      point: true,
      pointSideb: true,
    });
  });
};
