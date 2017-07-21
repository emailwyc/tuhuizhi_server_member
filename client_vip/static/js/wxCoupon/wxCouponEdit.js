/* 微信小程序活动编辑 */
require('../../scss/wxCoupon/wxCouponEdit.scss');
import { editActivity } from '../model/wxCoupon';
import { buildList } from '../model/buildManage';
const $ = window.$;
require('../modules/cookie')($);
const keyadmin = $.cookie('ukey'); // key_admin
console.log(keyadmin);
const wxCouponEdit = {
  init() {
    this.initDom();
    this.buildList(this.buildid);
    this.initEvent();
  },
  initDom() {
    this.$buildName = $('buildName');
    this.$submitBtn = $('.submitBtn'); // 提交
    this.$buildSelect = $('.buildSelect');
    this.$activeid = $('.activeid'); // 活动ID
    this.buildid = this.getQueryString('buildid');
    this.activeid = this.getQueryString('activeid');
    this.id = this.getQueryString('id');
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
        this.editActivity();
      }
    });
  },
  editActivity() {
    editActivity({
      key_admin: keyadmin,
      build_id: this.$buildSelect.val(),
      act_id: $('.activeid').val(),
      id: this.id,
    }).then(json => {
      console.log(json);
      window.location.href = '/wxCoupon/wxCoupon';
    }, json => {
      console.log(json);
      alert('提交失败');
    });
  },
  buildList(buildid) {
    buildList({
      key_admin: keyadmin,
    }).then(json => {
      console.log(json);
      let options = '';
      $.map(json.data, n => {
        options += `<option value="${n.buildid}"
        ${buildid === n.buildid ? 'selected' : ''}>${n.name}</option>`;
      });
      this.$buildSelect.append(options);
      $('.activeid').val(this.activeid);
    }, json => {
      console.log(json);
    });
  },
  getQueryString(name) {
    const reg = new RegExp(`(^|&)${name}=([^&]*)(&|$)`);
    const r = window.location.search.substr(1).match(reg);
    if (r !== null) {
      return unescape(r[2]);
    }
    return null;
  },
};
wxCouponEdit.init();
