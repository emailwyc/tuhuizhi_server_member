require('../../scss/dashboard/index.scss');
const hogan = require('hogan.js');
const tplmarketlist = require('./tpls/marketlist.html');
import { adminList, enable, savePwd } from './model';
import { out } from '../modules/out.js';
const $ = window.$;
const storage = localStorage;
const conf = window.conf;
const list = {
  init() {
    this.initDom();
    this.initEvent();
    this.list();
    this.page = 1;
    if (!$.cookie('ukey')) {
      alert('登录超时请重新登录');
      location.href = '/dashboard/login';
      return;
    }
  },
  initDom() {
    this.$out = $('.out');
    this.search = $('.form-control');
    this.$tbody = $('tbody');
    this.$btn = $('.btn');
    this.$enable = $('.enable');
    this.$describe = $('.describe');

    this.pageBox = $('.page_box');
    this.$gopage = $('.gopage');
    this.$currentppage = $('.currentpage');
    this.$total = $('.total');
    this.$next = $('.next');
    this.$prev = $('.prev');
    this.$gopage_btn = $('.gopage_btn');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$describe.on('click', () => {
      storage.setItem('describe', conf.describe);
    });
    this.$btn.on('click', () => {
      this.list();
    });
    $('.table').on('click', 'a.resetpwd', (e) => {
      const $target = $(e.target);
      console.log($target);
      e.preventDefault();
      savePwd({
        ukey: $.cookie('ukey'),
        id: $target.data('id'),
      }).then(json => {
        console.log(json);
        alert('密码重置成功');
      }, json => {
        console.log(json);
        if (json.code === 1001) {
          alert('登录超时请重新登录');
          location.href = '/dashboard/login';
        }
      });
    });
    $('.table').on('click', 'a.enable', (e) => {
      const $target = $(e.target);
      console.log($target.html() === '已启用' ? '0' : '1');
      e.preventDefault();
      enable({
        ukey: $.cookie('ukey'),
        id: $target.data('id'),
        enable: $target.html() === '已启用' ? '0' : '1',
      }).then((json) => {
        console.log(json);
        $target.html($target.html() === '已启用' ? '未启用' : '已启用');
      }, (json) => {
        console.log(json);
        if (json.code === 1001) {
          alert('登录超时请重新登录');
          location.href = '/dashboard/login';
        }
      });
    });
    this.$prev.on('click', () => {
      if (this.page === 1) {
        alert('已经是第一页了');
      } else {
        this.page --;
        this.list(this.page);
      }
    });

    this.$next.on('click', () => {
      console.log(this.page);
      if (this.page === this.totalPage) {
        alert('已经是最后一页了');
      } else {
        this.page ++;
        this.list(this.page);
      }
    });

    this.$gopage_btn.on('click', () => {
      if (this.$gopage.val() > this.totalPage) {
        alert('已超过总页数！');
        this.$gopage.val('');
      } else {
        this.page = this.$gopage.val();
        this.list(this.$gopage.val());
      }
    });
  },
  list(pageNum) {
    adminList({
      ukey: $.cookie('ukey'),
      search: this.search.val(),
      page: pageNum || '1',
    }).then(json => {
      console.log(json);
      const tpllist = hogan.compile(tplmarketlist);
      $.each(json.data, (i, v) => {
        const dt = v;
        if (dt.enable === '1') {
          dt.enable = '已启用';
        } else {
          dt.enable = '未启用';
        }
      });
      this.$tbody.html(tpllist.render({ list: json.data }));
      console.log(json.count_page);
      if (json.data.length > 0 && json.count_page > 1) {
        this.pageBox.css('display', 'block');
        this.$currentppage.html(`当前第${json.page}页`);
        this.$total.html(`共${json.count_page}页`);
        this.totalPage = json.count_page;
        this.$gopage.val('');
      } else {
        this.pageBox.css('display', 'none');
      }
    }, json => {
      console.log(json);
      if (json.code === 102) {
        alert('找不到相关数据');
      }
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      }
    });
  },
};
list.init();
