var conf = require('../package');
var resource = conf.resource;

module.exports = function() {
  router.get('/resource', function* (next) {
    yield this.render('resource', {
      partials: {
				resourceSideb: 'resource_sideb'
			},
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'resource/resource',
        title: '静态资源列表',
      },
      // resource: true,
      isResource: true,
    });
  });
  router.get('/resource/resourceinfo', function* (next) {
    yield this.render('resource_info', {
      partials: {
        resourceSideb: 'resource_sideb'
      },
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'resource/resourceinfo',
        title: '添加静态资源',
      },
      useEditor: true,
      // resource: true,
      isResource: true,
    });
  });
};
