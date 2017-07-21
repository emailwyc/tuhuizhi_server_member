var version = require('../app.json').version;

module.exports = () => {
  // 兼容 start
  // 旧版停车缴费，等奥永替换为新版后，需要
  // 1. 将原来电子会员里的停车代码移除
  // 2. redirect 移除
  router.get('/park/car', function* () {
    const key = this.query.key_admin;
    this.redirect(`/aypark/car?key_admin=${key}`);
  });
  // 兼容 end

  router.get('/park', function* () {
    // this.body = '停车缴费首页';
    yield this.render('index', {
      page: {
        staticBase: '//res.rtmap.com',
        version,
        id: 'index',
        title: '智能停车',
        dtid: '1005.1',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  router.get('/park/records', function* () {
    yield this.render('records', {
      page: {
        staticBase: '//res.rtmap.com',
        version,
        id: 'records',
        title: '停车缴费记录',
        dtid: '1005.2',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  router.get('/park/standard', function* () {
    yield this.render('standard', {
      page: {
        staticBase: '//res.rtmap.com',
        version,
        id: 'standard',
        title: '停车收费优惠标准',
        dtid: '1005.3',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  router.get('/park/mycar', function* () {
    yield this.render('mycar', {
      page: {
        staticBase: '//res.rtmap.com',
        version,
        id: 'mycar',
        title: '我的车',
        dtid: '1005.4',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 搜索自己的车排号，后续用绑定流程替换
  router.get('/park/search', function* () {
    yield this.render('search', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'search',
        title: '停车缴费',
        dtid: '1005.5',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 支付停车费
  router.get('/pay/park', function* () {
    const key = this.query.key_admin;
    // 兼容 start
    // 旧版停车缴费，等奥永替换为新版后，需要
    // 1. 将原来电子会员里的停车代码移除
    // 2. redirect 移除
    if (key === 'ad357006c826abc7555f0f7e8a5e5493') {
      this.redirect(`/pay/aypark?key_admin=${key}`);
      return;
    }
    // 兼容 end

    yield this.render('pay', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'pay',
        title: '停车缴费',
        dtid: '1005.6',
      },
      key,
      openid: this.query.openid,
    });
  });

  // 缴费记录详情
  router.get('/park/recordsinfo', function* () {
    yield this.render('recordsinfo', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'recordsinfo',
        title: '停车缴费记录',
        dtid: '1005.7',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 优惠卷
  router.get('/park/discount', function* () {
    yield this.render('discount', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'discount',
        title: '优惠券',
        dtid: '1005.8',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 西单
  // 缴费成功页
  router.get('/park/success', function* () {
    yield this.render('success', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'success',
        title: '缴费成功',
        dtid: '1005.9',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 我的优惠券
  router.get('/park/myticket', function* () {
    yield this.render('myticket', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'myticket',
        title: '我的优惠券',
        dtid: '1005.10',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 我的优惠券-转增停车券-已使用
  router.get('/park/shareticket', function* () {
    yield this.render('shareticket', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'shareticket',
        title: '转赠停车券',
        dtid: '1005.11',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 分享后打开页
  router.get('/park/share', function* () {
    yield this.render('share', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'share',
        title: '悦米',
        dtid: '1005.12',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 购买停车券列表页
  router.get('/park/buy', function* () {
    yield this.render('buy', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'buy',
        title: '购买停车券',
        dtid: '1005.13',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 购买条款
  router.get('/park/buyterms', function* () {
    yield this.render('buyterms', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'buyterms',
        title: '购买条款',
        dtid: '1005.14',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });


  // 购买停车券详情页-提交订单
  router.get('/park/buyticket', function* () {
    yield this.render('buyticket', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'buyticket',
        title: '购买停车券',
        dtid: '1005.15',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 购买-收银台
  router.get('/pay/cashierdesk', function* () {
    yield this.render('cashierdesk', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'cashierdesk',
        title: '收银台',
        dtid: '1005.16',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 购买成功
  router.get('/park/buysuccess', function* () {
    yield this.render('buysuccess', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'buysuccess',
        title: '购买成功',
        dtid: '1005.17',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 购买失败
  router.get('/park/buyerror', function* () {
    yield this.render('buyerror', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'buyerror',
        title: '购买失败',
        dtid: '1005.18',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 激活
  router.get('/park/activate', function* () {
    yield this.render('activate', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'activate',
        title: '激活',
        dtid: '1005.19',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 激活后打开页
  router.get('/park/activateopen', function* () {
    yield this.render('activateopen', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'activateopen',
        title: '激活',
        dtid: '1005.20',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 电子发票页
  router.get('/park/invoice', function* () {
    yield this.render('invoice', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'invoice',
        title: '停车券发票',
        dtid: '1005.21',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 电子发票详情页
  router.get('/park/invoicedetail', function* () {
    yield this.render('invoicedetail', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'invoicedetail',
        title: '停车券发票',
        dtid: '1005.22',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 我要开发票页
  router.get('/park/invoicing', function* () {
    yield this.render('invoicing', {
      page: {
        version,
        staticBase: '//res.rtmap.com',
        id: 'invoicing',
        title: '停车券发票',
        dtid: '1005.23',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });

  // 静态资源页
  router.get('/park/shareold', function* () {
    yield this.render('shareold', {
      page: {
        staticBase: '//res.rtmap.com',
        version,
        id: 'shareold',
        title: '分享-旧',
        dtid: '1005.24',
      },
      key: this.query.key_admin,
      openid: this.query.openid,
    });
  });
};
