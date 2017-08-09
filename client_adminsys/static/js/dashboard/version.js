require('../../scss/dashboard/version.scss');
require('../bootstrap/modal');
import { out } from '../modules/out.js';
import { getversion, addversion, upversion, delversion } from './model';
console.log(out);
const $ = window.$;
const version = {
  init() {
    this.initDom();
    this.initEvent();
    this.getversion();
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
  },
  initDom() {
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
      this.$category1.val($td.eq(2).html());
      this.$vername1.val($td.eq(3).html());
      this.$vernum1.val($td.eq(4).html());
      this.$gourl1.val($td.eq(1).html());
      this.$description1.val($td.eq(2).html());
      this.$verid.val($td.eq(4).html());
      $('#myModal1').modal('show');
      console.log($(e.target).parent().prevAll());
    });
    this.$tbody.on('click', '.del', (e) => {
      const id = $(e.target).parent().prevAll().eq(4).html();
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
  getversion() {
    getversion({
      ukey: $.cookie('ukey'),
      classes: 'member',
    }).then(json => {
      console.log(json);
      let html = '';
      $.each(json.data, (i, n) => {
        html += `<tr>
        <td>${n.id}</td>
        <td>${n.name}</td>
        <td>${n.desc}</td>
        <td>${n.url}</td>
        <td>${n.datetime}</td>
        <td><a href="javascript:void(0)" class="up">更新</a>
         <a href="javascript:void(0)" class="del">删除</a>
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
    addversion({
      ukey: $.cookie('ukey'),
      classes: 'member',
      name: this.$vername.val(),
      code: '',
      url: this.$gourl.val(),
      desc: this.$description.val(),
    }).then(json => {
      console.log(json);
      $('#myModal').modal('hide');
      alert('添加成功');
      this.getversion();
    }, (json) => {
      alert(`添加失败:${json.msg}`);
    });
  },
  upversion() {
    upversion({
      ukey: $.cookie('ukey'),
      classes: 'member',
      id: this.$verid.val(),
      name: this.$vername1.val(),
      code: '',
      url: this.$gourl1.val(),
      desc: this.$description1.val(),
    }).then(json => {
      console.log(json);
      $('#myModal1').modal('hide');
      alert('更新成功');
      this.getversion();
    });
  },
  delversion(id) {
    delversion({
      ukey: $.cookie('ukey'),
      id,
    }).then(json => {
      console.log(json);
      // $('#myModal1').modal('hide');
      alert('删除成功');
      this.getversion();
    }, (json) => {
      alert(`删除失败:${json.msg}`);
    });
  },
};
version.init();
