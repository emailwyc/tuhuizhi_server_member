require('../../scss/evaluate/label.scss');
require('../bootstrap/modal');
import { getTagsList, delTagsOne, getClassAll } from '../model/evaluate.js';
const $ = window.$;
const main = {
  init() {
    this.page = 1;
    this.initDom();
    this.initEvent();
    this.getClassAll();
    this.getTagsList({ class_id: 0, star: 0 });
    this.state = {};
  },
  initDom() {
    this.$tbody = $('.table tbody');
    this.$delOk = $('.delOk');
    this.$msg = $('.msg');
    this.$myModal = $('#myModal');
    this.$classBox = $('.class-box');
    this.$starBox = $('.star-box');

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
      this.delTagsOne();
    });
    this.$classBox.on('change', () => {
      this.page = 1;
      this.getTagsList({ class_id: this.$classBox.find('option:selected').val(),
       star: this.$starBox.find('option:selected').val() });
    });
    this.$starBox.on('change', () => {
      this.page = 1;
      this.getTagsList({ class_id: this.$classBox.find('option:selected').val(),
       star: this.$starBox.find('option:selected').val() });
    });
    this.$prev.on('click', () => {
      if (this.page === 1) {
        alert('已经是第一页了');
      } else {
        this.page --;
        this.getTagsList();
      }
    });
    this.$next.on('click', () => {
      console.log(this.page);
      if (this.page === this.totalPage) {
        alert('已经是最后一页了');
      } else {
        this.page ++;
        this.getTagsList();
      }
    });
    this.$gopage_btn.on('click', () => {
      if (this.$gopage.val() > this.totalPage) {
        alert('已超过总页数！');
        this.$gopage.val('');
      } else {
        this.page = parseInt(this.$gopage.val(), 10);
        this.getTagsList();
      }
    });
  },
  getTagsList(search) {
    getTagsList({
      key_admin: $.cookie('ukey'),
      page: this.page,
      class_id: search.class_id,
      star: search.star,
    }).then(result => {
      let tr = '';
      $.each(result.data.data, (i, v) => {
        tr += `<tr><td>${v.order}</td><td>${v.name}</td>
        <td><p class="class-name-p overfl">${v.class_name.join('、')}</p></td>
        <td>${(v.star >= 1 && v.star <= 3) ? '3星及以下' : '4星及以上'}</td>
        <td>
        <a href="/evaluate/editlabel?id=${v.id}" class="edit">编辑</a>
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
      this.$tbody.html(`<tr><td colspan="5">${json.msg}</td></tr>`);
      this.pageBox.css('display', 'none');
    });
  },
  delTagsOne() {
    delTagsOne({
      key_admin: $.cookie('ukey'),
      tags_id: this.state.id,
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
      let html = '<option value="0">全部</option>';
      $.each(result.data, (i, item) => {
        html += `<option value="${item.id}">${item.name}</option>`;
      });
      this.$classBox.html(html);
    }, error => {
      if (error.code !== 102) {
        alert(error.msg);
      }
    });
  },
};
main.init();
