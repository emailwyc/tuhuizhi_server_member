require('../../scss/survey/property.scss');
const $ = window.$;
require('../modules/cookie')($);
import { getBlackFilter, setBlackFilter } from '../model/survey';

const main = {
  init() {
    this.initDom();
    this.initEvent();
    this.getBlackFilter();
  },
  initDom() {
    this.$isSet = $('.is-set');
    this.$subBtn = $('.subBtn .btn'); // 提交
  },
  initEvent() {
    this.$subBtn.on('click', () => this.setBlackFilter());
  },
  getBlackFilter() {
    getBlackFilter({
      key_admin: $.cookie('ukey'),
    }).then((result) => {
      $(`#declareRadio${result.data.function_name}`).prop('checked', true);
    });
  },
  setBlackFilter() {
    setBlackFilter({
      key_admin: $.cookie('ukey'),
      is_filter: this.$isSet.find('input:checked').val(),
    }).then((result) => {
      alert(result.msg);
    }, error => {
      alert(error.msg);
    });
  },
};
main.init();
