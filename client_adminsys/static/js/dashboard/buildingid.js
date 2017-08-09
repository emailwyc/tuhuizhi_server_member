require('../../scss/dashboard/buildingid.scss');
import { getBuildAndAppid, editBuildAndAppid } from './model/index';
const $ = window.$;
const conf = window.conf;
const buildingid = {
  init() {
    this.initDom();
    this.initEvent();
    this.getBuildAndAppid();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
  },
  initDom() {
    this.$subheader = $('.sub-header');
    this.$subheader.html(`资源绑定-->${conf.name}`);
    this.$subbtn = $('.subbtn');
    this.$nameid = $('.nameid');
    this.$appid = $('.appid');
    this.$buildid = $('.buildid');
    this.$subpayacc = $('.subpayacc');
    this.$op = $('.op');
    this.$msg = $('.msg');
    this.$nameid.val(conf.id);
    this.$applet = $('.wxxappid');
    this.$alipay = $('.aliappid');
  },
  initEvent() {
    this.$subbtn.on('click', () => {
      this.editBuildAndAppid();
    });
  },
  getBuildAndAppid() {
    getBuildAndAppid({
      ukey: $.cookie('ukey'),
      admin_id: conf.id,
    }).then(json => {
      console.log(json);
      // this.$nameid.val(json.data.id);
      this.$appid.val(json.data.wechat_appid);
      this.$applet.val(json.data.applet_appid);
      this.$alipay.val(json.data.alipay_appid);
      this.$subpayacc.val(json.data.subpayacc);
      this.$op.val(json.data.op);
      const buildid = json.data.buildid;
      if (buildid.indexOf('，') !== -1) {
        const build = buildid.replace('，', ',');
        this.$buildid.val(build);
      } else {
        this.$buildid.val(json.data.buildid);
      }
    }, json => {
      console.log(json);
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
  editBuildAndAppid() {
    editBuildAndAppid({
      ukey: $.cookie('ukey'),
      admin_id: conf.id,
      appid: this.$appid.val(),
      applet_appid: this.$applet.val(),
      alipay_appid: this.$alipay.val(),
      build_id: this.$buildid.val(),
      pay_account: this.$subpayacc.val(),
      op_id: this.$op.val(),
    }).then(json => {
      console.log(json);
      this.$msg.html(json.msg);
      location.reload();
    }, json => {
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
      console.log(json);
    });
  },
};
buildingid.init();
