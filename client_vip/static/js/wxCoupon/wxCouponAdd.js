/* 微信小程序配置-添加活动 */
require('../../scss/wxCoupon/wxCouponAdd.scss');
import { addActivity } from '../model/wxCoupon';
import { buildList } from '../model/buildManage';
const $ = window.$;
require('../modules/cookie')($);
const keyadmin = $.cookie('ukey'); // key_admin
console.log(keyadmin);
const wxCouponAdd = {
  init() {
    this.initDom();
    this.buildList();
    this.initEvent();
  },
  initDom() {
    this.$buildName = $('buildName');
    this.$submitBtn = $('.submitBtn'); // 提交
    this.$buildSelect = $('.buildSelect');
    this.$activeid = $('.activeid'); // 活动ID
  },
  initEvent() {
    this.$submitBtn.on('click', () => {
      if (this.$buildSelect.val() === '0') {
        $('.buildIdSign').html('*请选择您需要的建筑物名称');
        return;
      } else if (this.$activeid.val() === '') {
        $('.buildIdSign').html('');
        $('.activeSign').html('*请输入活动ID');
        // return;
      } else {
        $('.activeSign').html('');
        this.addActivity();
      }
    });
  },
  addActivity() {
    addActivity({
      key_admin: keyadmin,
      build_id: this.$buildSelect.val(),
      act_id: $('.activeid').val(),
      type_id: '1',
    }).then(json => {
      console.log(json);
      window.location.href = '/wxCoupon/wxCoupon';
    }, json => {
      console.log(json);
      alert(json.msg);
    });
  },
  buildList() {
    buildList({
      key_admin: keyadmin,
    }).then(json => {
      console.log(json);
      let options = '';
      $.map(json.data, n => {
        options += `<option value="${n.buildid}">${n.name}</option>`;
      });
      this.$buildSelect.append(options);
    }, json => {
      console.log(json);
    });
  },
};
wxCouponAdd.init();
