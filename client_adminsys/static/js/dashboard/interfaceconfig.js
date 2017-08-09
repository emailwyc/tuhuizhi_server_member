// 接口配置
require('../../scss/dashboard/interfaceconfig.scss');
import { out } from '../modules/out.js';
import { adminList } from './model';
const hogan = require('hogan.js');
const tplconfiglist = require('./tpls/interfaceconfiglist.html');
const $ = window.$;
const interfaceconfig = {
  init() {
    this.initDom();
    this.initEvent();
    this.adminList();
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
  },
  initEvent() {
    this.$searchbtn.on('click', () => {
      this.adminList();
    });

    this.$out.on('click', () => {
      out();
    });
  },
  adminList() {
    adminList({
      ukey: $.cookie('ukey'),
      search: this.$search.val(),
    }).then(json => {
      console.log(json);
      const interconfiglist = hogan.compile(tplconfiglist);
      this.$tbody.html(interconfiglist.render({ list: json }));
    }, json => {
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
};
interfaceconfig.init();
