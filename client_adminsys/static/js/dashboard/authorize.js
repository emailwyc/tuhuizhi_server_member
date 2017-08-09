require('../../scss/dashboard/authorize.scss');
import { weiList } from './model';
const $ = window.$;
const authorize = {
  init() {
    this.initDom();
    this.initEvent();
    this.authorityList();
  },
  initDom() {
    this.$table = $('.table tbody');
    this.$name = $('.name');
    this.$appid = $('.appid');
    this.$button = $('button');
    this.$pages = $('.pages');
    this.$totalpage = $('.totalpage');
    this.$prev = $('.prev');
    this.$currentpage = $('.currentpage');
    this.$next = $('.next');
    this.$gopage = $('.gopage');
    this.$sure = $('.sure');
    this.page = 1;
    this.countpage = '';
    this.curtpage = 1;
  },
  initEvent() {
    this.$button.on('click', () => {
      console.log(this.$name.val(), this.$appid.val());
      this.authorityList();
    });
    this.$prev.on('click', () => {
      if (this.page > 1) {
        this.page--;
        this.authorityList();
      } else {
        this.page = 1;
        alert('已经是第一页了');
      }
      console.log(this.page);
    });
    this.$next.on('click', () => {
      if (this.page < this.countpage) {
        this.page++;
        this.authorityList();
      } else {
        this.page = this.countpage;
        alert('已经是最后一页了');
      }
      console.log(this.page);
    });
    this.$sure.on('click', () => {
      this.page = this.$gopage.val();
      console.log(this.page);
      this.authorityList();
    });
    this.$gopage.on('change', () => {
      if (this.$gopage.val() > this.countpage) {
        this.$gopage.val(this.countpage);
      }
    });
  },
  authorityList() {
    weiList({
      ukey: $.cookie('ukey'),
      page: this.page || 1,
      lines: 10,
      name: this.$name.val() || '',
      appid: this.$appid.val() || '',
    }).then(json => {
      console.log(json);
      this.countpage = json.count_page;
      this.$currentpage.html(json.page);
      this.$totalpage.html(json.count_page);
      if (json.count >= 10) {
        this.$pages.css({ display: 'block' });
      } else {
        this.$pages.css({ display: 'none' });
      }
      let td = '';
      $.each(json.wechatdata, (i, v) => {
        td += `<tr data-id="${v.id}"><td>${v.nick_name}</td><td>${v.appid}</td>
        <td>${v.authorization_info}</td><td class="td_img"><img src="${v.head_img}" /></td>
        <td>${v.createtime}</td></tr>`;
        this.$table.html(td);
      });
    }, json => {
      console.log(json);
      if (json.code === 1001) {
        alert('登录超时请重新登录');
        location.href = '/dashboard/login';
      } else {
        this.$pages.css({ display: 'none' });
        this.$table.html(json.msg);
      }
    });
  },
};
authorize.init();
