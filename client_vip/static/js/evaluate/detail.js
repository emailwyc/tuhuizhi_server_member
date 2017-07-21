require('../../scss/evaluate/detail.scss');
require('../bootstrap/modal');
import { getEvalList, getStaffOne } from '../model/evaluate.js';
const $ = window.$;
const conf = window.conf;
const main = {
  init() {
    this.page = 1;
    this.initDom();
    this.initEvent();
    this.getStaffOne();
    this.getEvalList();
  },
  initDom() {
    this.$tbody = $('.table tbody');
    this.$searchBtn = $('#searchBtn');
    this.$startTime = $('.start-time');
    this.$endTime = $('.end-time');
    this.$personPic = $('.person-pic');
    this.$personName = $('.person-name');
    this.$personId = $('.person-id');
    this.$personTel = $('.person-tel');

    this.pageBox = $('.page_box');
    this.$gopage = $('.gopage');
    this.$currentppage = $('.currentpage');
    this.$total = $('.total');
    this.$next = $('.next');
    this.$prev = $('.prev');
    this.$gopage_btn = $('.gopage_btn');
  },
  initEvent() {
    this.$searchBtn.on('click', () => {
      const startTime = this.$startTime.val();
      if (!startTime) {
        alert('请选择开始时间');
        return false;
      }

      const endTime = this.$endTime.val();
      if (!endTime) {
        alert('请选择结束时间');
        return false;
      }
      this.page = 1;
      return this.getEvalList();
    });

    this.$prev.on('click', () => {
      if (this.page === 1) {
        alert('已经是第一页了');
      } else {
        this.page --;
        this.getEvalList();
      }
    });
    this.$next.on('click', () => {
      console.log(this.page);
      if (this.page === this.totalPage) {
        alert('已经是最后一页了');
      } else {
        this.page ++;
        this.getEvalList();
      }
    });
    this.$gopage_btn.on('click', () => {
      if (this.$gopage.val() > this.totalPage) {
        alert('已超过总页数！');
        this.$gopage.val('');
      } else {
        this.page = parseInt(this.$gopage.val(), 10);
        this.getEvalList();
      }
    });
  },
  tagsFun(tags) {
    const tags1 = [];
    if (tags.length > 0) {
      $.each(tags, (i, item) => {
        tags1.push(item.name);
      });
    }
    return tags1.join('、');
  },
  getEvalList() {
    getEvalList({
      key_admin: $.cookie('ukey'),
      number: conf.number,
      page: this.page,
      startDate: this.$startTime.val() ? this.$startTime.val() : '',
      endDate: this.$endTime.val() ? this.$endTime.val() : '',
    }).then(result => {
      let tr = '';
      $.each(result.data.data, (i, v) => {
        tr += `<tr><td>${v.createtime}</td><td>${v.star}</td>
        <td><p class="class-name-p overfl">${main.tagsFun(v.tags)}</p></td>
        <td>${v.nickname}</td>
        <td>${v.message}</td></tr>`;
      });
      this.$tbody.html(tr);

      if (result.data.data.length > 0 && result.data.pageall > 1) {
        this.pageBox.css('display', 'block');
        this.$currentppage.html(`当前第${result.data.curpage}页`);
        this.$total.html(`共${result.data.pageall}页`);
        this.totalPage = result.data.pageall;
        this.$gopage.val('');
      } else {
        this.pageBox.css('display', 'none');
      }
      this.$gopage.val('');
    }, error => {
      this.$tbody.html(`<tr><td colspan="5">${error.msg}</td></tr>`);
      this.pageBox.css('display', 'none');
    });
  },
  getStaffOne() {
    getStaffOne({
      key_admin: $.cookie('ukey'),
      staff_id: conf.id,
    }).then((result) => {
      this.$personPic.attr('src', result.data.avatar);
      this.$personName.html(result.data.name);
      this.$personId.html(result.data.number);
      this.$personTel.html(result.data.mobile);
    }, error => {
      alert(error.msg);
    });
  },
};
main.init();
