require('../../scss/dashboard/establishaccount.scss');
import { createPayChild } from './model/';
import { out } from '../modules/out.js';
const $ = window.$;
const conf = window.conf;
const establishaccount = {
  init() {
    this.initDom();
    this.initEvent();
  },
  initDom() {
    this.$out = $('.out');
    this.$tbody = $('.table tbody');
    this.$subbtn = $('.subbtn');
    this.$tbody = $('.table tbody');
    this.$subheader = $('.sub-header');
    this.$subheader.html(`添加子商户详情 --> ${conf.name}`);
    this.$adminid = $('.adminid');
    this.$buildid = $('.buildid');
    this.$floor = $('.floor');
    this.$poino = $('.poi_no');
    this.$poiname = $('.poi_name');
    this.$payaccount = $('.payaccount');
    this.$msg = $('.msg');
    this.$adminid.html(conf.name);
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$subbtn.on('click', () => {
      this.createPayChild();
    });
  },
  createPayChild() {
    createPayChild({
      ukey: $.cookie('ukey'),
      adminid: conf.id,
      buildid: this.$buildid.val(),
      floor: this.$floor.val(),
      poi_no: this.$poino.val(),
      poi_name: this.$poiname.val(),
      pay_child_account: this.$payaccount.val(),
    }).then(json => {
      console.log(json);
      this.$msg.html(json.msg);
      setTimeout(() => {
        // location.reload();
        location.href = `/dashboard/subaccount?name=${conf.name}&id=${conf.id}`;
      }, 1500);
    }, json => {
      console.log(json);
      this.$msg.html(json.msg);
    });
  },
};
establishaccount.init();
