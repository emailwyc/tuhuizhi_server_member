require('../../scss/parking/associationConfig.scss');
import { getCarRelationlimit, editCarRelationlimit } from '../model/parking.js';
const $ = window.$;
require('../modules/cookie')($);
import { out } from '../modules/out.js';

const associationConfig = {
  init() {
    this.initDom();
    this.initEvent();
    this._getCarRelationlimit();
  },
  initDom() {
    this.$out = $('.out');
    this.$carNum = $('#example-number-input');
    this.$exchangeRadios1 = $('#exchangeRadios1');
    this.$exchangeRadios2 = $('#exchangeRadios2');
    this.$exchangeRadios = $('.form-check-input:radio[name=exchangeRadios]');
    this.$subBtn = $('.subBtn');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });

    this.$subBtn.on('click', () => {
      this._editCarRelationlimit();
    });
  },
  _getCarRelationlimit() {
    getCarRelationlimit({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      if (json.data.limit) {
        this.$exchangeRadios1.attr('checked', true);
        this.$carNum.val(json.data.limit);
      } else {
        this.$exchangeRadios2.attr('checked', true);
      }
    }).catch(err => {
      alert(err.msg);
    });
  },
  _editCarRelationlimit() {
    let limit = 0;
    const id = this.$exchangeRadios.filter(':checked').val();
    if (id === '1') {
      limit = this.$carNum.val();
      if (!limit || limit == 0) {
        alert('请输入关联车辆数');
        return;
      }
    }
    editCarRelationlimit({
      key_admin: $.cookie('ukey'),
      limit,
    }).then(json => {
      console.log(json);
      alert(json.msg);
    }).catch(err => {
      alert(err.msg);
    });
  },
};

associationConfig.init();
