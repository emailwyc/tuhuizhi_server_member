require('../../scss/dashboard/subcolumn.scss');
require('../bootstrap/modal');
import { out } from '../modules/out.js';
import { subcolumninsert, subcolumnlist, subcolumnsave, subcolumnonce } from './model';
console.log(out);
const $ = window.$;
const conf = window.conf;
const subcolumn = {
  init() {
    this.initDom();
    this.initEvent();
    this.catalogversion();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
  },
  initDom() {
    this.$subheader = $('.sub-header');
    this.$subheader.html('<a href="/dashboard/functionentry">功能入口</a>>子栏目模板');
    this.$tbody = $('tbody');
    this.$search = $('.form-control');
    this.$searchbtn = $('.btn');
    this.$out = $('.out');
    this.$category = $('.category');
    this.$vername = $('.vername');
    this.$vernum = $('.vernum');
    this.$gourl = $('.gourl');
    this.$description = $('.description');
    this.$addversion = $('.addversion');
    this.$category1 = $('.category1');
    this.$vername1 = $('.vername1');
    this.$vernum1 = $('.vernum1');
    this.$gourl1 = $('.gourl1');
    this.$description1 = $('.description1');
    this.$addversion1 = $('.addversion1');
    this.$verid = $('.verid');
  },
  initEvent() {
    // this.$searchbtn.on('click', () => {
    //   this.adminList();
    // });
    this.$tbody.on('click', '.up', (e) => {
      const $td = $(e.target).parent().prevAll();
      // this.$category1.val($td.eq(3).html());
      this.$vername1.val($td.eq(2).html());
      // this.$vernum1.val($td.eq(5).html());
      // this.$gourl1.val($td.eq(2).html());
      // this.$description1.val($td.eq(3).html());
      this.$verid.val($td.eq(3).html());
      $('#myModal1').modal('show');
      console.log($(e.target).parent().prevAll());
    });
    this.$tbody.on('click', '.del', (e) => {
      const id = $(e.target).parent().prevAll().eq(3).html();
      this.delversion(id);
    });
    this.$out.on('click', () => {
      out();
    });
    this.$addversion.on('click', () => {
      this.addversion();
    });
    this.$addversion1.on('click', () => {
      this.upversion();
    });
  },
  catalogversion() {
    subcolumnlist({
      ukey: $.cookie('ukey'),
      catalog_id: conf.id,
    }).then(json => {
      console.log(json);
      let html = '';
      $.each(json.data, (i, n) => {
        html += `<tr>
        <td>${n.id}</td>
        <td>${n.name}</td>
        <td>${n.datetime}</td>
        <td>${n.status - 0 ? '启用' : '删除'}</td>
        <td><a href="javascript:void(0)" class="up">${n.status - 0 ? '更新' : ''}</a>
         <a href="javascript:void(0)" class="del">${n.status - 0 ? '删除' : '恢复'}</a>
        </tr>`;
      });
      this.$tbody.html(html);
    }, json => {
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
  addversion() {
    subcolumninsert({
      ukey: $.cookie('ukey'),
      catalog_id: conf.id,
      name: this.$vername.val(),
      // url: this.$gourl.val(),
      // desc: this.$description.val(),
    }).then(json => {
      console.log(json);
      $('#myModal').modal('hide');
      alert('添加成功');
      this.catalogversion();
    }, (json) => {
      alert(`添加失败:${json.msg}`);
    });
  },
  upversion() {
    subcolumnsave({
      ukey: $.cookie('ukey'),
      // type_id: conf.id,
      id: this.$verid.val() - 0,
      name: this.$vername1.val(),
      // url: this.$gourl1.val(),
      // desc: this.$description1.val(),
    }).then(json => {
      console.log(json);
      $('#myModal1').modal('hide');
      alert('更新成功');
      this.catalogversion();
    });
  },
  delversion(id) {
    subcolumnonce({
      ukey: $.cookie('ukey'),
      id,
      // type_id: conf.id,
    }).then(json => {
      console.log(json);
      // $('#myModal1').modal('hide');
      alert('操作成功');
      this.catalogversion();
    }, (json) => {
      alert(`操作成功:${json.msg}`);
    });
  },
};
subcolumn.init();
