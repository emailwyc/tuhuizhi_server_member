require('../../scss/evaluate/evaluate.scss');
require('../bootstrap/modal');
// import { out } from '../modules/out.js';
import { getClassList, delClassOne } from '../model/evaluate.js';
const $ = window.$;
const main = {
  init() {
    this.initDom();
    this.initEvent();
    this.getClassList();
    this.state = {};
  },
  initDom() {
    this.$tbody = $('.table tbody');
    this.$delOk = $('.delOk');
    this.$msg = $('.msg');
    this.$myModal = $('#myModal');
  },
  initEvent() {
    this.$tbody.on('click', ('a.del'), (e) => {
      main.$myModal.modal('show');
      const target = $(e.target);
      this.state.id = target.data('id');
    });
    this.$delOk.on('click', () => {
      this.delClassOne();
    });
  },
  tagsFun(tags) {
    const tags1 = [];
    const tags2 = [];
    if (tags.length > 0) {
      $.each(tags, (i, item) => {
        if (item.star >= 1 && item.star <= 3) {
          tags1.push(item.name);
        } else {
          tags2.push(item.name);
        }
      });
    }
    return `<td><p class="calss-space overfl">${tags1.join('、')}</p></td>
    <td><p class="calss-space overfl">${tags2.join('、')}</p></td>`;
  },
  getClassList() {
    getClassList({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      let tr = '';
      $.each(json.data, (i, v) => {
        tr += `<tr><td>${v.name}</td><td>${v.count}</td>${main.tagsFun(v.tags)}
        <td><a href="/evaluate/staff?fromClassName=${v.id}" class="edit">查看员工</a>
        <a href="/evaluate/add?id=${v.id}" class="edit">编辑</a>
        <a href="#" class="del delete${v.id}" data-toggle="modal" data-id="${v.id}">删除</a>
        </td></tr>`;
      });
      this.$tbody.html(tr);
    }, json => {
      this.$tbody.html(`<tr><td colspan="6">${json.msg}</td></tr>`);
    });
  },
  delClassOne() {
    delClassOne({
      key_admin: $.cookie('ukey'),
      class_id: this.state.id,
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
