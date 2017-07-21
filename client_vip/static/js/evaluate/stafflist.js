require('../../scss/evaluate/stafflist.scss');
require('../bootstrap/modal');
import { getStaffList, delStaffOne } from '../model/evaluate.js';
const $ = window.$;
require('../modules/qrcode')($);

const main = {
  init() {
    this.page = 1;
    this.initDom();
    this.initEvent();
    this.getStaffList();
    this.state = {};
  },
  initDom() {
    this.$tbody = $('.table tbody');
    this.$delOk = $('.delOk');
    this.$msg = $('.msg');
    this.$myModal = $('#myModal');
    this.$classBox = $('.class-box');
    this.$staffSearch = $('#staff-search');
    this.$searchBtn = $('#searchBtn');


    this.pageBox = $('.page_box');
    this.$gopage = $('.gopage');
    this.$currentppage = $('.currentpage');
    this.$total = $('.total');
    this.$next = $('.next');
    this.$prev = $('.prev');
    this.$gopage_btn = $('.gopage_btn');
  },
  initEvent() {
    this.$tbody.on('click', ('a.del'), (e) => {
      main.$myModal.modal('show');
      const target = $(e.target);
      this.state.id = target.data('id');
    });
    this.$delOk.on('click', () => {
      this.delStaffOne();
    });
    this.$searchBtn.on('click', () => {
      if (!this.$staffSearch.val()) {
        alert('请输入要搜索的内容员工工号或者姓名');
        return false;
      }
      this.page = 1;
      return this.getStaffList();
    });

    this.$prev.on('click', () => {
      if (this.page === 1) {
        alert('已经是第一页了');
      } else {
        this.page --;
        this.getStaffList();
      }
    });
    this.$next.on('click', () => {
      console.log(this.page);
      if (this.page === this.totalPage) {
        alert('已经是最后一页了');
      } else {
        this.page ++;
        this.getStaffList();
      }
    });
    this.$gopage_btn.on('click', () => {
      if (this.$gopage.val() > this.totalPage) {
        alert('已超过总页数！');
        this.$gopage.val('');
      } else {
        this.page = parseInt(this.$gopage.val(), 10);
        this.getStaffList();
      }
    });
  },
  getStaffList() {
    getStaffList({
      key_admin: $.cookie('ukey'),
      page: this.page,
      class_id: 0,
      keyword: this.$staffSearch.val(),
    }).then(result => {
      let tr = '';
      $.each(result.data.data, (i, v) => {
        tr += `<tr><td>${v.name}</td><td>${v.number}</td>
        <td>${v.mobile}</td>
        <td>
        <a href="/evaluate/edituser?id=${v.id}" class="edit">编辑</a>
        <a href="#" class="del delete${v.id}" data-toggle="modal" data-id="${v.id}">删除</a>
        </td></tr>`;
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
    }, json => {
      this.$tbody.html(`<tr><td colspan="4">${json.msg}</td></tr>`);
      this.pageBox.css('display', 'none');
    });
  },
  delStaffOne() {
    delStaffOne({
      key_admin: $.cookie('ukey'),
      staff_id: this.state.id,
    }).then(() => {
      this.$myModal.modal('hide');
      $(`.delete${main.state.id}`).parents('tr').remove();
    }, error => {
      alert(error.msg);
      this.$myModal.modal('hide');
    });
  },
};
main.init();
