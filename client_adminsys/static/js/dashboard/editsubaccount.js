require('../../scss/dashboard/editsubaccount.scss');
import { getPayChildById, updatePayChild } from './model/';
import { out } from '../modules/out.js';
const $ = window.$;
const conf = window.conf;
const editsubaccount = {
  init() {
    this.initDom();
    this.initEvent();
    this.getPayChildById();
  },
  initDom() {
    this.$out = $('.out');
    this.$subtn = $('.subbtn');
    this.$tbody = $('.table tbody');
    this.$subheader = $('.sub-header');
    this.$subheader.html(`修改子商户详情 --> ${conf.name}`);
    this.$adminid = $('.adminid');
    this.$buildid = $('.buildid');
    this.$floor = $('.floor');
    this.$poino = $('.poi_no');
    this.$poiname = $('.poi_name');
    this.$payaccount = $('.payaccount');
    this.$msg = $('.msg');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$subtn.on('click', () => {
      this.updatePayChild();
    });
  },
  getPayChildById() {
    getPayChildById({
      ukey: $.cookie('ukey'),
      id: conf.subid,
    }).then(json => {
      console.log(json);
      const data = json.data;
      this.$adminid.html(conf.name);
      this.$buildid.val(data.buildid);
      this.$floor.val(data.floor);
      this.$poino.val(data.poi_no);
      this.$poiname.val(data.poi_name);
      this.$payaccount.val(data.pay_child_account);
    }, json => {
      console.log(json);
    });
  },
  updatePayChild() {
    updatePayChild({
      ukey: $.cookie('ukey'),
      id: conf.subid,
      buildid: this.$buildid.val(),
      floor: this.$floor.val(),
      poi_no: this.$poino.val(),
      poi_name: this.$poiname.val(),
      pay_child_account: this.$payaccount.val(),
    }).then(json => {
      console.log(json);
      this.$msg.html(json.msg);
      setTimeout(() => {
        location.reload();
      }, 1500);
    }, json => {
      console.log(json);
      this.$msg.html(json.msg);
    });
  },
};
editsubaccount.init();
