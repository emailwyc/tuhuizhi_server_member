require('../../scss/wifi/wificonfig.scss');
import { confmemwifi, getwifi } from '../model/wifi';
const $ = window.$;
require('../modules/cookie')($);
const wificonfig = {
  init() {
    this.initDom();
    this.initEvent();
    this.getwifi();
  },
  initDom() {
    this.$subBtn = $('.subBtn');
  },
  initEvent() {
    this.$subBtn.on('click', () => {
      this.confmemwifi();
    });
  },
  confmemwifi() {
    confmemwifi({
      key_admin: $.cookie('ukey'),
      is_mem: $('.form-group').find('input[name="ismem"]:checked').val(),
    }).then(json => {
      console.log(json);
      alert(json.msg);
    }, json => {
      console.log(json);
    });
  },
  getwifi() {
    getwifi({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      $('.form-group').find(`input[name="ismem"][value="${json.data.function_name.is_mem}"]`)
      .attr('checked', true);
    }, json => {
      console.log(json);
    });
  },
};
wificonfig.init();
