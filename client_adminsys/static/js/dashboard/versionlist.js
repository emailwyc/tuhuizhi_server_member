require('../../scss/dashboard/versionlist.scss');
import { getversion, setversion,
   catalogliststatus, catalogversiontwo, catalogversionlist, adminversion } from './model/index';
const $ = window.$;
const conf = window.conf;
const versionlist = {
  init() {
    this.initDom();
    this.initEvent();
    // this.getversion();
    this.catalogliststatus();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
  },
  initDom() {
    this.$subheader = $('.sub-header');
    this.$subheader.html(`版本管理-->${conf.name}`);
    this.$subbtn = $('.subbtn');
    this.$nameid = $('.nameid');
    this.$appid = $('.appid');
    this.$buildid = $('.buildid');
    this.$msg = $('.msg');
    this.$nameid.val(conf.id);
    // this.$golookcarurl = $('.golookcarurl');
    // this.$gosmallstore_curl = $('.gosmallstore_curl');
    // this.$gosmallstore_burl = $('.gosmallstore_burl');
    // this.$goactivityurl = $('.goactivityurl');
    // this.$gowebsiteurl = $('.gowebsiteurl');
    // this.$goscorestoreurl = $('.goscorestoreurl');
    // this.$goscoreaddurl = $('.goscoreaddurl');
    // this.$goscoretransferurl = $('.goscoretransferurl');
    // this.$gocardbagurloneurl = $('.gocardbagurloneurl');
    // this.$gocardbagurltwourl = $('.gocardbagurltwourl');
    // this.$gocarpayurl = $('.gocarpayurl');
    // this.$goquestionurl = $('.goquestionurl');
    this.$gourl = $('.gourl');
    // 后添加
    this.$version = $('.version');
    this.$submbtn = $('.submbtn');
    this.$list = $('.list');
  },
  initEvent() {
    this.$subbtn.on('click', () => {
      this.setversion();
    });
    this.$version.on('change', () => {
      this.$gourl.html(this.arry[this.$version.val()]);
      this.catalogversiontwo();
    });
    this.$submbtn.on('click', () => {
      this.adminversion();
    });
  },
  getversion() {
    getversion({
      ukey: $.cookie('ukey'),
      classes: 'member',
    }).then(json => {
      console.log(json);
      let html = '';
      $.each(json.data, (i, n) => {
        html += `<option value="${n.id}">${n.name}</option>`;
      });
      this.$buildid.html(html);
      this.getshopversion();
    }, json => {
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
  setversion() {
    setversion({
      ukey: $.cookie('ukey'),
      classes: 'member',
      version_id: this.$buildid.val(),
      adminid: conf.id,
    }).then(json => {
      console.log(json);
      alert('修改成功');
      this.getversion();
    }, json => {
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
      alert(`${json.msg}`);
    });
  },
  // getshopversion() {
  //   getshopversion({
  //     ukey: $.cookie('ukey'),
  //     classes: 'member',
  //     adminid: conf.id,
  //     domain: window.location.hostname,
  //   }).then(json => {
  //     console.log(json);
  //     const html = $(`[value=${json.data.version_id}]`);
  //     const text = html.attr('selected', true).html();
  //     html.html(`* ${text}`);
  //     this.$gourl.html(json.data.memburl);
  //     this.$golookcarurl.html(json.data.lookcarsurl);
  //     this.$gosmallstore_curl.html(json.data.smallstore_c_url);
  //     this.$gosmallstore_burl.html(json.data.smallstore_b_url);
  //     this.$goactivityurl.html(json.data.activityurl);
  //     this.$gowebsiteurl.html(json.data.websiteurl);
  //     this.$goscorestoreurl.html(json.data.scorestoreurl);
  //     this.$goscoreaddurl.html(json.data.scoreaddurl);
  //     this.$goscoretransferurl.html(json.data.scoretransferurl);
  //     this.$gocardbagurloneurl.html(json.data.cardbagurloneurl);
  //     this.$gocardbagurltwourl.html(json.data.cardbagurltwourl);
  //     this.$gocarpayurl.html(json.data.carpayurl);
  //     this.$goquestionurl.html(json.data.questionurl);
  //   }, json => {
  //     if (json.code === 1001) {
  //       alert('登录超时请重新登录');
  //       location.href = '/dashboard/login';
  //     }
  //   });
  // },
  catalogliststatus() {
    catalogliststatus({
      ukey: $.cookie('ukey'),
      admin_id: conf.id,
    }).then(json => {
      console.log(json);
      let html = '';
      this.arry = [];
      $.each(json.data, (i, n) => {
        html += `<option value="${n.id}" data-url="${n.url}">${n.name}</option>`;
        this.arry[n.id] = n.url;
      });
      this.$version.html(html);
      this.catalogversiontwo();
      this.$gourl.html(this.arry[this.$version.val()]);
      // this.getshopversion();
    }, json => {
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
  catalogversiontwo() {
    catalogversiontwo({
      ukey: $.cookie('ukey'),
      catalog_id: this.$version.val(),
      admin_id: conf.id,
    }).then(json => {
      console.log(json);
      let html = '';
      $.each(json.data, (i, n) => {
        html += `<option value="${n.id}">${n.name}</option>`;
      });
      this.$list.html(html);
      this.catalogversionlist();
    }, json => {
      if (json.code === 102) {
        this.$list.html('<option value="">没有数据</option>');
      }
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
  catalogversionlist() {
    catalogversionlist({
      ukey: $.cookie('ukey'),
      type_id: this.$version.val(),
      admin_id: conf.id,
      // domain: window.location.hostname,
    }).then(json => {
      console.log(json);
      const html = $(`.list [value=${json.data.id}]`);
      const text = html.attr('selected', true).html();
      html.html(`* ${text}`);
    }, json => {
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
  adminversion() {
    adminversion({
      ukey: $.cookie('ukey'),
      catalog_id: this.$version.val(),
      version_id: this.$list.val(),
      admin_id: conf.id,
    }).then(json => {
      console.log(json);
      alert('修改成功');
      this.catalogversiontwo();
    }, json => {
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
      alert(`${json.msg}`);
    });
  },
};
versionlist.init();
