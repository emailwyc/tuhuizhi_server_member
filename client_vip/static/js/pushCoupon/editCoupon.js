const loading = require('rtloading');
require('../../scss/pushCoupon/editCoupon.scss');
import { out } from '../modules/out.js';
import { buildList } from '../model/buildManage';
import { editClassOne, getOne } from '../model/pushCoupon.js';
const $ = window.$;
require('../modules/cookie')($);
const conf = window.conf;

const editCoupon = {
  init() {
    this.initDom();
    this.initEvent();
    this._buildList();
  },
  initDom() {
    this.$out = $('.out');
    this.$build = $('.build');
    this.$activeRadios = $('.form-check-input:radio[name=activeRadios]');
    this.$erp = $('.erp');
    this.$erpRadio = $('#erpRadio');
    this.$activeRadio = $('#activeRadio');
    this.$activity = $('.activity');
    this.$pushRadios = $('.form-check-input:radio[name=pushRadios]');
    this.$radios1 = $('#pushRadios1');
    this.$radios2 = $('#pushRadios2');
    this.$textarea = $('#openidTextarea');
    this.$subBtn = $('.subBtn');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });

    if (conf.classid === '') {
      this.$activeRadio.attr('checked', true);
      this.$erp.attr('disabled', true);
      this.$radios1.attr('checked', true);
      this.$textarea.attr('disabled', true);
    }
    this.$activeRadios.on('change', (e) => {
      const ele = $(e.target).attr('id');
      if (ele === 'erpRadio') {
        this.$erp.attr('disabled', false);
        this.$activity.attr('disabled', true);
      } else {
        this.$erp.attr('disabled', true);
        this.$activity.attr('disabled', false);
      }
    });

    this.$pushRadios.on('change', (e) => {
      const ele = $(e.target).attr('id');
      if (ele === 'pushRadios1') {
        this.$textarea.attr('disabled', true);
      } else {
        this.$textarea.attr('disabled', false);
      }
    });

    this.$subBtn.on('click', () => {
      this._editClassOne();
    });
  },
  _buildList() {
    buildList({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      let options = '';
      $.map(json.data, n => {
        options += `<option value="${n.buildid}">${n.name}</option>`;
      });
      this.$build.append(options);
      this._getOne();
    }, json => {
      console.log(json);
    });
  },
  _getOne() {
    if (conf.classid === '') return;
    getOne({
      key_admin: $.cookie('ukey'),
      id: conf.classid,
    }).then((json) => {
      console.log(json);
      const data = json.data;
      const httpadd = data.httpadd;
      if (httpadd === '') {
        this.$activeRadio.attr('checked', true);
        this.$erp.attr('disabled', true);
        this.$activity.attr('disabled', false);
        this.$activity.val(data.activityid);
      } else {
        this.$erpRadio.attr('checked', true);
        this.$erp.attr('disabled', false);
        this.$activity.attr('disabled', true);
        this.$erp.val(httpadd);
      }
      const type = data.type;
      if (type === '0' || type === 0) {
        this.$radios1.attr('checked', true);
        this.$textarea.attr('disabled', true);
      } else {
        this.$radios2.attr('checked', true);
        this.$textarea.attr('disabled', false).val(data.openid);
      }
      const build = this.$build.find('option');
      build.each((i, v) => {
        const temp = $(v).val();
        if (temp === data.buildid) {
          $(v).attr('selected', true);
          return;
        }
      });
    }).catch(err => alert(err.msg));
  },
  _editClassOne() {
    loading.show();
    $('.weui_toast_content').text('数据提交中');
    const build = this.$build.find('option:selected');
    const buildId = build.val();
    const buildName = build.text();
    const activitys = this.$activeRadios.filter(':checked').val();
    const pushRadios = this.$pushRadios.filter(':checked');
    const type = pushRadios.val();
    let openId = '';
    if (type === 1 || type === '1') {
      openId = this.$textarea.val();
      if (openId.trim() === '') {
        loading.hide();
        alert('请填写openid');
        return;
      }
    }
    let activityId = '';
    let httpadd = '';
    if (activitys === 1 || activitys === '1') {
      activityId = this.$activity.val().trim();
      httpadd = '';
      if (activityId === '') {
        loading.hide();
        alert('请填写活动id');
        return;
      }
    } else {
      httpadd = this.$erp.val().trim();
      activityId = '';
      if (httpadd === '') {
        loading.hide();
        alert('请填写第三方地址');
        return;
      }
    }
    const par = {
      key_admin: $.cookie('ukey'),
      class_id: conf.classid,
      buildId,
      buildName,
      activityId,
      httpadd,
      type,
      openId,
    };
    editClassOne(par).then((json) => {
      loading.hide();
      console.log(json);
      alert(json.msg);
      location.href = '/pushCoupon';
    }).catch((err) => {
      loading.hide();
      alert(err.msg);
    });
  },
};

editCoupon.init();
