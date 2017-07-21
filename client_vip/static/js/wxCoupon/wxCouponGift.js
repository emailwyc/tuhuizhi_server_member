require('../../scss/wxCoupon/wxCouponGift.scss');
import { buildidList } from '../model/mall';
import { propertyList, couponSave } from '../model/wxCoupon';
// import { out } from '../modules/out.js';
require('../bootstrap/modal');
const $ = window.$;
const keyAdmin = $.cookie('ukey');
require('../modules/cookie')($);
const wxCouponGift = {
  init() {
    this.initDom();
    this.initEvent();
    this.buildidList();
    this.status = {
      ctg: {},
    };
  },
  initDom() {
    this.$inputBuild = $('#inputBuild');
    this.$inputStatus = $('#inputStatus');
    this.$tbody = $('.table tbody');
    this.$sort = $('.sort');
    this.$save = $('.save');
    this.$msg = $('.msg');
  },
  initEvent() {
    this.$inputBuild.on('change', (e) => {
      const target = $(e.target);
      this.propertyList(target.val());
    });
    this.$inputStatus.on('change', (e) => {
      const target = $(e.target);
      this.propertyList(target.val());
    });
    this.$tbody.on('click', ('a.editBtn'), (e) => {
      const target = $(e.target);
      console.log(target.data('pid'));
      this.status.ctg = {
        pid: target.data('pid'),
      };
    });
    this.$save.on('click', () => {
      this.couponSave(this.status.ctg.pid);
    });
  },
  buildidList() {
    buildidList({
      key_admin: keyAdmin,
    }).then(json => {
      let option = '';
      $.each(json.data, (i, v) => {
        option += `<option value="${v.buildid}">${v.name}</option>`;
      });
      this.$inputBuild.html(option);
      this.propertyList();
    });
  },
  propertyList(buildid, status) {
    propertyList({
      key_admin: keyAdmin,
      buildid: this.$inputBuild.val() || buildid,
      status: this.$inputStatus.val() || status,
    }).then(json => {
      console.log(json);
      let tr = '';
      const Stuas = {
        2: '已领取',
        3: '已使用',
        5: '已过期',
      };
      if (json.data !== null) {
        $.each(json.data, (i, v) => {
          tr += `<tr><td>${v.sort ? v.sort : ''}</td><td><img src="${v.imgUrl}"/></td><td>${v.main}</td><td>${v.startTime}</td>
          <td>${v.endTime}</td><td>${Stuas[v.status] ? Stuas[v.status] : ''}</td><td>${v.num}</td><td>${v.issue}</td>
          <td><a href="javascript:;" data-pid="${v.pid}" data-toggle="modal" data-target="#myModal" class="editBtn">编辑</a></td>
          </tr>`;
        });
        this.$tbody.html(tr);
      } else {
        this.$tbody.html('<tr><td colspan="9">暂无数据</td></tr>');
      }
    }, json => {
      console.log(json);
    });
  },

  couponSave(pid) {
    couponSave({
      key_admin: keyAdmin,
      buildid: this.$inputBuild.val(),
      pid,
      sort: this.$sort.val(),
    }).then(json => {
      console.log(json);
      this.$msg.css('display', 'none');
      $('#myModal').modal('hide');
      this.propertyList();
    }, json => {
      console.log(json);
      if (this.$sort.val() === '') {
        this.$msg.css({ marginLeft: 0, display: 'block' }).html('排序不能为空');
      }
    });
  },
};
wxCouponGift.init();
