require('../../scss/wxCoupon/wxCouponAttr.scss');
import { getAppletTitle, appletTitle } from '../model/wxCoupon';
import { out } from '../modules/out.js';
const $ = window.$;
require('../modules/cookie')($);

const wxCouponAttr = {
  init() {
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      alert('未找到ukey');
      location.href = '/user/login';
      return;
    }
    this._getAppletTitle();
  },
  initDom() {
    this.$out = $('.out');
    this.$title = $('.title-input');
    this.$subBtn = $('.sub-btn');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });

    this.$subBtn.on('click', () => {
      // 保存配置
      this._appletTitle();
    });
  },
  _getAppletTitle() {
    getAppletTitle({
      key_admin: $.cookie('ukey'),
    }).then((json) => {
      console.log(json);
      this.$title.val(json.data);
    }).catch(err => console.log(err));
  },
  _appletTitle() {
    appletTitle({
      key_admin: $.cookie('ukey'),
      content: this.$title.val(),
    }).then(json => {
      console.log(json);
      alert(json.msg);
    }).catch(err => console.log(err));
  },
};

wxCouponAttr.init();
