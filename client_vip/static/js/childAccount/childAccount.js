require('../../scss/childAccount/childAccount.scss');
import { getList, getBuildidAll, delAccountOne } from '../model/childAccount';
const $ = window.$;
require('../modules/cookie')($);
const buildid = {};
const childAccount = {
  init() {
    this.getBuildidAll();
    this.initDom();
    this.initEvent();
    this.state = {
      ctg: {},
    };
    this.pages = 1;
  },
  initDom() {
    this.$tbody = $('.table > tbody');
    this.$searchinput = $('.searchinput');
    this.$searchBtn = $('.search_btn');
    this.$prev = $('.prev');
    this.$next = $('.next');
    this.$gopageBtn = $('.gopage_btn');
    this.$gopage = $('.gopage');
  },
  initEvent() {
    this.$tbody.on('click', ('a.del'), (e) => {
      const target = $(e.target);
      console.log(target);
      this.state.ctg = {
        id: target.data('id'),
      };
      if (confirm('确定要删除吗')) {
        this.delAccountOne(this.state.ctg.id);
      } else {
        alert('已取消删除');
      }
    });
    this.$searchBtn.on('click', () => {
      this.getList();
    });

    this.$prev.on('click', () => {
      if (this.pages === 1) {
        alert('已经是第一页了');
      } else {
        this.pages --;
        this.getList();
      }
    });
    this.$next.on('click', () => {
      if (this.pages === this.pageall) {
        alert('已经是第一页了');
      } else {
        this.pages ++;
        this.getList();
      }
    });
    this.$gopageBtn.on('click', () => {
      if (this.$gopage.val() > this.pageall) {
        alert('已超过总页数！');
        this.$gopage.val('');
      } else {
        this.pages = parseInt(this.$gopage.val(), 10);
        this.getList();
      }
    });
  },
  // 获取账户列表
  getList() {
    getList({
      key_admin: $.cookie('ukey'),
      page: this.pages,
      keyword: this.$searchinput.val(),
    }).then(json => {
      console.log(json);
      let tr = '';
      $.each(json.data.data, (i, v) => {
        tr += `<tr><td>${v.id}</td><td>${v.name}</td>
        <td class="buildid">${buildid[v.buildid]}</td><td>
        <a href="/childAccount/addChildAccount?id=${v.id}" class="edit">编辑</a>
        <a href="javascript:;" class="del" data-id="${v.id}">删除</a></td></tr>`;
      });
      this.$tbody.html(tr);
      if (json.data.data.length > 0 && json.data.pageall > 1) {
        $('.pager').css('display', 'block');
        this.pageall = json.data.pageall;
        $('.total').html(`总共${this.pageall}页`);
        $('.currentpage').html(`当前第${json.data.curpage}页`);
      } else {
        $('.pager').css('display', 'none');
      }
    }, json => {
      console.log(json);
    });
  },
  // 获取该商户下所有buildid信息
  getBuildidAll() {
    getBuildidAll({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      $.each(json.data, (i, v) => {
        buildid[v.id] = v.name;
      });
      this.getList();
    }, json => {
      console.log(json);
    });
  },
  delAccountOne(id) {
    delAccountOne({
      key_admin: $.cookie('ukey'),
      accid: id,
    }).then(json => {
      console.log(json);
      alert(json.msg);
      this.getList();
    }, json => {
      console.log(json);
    });
  },
};
childAccount.init();
