var conf = require('../package');
var resource = conf.resource;
var elasticsearch = require('elasticsearch');
var client = new elasticsearch.Client({
  host: 'localhost:9200',
  log: 'trace'
});

module.exports = function() {

  router.get('/dt/uv', function* (next) {
    var uv = yield client.search({
      body: {
        aggs: {
          ipv: {
            cardinality: {
              field: 'clientip.raw',
            },
          },
        },
      },
    });
    this.body = uv;
  });

  router.get('/dt/pv', function* (next) {
    if (!this.query.d) {
      var pvs = yield client.search({
        q: 'dt_t:pageview',
        body: {
          aggs: {
            pvs: {
              date_histogram: {
                script: 'doc["dt_id"].value',
                interval: 'day',
                format: 'MM-dd',
              },
            },
          },
        },
      });
      console.log(pvs);
      return;
    }
    var date = this.query.d ? new Date(this.query.d) : new Date();
		var month = date.getMonth() + 1;
		var day = date.getDate();
		date = date.getFullYear() +'.'+ (month < 10 ? '0' + month : month) + '.' + (day < 10 ? '0'+ day : day);

    var pv = yield client.search({
      index: 'logstash-' + date,
      type: 'logs',
      q: 'dt_t:pageview',
      body: {
        aggs: {
          pvs: {
            terms: {
              field: 'dt_id.raw',
            },
          },
        },
      },
    });
    // this.body = pv.aggregations.pvs.buckets;
    yield this.render('dt_pv', {
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'dt/pv',
        title: 'pv状态',
      },
      list: pv.aggregations.pvs.buckets,
    });
  });

  router.get('/dt/ev', function* (next) {
    var date = new Date(this.query.d);
		var month = date.getMonth() + 1;
		var day = date.getDate();
		date = date.getFullYear() +'.'+ (month < 10 ? '0' + month : month) + '.' + (day < 10 ? '0'+ day : day);

    var pv = yield client.search({
      index: 'logstash-' + date,
      type: 'logs',
      q: 'dt_t:event',
      body: {
        aggs: {
          pvs: {
            terms: {
              field: 'dt_eventAction.raw',
            },
          },
        },
      },
    });

    // this.body = pv.aggregations.pvs.buckets;
    yield this.render('dt_ev', {
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'dt/ev',
        title: '事件',
      },
      list: pv.aggregations.pvs.buckets,
    });
  });

  router.get('/dt/api', function* (next) {
    var data = yield client.search({
      q: 'dt_t:api',
      sort: '@timestamp:desc',
      // body: {
      //   query: {
      //     match: {
      //       dt_t: 'api',
      //     },
      //     sort: [{
      //       timestamp: { order: 'desc' },
      //     }],
      //   },
      // },
    });

    yield this.render('dt_api', {
      page: {
        staticBase: '//res.rtmap.com',
        version: resource.static,
        mode: resource.mode,
        id: 'dt/api',
        title: 'api状态',
      },
      list: data.hits.hits,
    });
  });
};
