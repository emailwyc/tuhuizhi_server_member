require('../../scss/parking/configurelist.scss');
require('../bootstrap/modal');
const catalogId = location.href.indexOf('vip.rtmap.com') > 0 ||
 location.href.indexOf('vipvs2.rtmap.com') > 0 ? '3' : '9';
// import { out } from '../modules/out.js';
import { getSquaredList, delSquared } from '../model/parking.js';
const $ = window.$;
const main = {
  init() {
    this.initDom();
    this.initEvent();
    this.getSquaredList();
    this.state = {
      ctg: {},
    };
  },
  initDom() {
    this.$table = $('.table');
    this.$tbody = $('.table tbody');
    this.$delOk = $('.delOk');
    this.$msg = $('.msg');
    this.$myModal = $('#myModal');
    this.$layoutbox = $('.layoutbox');
  },
  initEvent() {
    this.$tbody.on('click', ('a.del'), (e) => {
      const target = $(e.target);
      this.state.ctg = {
        id: target.data('id'),
      };
    });
    this.$delOk.on('click', () => {
      this.delSquared();
    });
    this.$tbody.on('click', ('a.see'), (e) => {
      const target = $(e.target);
      alert(target.data('url'));
    });
  },
  getSquaredList() {
    getSquaredList({
      key_admin: $.cookie('ukey'),
      catalog_id: catalogId,
    }).then(json => {
      let tr = '';
      $.each(json.data, (i, v) => {
        tr += `<tr><td>${v.order}</td><td>${v.title}</td>
        <td><img src="${v.logo}" class="imglogo" /></td><td>
        <a href="javascript:;" data-url="${v.url}" class="see">查看</a></td>
        <td>${v.content}</td>
        <td>
        <a href="/parking/configure?id=${v.id}" class="edit">编辑</a>
        <a href="#" class="del" data-toggle="modal" data-target="#myModal"
        data-id="${v.id}">删除</a>
        </td></tr>`;
      });
      this.$tbody.html(tr);
    }, json => {
      this.$tbody.html(`<tr><td colspan="6">${json.msg}</td></tr>`);
    });
  },
  delSquared() {
    delSquared({
      key_admin: $.cookie('ukey'),
      sid: this.state.ctg.id,
    }).then(() => {
      main.getSquaredList();
    }, json => {
      alert(json.msg);
      this.$myModal.modal('hide');
    });
  },
};
main.init();
