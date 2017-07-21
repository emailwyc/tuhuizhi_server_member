var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/tinymall/micromall', function* (next) {
    yield this.render('micro_mall', {
      partials: {
				microSideb: 'micro_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'micromall/micromall',
        title: 'style微商城',
      },
      // mall: true,
      // isAdmin: true,
      isClass: true,
    });
  });

  router.get('/tinymall/topad', function* (next) {
    yield this.render('micro_topad', {
      partials: {
				microSideb: 'micro_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'micromall/topad',
        title: 'style微商城',
      },
      // mall: true,
      // isAdmin: true,
      isClass: true,
      useEditor: true,
      position: this.query.position,
    });
  });
  router.get('/tinymall/newtopad', function* (next) {
    yield this.render('micro_newtopad', {
      partials: {
				microSideb: 'micro_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'micromall/newtopad',
        title: 'style微商城',
      },
      // mall: true,
      // isAdmin: true,
      isClass: true,
      useEditor: true,
      id: this.query.id,
      position: this.query.position,
    });
  });

  router.get('/tinymall/newbottom', function* (next) {
    yield this.render('micro_newbottom', {
      partials: {
        microSideb: 'micro_sideb'
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'micromall/newbottom',
        title: 'style微商城',
      },
      // mall: true,
      // isAdmin: true,
      isClass: true,
      useEditor: true,
      // status: this.query.status,
      position: this.query.position,
    });
  });

  router.get('/tinymall/facility', function* (next) {
    yield this.render('micro_facility', {
      partials: {
        microSideb: 'micro_sideb'
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'micromall/facility',
        title: '功能区',
      },
      // mall: true,
      // isAdmin: true,
      isClass: true,
      useEditor: true,
      position: this.query.position,
    });
  });
};
