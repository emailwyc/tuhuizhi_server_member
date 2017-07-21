var json2csv = require('json2csv');
var Base = require('../models/base');
module.exports = function() {
  router.get('/api/dt/base', function* (next) {
    var action = this.query.action;
    var service = this.query.service;
    var sDate = this.query.sdate;
    var eDate = this.query.edate;
    var key = this.query.key;
    var size = +(this.query.size || 10);
    var list = [];
    // 汇总全部数据
    if (service === '1000') {
      console.log([key, sDate, eDate]);
      list = yield Base.aggregate([
        {
          $match: {
            key: key,
            date: {
              $gte: new Date(sDate),
              $lt: new Date(eDate),
            },
          },
        },
        {
          $group: {
            _id: "$date",
            pv: {'$sum': "$pv"},
            totalpv: {'$sum': "$totalpv"},
            uv: {'$sum': "$uv"},
            totaluv: {'$sum': "$totaluv"},
          }
        },
        { $sort: { "_id" : -1} }
  		]).limit(size).exec();
      console.log(list);
    } else {
      list = yield Base.find({
        service: service,
        date: {
          $gte: new Date(sDate),
          $lt: new Date(eDate),
        },
      }).sort({ date: -1 }).limit(size).lean();
    }

    list.forEach((v) => {
      const date = service === '1000' ? v._id : v.date;
      const month = date.getMonth() + 1;
      const day = date.getDate();
      const d = date.getFullYear() +'-'+ (month < 10 ? '0' + month : month) +
        '-' + (day < 10 ? '0' + day : day);

      v.date = d;
    });

    if (action === 'export') {
        const fields = ['date', 'pv', 'totalpv', 'uv', 'totaluv'];
        const fieldNames = ['日期', '当天页面访问量', '累计页面访问量', '日页面访问人数', '累计页面访问人数'];
        console.log(list);
        try {
          const result = json2csv({ data: list, fields: fields, fieldNames: fieldNames });
          this.type = 'text/csv';
          this.attachment('base.csv');
          this.body = result;
        } catch (err) {
          console.error(err);
          yield next;
        }
        return;
    }

    this.body = {
      code: 200,
      data: {
        totalPage: 1,
        list,
      },
    };
  });
};
