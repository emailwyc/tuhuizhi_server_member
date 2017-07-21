const loading = require('rtloading');
require('../../scss/pushCoupon/pushCoupon.scss');
import { out } from '../modules/out.js';
import { getCouponList, delTagsOne, sendCoupon } from '../model/pushCoupon.js';
require('../bootstrap/modal');
const $ = window.$;
require('../modules/cookie')($);

const pushCoupon = {
  init() {
    this.page = {
      pagenum: 1,
    };
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      alert('未找到ukey');
      location.href = '/user/login';
      return;
    }
    this._getCouponList();
  },
  initDom() {
    this.$myModal = $('#myModal');
    this.$gridSystemModal = $('#gridSystemModal');
    this.$save = $('.save');
    this.$del = $('.del');
    this.$out = $('.out');
    this.$tbody = $('.table tbody');

    this.$total = $('.pager .total');
    this.$pagenum = $('.pager .pagenum');
    this.$currentpage = $('.pager .currentpage');
    this.$prev = $('.pager .prev');
    this.$next = $('.pager .next');
    this.$gopage = $('.pager .gopage');
    this.$gopageBtn = $('.pager .gopage_btn');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });

    this.$tbody.on('click', '.del-btn', (e) => {
      const couponId = $(e.target).attr('data-id');
      this.$delId = couponId;
    });

    this.$tbody.on('click', '.send-btn', (e) => {
      const couponId = $(e.target).next().next().attr('data-id');
      this.$sendId = couponId;
    });

    this.$save.on('click', () => {
      this._sendCoupon();
    });

    this.$del.on('click', () => {
      this._delTagsOne();
    });
  },
  _render(data) {
    $.each(data, (i, v) => {
      const temp = v.httpadd === '' ? v.activityid : v.httpadd;
      const tmp = `<tr>
        <td>${i + 1}</td>
        <td>${v.buildid}</td>
        <td>${v.buildname}</td>
        <td>${temp}</td>
        <td>
          <a href="javascript:;" class="send-btn" data-toggle="modal"
           data-target="#gridSystemModal">发送</a>
          <a href="pushCoupon/editCoupon?classid=${v.id}">编辑</a>
          <a href="javascript:;" class="del-btn" data-id="${v.id}" data-toggle="modal"
           data-target="#myModal">删除</a>
        </td>
      </tr>`;
      this.$tbody.append(tmp);
    });
  },
  _getCouponList() {
    getCouponList({
      key_admin: $.cookie('ukey'),
      page: this.page.pagenum,
    }).then((json) => {
      console.log(json);
      this._render(json.data.data);
      this.$total.text(`共${json.data.countall}条`);
      this.$currentpage.text(`当前第${json.data.curpage}页`);
      $('.pager').css('display', 'none');
    }).catch(err => {
      console.log(err);
      this.$tbody.html(`<tr><td colspan="4">${err.msg}</td></tr>`);
      $('.pager').css('display', 'none');
    });
  },
  _delTagsOne() {
    delTagsOne({
      key_admin: $.cookie('ukey'),
      id: this.$delId,
    }).then((json) => {
      this.$myModal.modal('hide');
      console.log(json);
      alert(json.msg);
      location.reload();
    }).catch(err => alert(err.msg));
  },
  _sendCoupon() {
    this.$gridSystemModal.modal('hide');
    loading.show();
    $('.weui_toast_content').text('正在发送中，该操作时间较长，请耐心等待');
    sendCoupon({
      key_admin: $.cookie('ukey'),
      id: this.$sendId,
    }).then((json) => {
      loading.hide();
      console.log(json);
      alert(json.msg);
    }).catch((err) => {
      loading.hide();
      alert(err.msg);
    });
  },
};

pushCoupon.init();
