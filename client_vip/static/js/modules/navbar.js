import { jurisdictionList } from '../model';
const $ = window.$;
const storage = window.sessionStorage;
require('./cookie')($);
export const navBar = {
  init() {
    this.initDom();
    this.initEvent();
    this.status();
    // console.log(storage.name);
  },
  initDom() {
    this.$out = $('.out');
    this.$sidebar = $('.sidebar');
    this.$navbar = $('.navbar');
    this.$container = $('.container-fluid');
    this.$logoauthor = $('.logoauthor');
    this.$logoauthor.html(`${decodeURI($.cookie('name'))}管理平台`).css('color', '#e6e6e6');
  },
  qs(name) {
    const reg = new RegExp(`(^|&)${name}=([^&]*)(&|$)`);
    const r = window.location.search.substr(1).replace(/\?/g, '&').match(reg);
    if (r !== null) {
      return decodeURIComponent(r[2]);
    }
    return null;
  },
  initEvent() {
    this.$out.on('click', () => {
      if (confirm('确定要退出吗？')) {
        $.cookie('ukey', '', { path: '/' });
        location.href = '/user/login';
      }
    });

    this.$navbar = $('.navbar-nav');
    if (!this.$navbar[0]) return;
    jurisdictionList({
      key_admin: $.cookie('ukey'),
      childid: storage.getItem('childid'),
    }).then(json => {
      console.log(json);
      const list = json.data;

      let li = '';
      $.each(list, (i, v) => {
        const menu = v;
        menu.column_html = menu.column_html.replace(/\{key\}/g, $.cookie('ukey'));
        menu.childid_html = menu.column_html.replace(/\{childid\}/g, storage.getItem('childid'));
        const childid = storage.getItem('childid');
        const childid2 = childid !== '' ? childid : 'ismaster';
        menu.childid_html = menu.column_html.replace(/\{childid\}/g, childid2);
        let classN = '';
        if (location.pathname.slice(0, 5) === v.column_html.slice(0, 5)) {
          classN = 'active';
        }
        li += `<li class="nav-item ${classN}">
          <a class="nav-link" href="${v.childid_html}">${v.column_name}</a>
        </li>`;
        // li += `<li class="nav-item ${classN}">
        //   <a class="nav-link" href="${v.column_html}">${v.column_name}</a>
        // </li>`;

        this.$navbar.html(li);
      });
      const height = $('.navbar').height() - 0;
      const em = $('.navbar').css('font-size');
      const em1 = parseInt(em, 10);
      this.$sidebar.css('top', height + em1);
      this.$container.css('padding-top', height + em1 + 18);
    }, json => {
      console.log(json);
      if (json.code === 502) {
        alert(json.msg);
        location.href = '/user/login';
      }
    });
  },
  status() {
  },
};
