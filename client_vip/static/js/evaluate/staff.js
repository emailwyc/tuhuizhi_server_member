require('../../scss/evaluate/staff.scss');
require('../bootstrap/modal');
import { getStaffList, delStaffOne, getClassAll } from '../model/evaluate.js';
const $ = window.$;
require('../modules/qrcode')($);
const conf = window.conf;
const cPath = location.href.indexOf('vip.rtmap.com') > 0 || location.href.indexOf('vipvs2.rtmap.com') > 0 ? `https://h5.rtmap.com/evaluation?key_admin=${$.cookie('ukey')}` : `https://h2.rtmap.com/evaluation?key_admin=${$.cookie('ukey')}`;

const main = {
  init() {
    this.page = 1;
    this.initDom();
    this.initEvent();
    this.getClassAll();
    if (!conf.fromClassName) this.getStaffList();
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
    this.$classBox.on('change', () => {
      this.getStaffList();
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
  loadQrcode() {
    const cid = main.$classBox.find('option:selected').val();
    $.each($('.qrcode-number'), (ind, item) => {
      const text = `${cPath}&id=${$(item).attr('data-number')}&cId=${cid}`;
      $(item).qrcode({ width: 80, height: 80, text });
    });
  },
  getStaffList(val) {
    getStaffList({
      key_admin: $.cookie('ukey'),
      page: this.page,
      class_id: val ? conf.fromClassName : this.$classBox.find('option:selected').val(),
      keyword: this.$staffSearch.val(),
    }).then(result => {
      let tr = '';
      $.each(result.data.data, (i, v) => {
        tr += `<tr><td>${v.name}</td><td>${v.number}</td>
        <td class='qrcode-number' data-number='${v.number}'></td>
        <td>${v.comment.all}</td>
        <td>${v.comment['5']}</td><td>${v.comment['4']}</td>
        <td>${v.comment['3']}</td><td>${v.comment['2']}</td><td>${v.comment['1']}</td>
        <td>
        <a href="/evaluate/detail?id=${v.id}&number=${v.number}" class="edit">查看评价</a>
        </td></tr>`;
      });
      this.$tbody.html(tr);
      this.loadQrcode();
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
      this.$tbody.html(`<tr><td colspan="10">${json.msg}</td></tr>`);
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
  getClassAll() {
    getClassAll({
      key_admin: $.cookie('ukey'),
    }).then(result => {
      let html = '';
      $.each(result.data, (i, item) => {
        html += `<option value="${item.id}" ${(conf.fromClassName &&
           conf.fromClassName === item.id) ? 'selected="true"' : ''}">${item.name}</option>`;
      });
      this.$classBox.html(html);
      if (conf.fromClassName) this.getStaffList(conf.fromClassName);
    }, error => {
      if (error.code === 102) {
        if (conf.fromClassName) this.getStaffList(conf.fromClassName);
      } else {
        alert(error.msg);
      }
    });
  },
};
main.init();
