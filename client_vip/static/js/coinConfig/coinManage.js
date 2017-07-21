/* Y币管理 */
require('../../scss/coinConfig/coinManage.scss');
import { getUserList } from '../model/coinConfig';
const $ = window.$;
require('../modules/cookie')($);
const keyadmin = $.cookie('ukey'); // key_admin
console.log(keyadmin);
const coinManage = {
  init() {
    this.initDom();
    this.getUserList();
    this.initEvent();
  },
  initDom() {
    this.$tbody = $('tbody');
    this.$searchbtn = $('.searchbtn'); // 搜索
    this.$checkbtn = $('.checkbtn'); // 查询
    this.$prev = $('.prev'); // 上一页
    this.$next = $('.next'); // 下一页
    this.$gopage = $('.gopage'); // 跳转页码
    this.$sure = $('.sure'); // 跳转确定
    this.page = 1;
    this.countpage = '';
    this.curtpage = 1;
  },
  initEvent() {
    // 搜索
    this.$searchbtn.on('click', () => {
      this.getUserList();
    });
    this.$checkbtn.on('click', () => {
      this.getUserList();
    });
    $(document).keydown((event) => {
      if (event.keyCode === 13) { // enter
        event.stopPropagation();
        event.preventDefault();
        this.getUserList();
      }
    });
    this.$prev.on('click', () => {
      if (!this.$prev.hasClass('keyactive')) {
        this.page--;
        this.getUserList();
      } else {
        return;
      }
      console.log(this.page);
    });
    this.$next.on('click', () => {
      if (!this.$next.hasClass('keyactive')) {
        this.page++;
        this.getUserList();
      } else {
        return;
      }
      console.log(this.page);
    });
    this.$gopage.on('change', () => {
      if (this.$gopage.val() > this.countpage) {
        this.$gopage.val(this.countpage);
      }
    });
    this.$sure.on('click', () => {
      this.page = this.$gopage.val();
      this.getUserList();
    });
  },
  getUserList() {
    getUserList({
      key_admin: keyadmin,
      page: this.page || 1,
      stime: $('.starTime').val(),
      etime: $('.endTime').val(),
      keyword: $('.searchinput').val(),
    }).then(json => {
      console.log(json);
      this.renderDom(json.data);
    }, json => {
      console.log(json);
      this.$tbody.html(`<tr><td>${json.msg}</td></tr>`);
    });
  },
  renderDom(data) {
    const list = data.data;
    let html = '';
    if (data.countall > 10) {                // 是否显示分页功能-总条数
      $('.pages').css({ display: 'block' });
    } else {
      $('.pages').css({ display: 'none' });
    }
    if (data.curpage >= data.pageall) {     // 下一页按钮设为灰色表示不可点击
      this.$next.addClass('keyactive');
      this.$prev.removeClass('keyactive');
    } else {
      this.$next.removeClass('keyactive');
    }
    if (data.curpage <= 1) {               // 上一页按钮设为灰色表示不可点击
      this.$prev.addClass('keyactive');
    }
    if (list.length > 0) {
      $('.totalpage').html(data.pageall); // 总页数
      $('.currentpage').html(data.curpage); // 当前页
      this.$gopage.val(data.curpage);
      this.countpage = data.pageall;
      $.map(list, (n, i) => {
        html += `<tr>
          <td class="bodyOrder">${i + 1}</td>
          <td><img src="${n.headimg.indexOf('http') >= 0 ? `${n.headimg}` : 'https://img.rtmap.com/o_1bdgpootk1r5urk1hv812ur1l1se.jpg'}" alt="默认图片"></td>
          <td>${n.nickname}</td>
          <td>${n.openid}</td>
          <td>${n.ycoin}</td>
          <td>${n.createtime}</td>
          <td class="bodyStatus"><a href="/coinConfig/coinRecord?userid=${n.id}&userimg=${encodeURIComponent(n.headimg.indexOf('http') >= 0 ? `${n.headimg}` : 'https://img.rtmap.com/o_1bdgpootk1r5urk1hv812ur1l1se.jpg')}&name=${escape(n.nickname)}&opid=${n.openid}">详情</a></td>
        </tr>`;
      });
      this.$tbody.html(html);
    } else {
      this.$tbody.html('<tr><td>无数据</td></tr>');
      $('.pages').css({ display: 'none' });
    }
  },
};
coinManage.init();
