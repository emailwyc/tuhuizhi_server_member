var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/evaluate', function* (next) {
    yield this.render('evaluate', {
      partials: {
				evaluateSideb: 'evaluate_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'evaluate/evaluate',
        title: '员工评价管理',
      },
      // resource: true,
      isEvaluate: true,
    });
  });

  router.get('/evaluate/label', function* (next) {
    yield this.render('evaluate_label', {
      partials: {
				evaluateSideb: 'evaluate_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'evaluate/label',
        title: '员工评价管理',
      },
      // resource: true,
      isLabel: true,
    });
  });

  router.get('/evaluate/staff', function* (next) {
    yield this.render('evaluate_staff', {
      partials: {
				evaluateSideb: 'evaluate_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'evaluate/staff',
        title: '员工评价管理',
      },
      fromClassName: this.query.fromClassName,
      isEvaluate: true,
    });
  });

  router.get('/evaluate/stafflist', function* (next) {
    yield this.render('evaluate_stafflist', {
      partials: {
				evaluateSideb: 'evaluate_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'evaluate/stafflist',
        title: '员工评价管理',
      },
      isStaff: true,
    });
  });

  router.get('/evaluate/add', function* (next) {
    yield this.render('evaluate_add', {
      partials: {
				evaluateSideb: 'evaluate_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'evaluate/add',
        title: '员工评价管理',
      },
      id: this.query.id,
      isEvaluate: true,
    });
  });

  router.get('/evaluate/detail', function* (next) {
    yield this.render('evaluate_detail', {
      partials: {
				evaluateSideb: 'evaluate_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'evaluate/detail',
        title: '员工评价管理',
      },
      id: this.query.id,
      number: this.query.number,
      isEvaluate: true,
    });
  });

  router.get('/evaluate/editlabel', function* (next) {
    yield this.render('evaluate_editlabel', {
      partials: {
				evaluateSideb: 'evaluate_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'evaluate/editlabel',
        title: '员工评价管理',
      },
      id: this.query.id,
      isLabel: true,
    });
  });

  router.get('/evaluate/edituser', function* (next) {
    yield this.render('evaluate_edituser', {
      partials: {
				evaluateSideb: 'evaluate_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'evaluate/edituser',
        title: '员工评价管理',
      },
      id: this.query.id,
      isStaff: true,
    });
  });
};
