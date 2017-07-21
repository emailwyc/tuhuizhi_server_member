/* Y币管理 */
require('../../scss/coinConfig/coinRecord.scss');
import { getYcoinChangeList, addYcoinRecord } from '../model/coinConfig';
const $ = window.$;
require('../modules/cookie')($);
const keyadmin = $.cookie('ukey'); // key_admin
console.log(keyadmin);
const coinRecord = {
  init() {
    this.initDom();
    this.userinfo();              // 获取用户详情
    this.getYcoinChangeList();    // 获取用户Y币变更记录
    this.initEvent();
  },
  initDom() {
    this.$tbody = $('tbody');
    this.$userimg = $('.userimg'); // 用户头像
    this.$userName = $('.userName'); // 用户昵称
    this.$userOpenid = $('.userOpenid'); // 用户openID
    this.$back = $('.back'); // 返回键
    this.$headcheck = $('.headcheck'); // 查询键
    this.$headsubmit = $('.headsubmit'); // 提交变更
    this.$changeReason = $('.changeReason'); // 变更理由
    this.$prev = $('.prev'); // 上一页
    this.$next = $('.next'); // 下一页
    this.$gopage = $('.gopage'); // 跳转页码
    this.$sure = $('.sure'); // 跳转确定
    this.userid = this.getQueryString('userid');
    this.page = 1;  // 当前页初始值
    this.countpage = ''; // 总页数
    this.curtpage = 1;  // 当前页
  },
  initEvent() {
    // 返回
    this.$back.on('click', () => {
      window.location.href = '/coinConfig/coinManage';
    });
    this.$headsubmit.on('click', () => {
      if ($('.changeReason').val() !== '') {
        this.addYcoinRecord($('.changeReason').val());
      } else {
        $('.changeReason').focus();
        return;
      }
    });
    this.$headcheck.on('click', () => {
      this.getYcoinChangeList();
    });
    $(document).keydown((event) => {
      if (event.keyCode === 13) { // enter
        event.stopPropagation();
        event.preventDefault();
        this.getYcoinChangeList();
      }
    });
    // 上一页
    this.$prev.on('click', () => {
      if (!this.$prev.hasClass('keyactive')) {
        this.page--;
        this.getYcoinChangeList();
      } else {
        return;
      }
      console.log(this.page);
    });
    // 下一页
    this.$next.on('click', () => {
      if (!this.$next.hasClass('keyactive')) {
        this.page++;
        this.getYcoinChangeList();
      } else {
        return;
      }
      console.log(this.page);
    });
    // 跳转页码
    this.$gopage.on('change', () => {
      if (this.$gopage.val() > this.countpage) {
        this.$gopage.val(this.countpage);
      }
    });
    // 跳转按钮
    this.$sure.on('click', () => {
      this.page = this.$gopage.val();
      this.getYcoinChangeList();
    });
  },
  userinfo() { // 获取用户信息，未调用接口（有相应的接口）
    this.$userimg.attr('src', decodeURIComponent(this.getQueryString('userimg')));
    this.$userName.html(unescape(this.getQueryString('name')));
    this.$userOpenid.html(this.getQueryString('opid'));
  },
  addYcoinRecord(remark) {
    addYcoinRecord({
      key_admin: keyadmin,
      userid: this.userid,
      remarks: remark,
      add_ycion: $('.addYnum').val(),
      reduce_ycion: $('.deductYnum').val(),
    }).then(json => {
      console.log(json);
      window.location.reload();
    }, json => {
      // console.log(json);
      $('.hint').html(json.msg);
    });
  },
  getYcoinChangeList() {
    getYcoinChangeList({
      key_admin: keyadmin,
      userid: this.userid,
      page: this.page || 1,
      mark: $('#behover').val(),
      stime: $('.startime').val(),
      etime: $('.endtime').val(),
    }).then(json => {
      this.renderDom(json.data);
    }, json => {
      this.$tbody.html(`<tr><td>${json.msg}</td></tr>`);
    });
  },
  renderDom(data) {
    const list = data.data;
    let html = '';
    $('.totalpage').html(data.pageall); // 总页数
    $('.currentpage').html(data.curpage); // 当前页
    this.$gopage.val(data.curpage);
    this.countpage = data.pageall;
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
    } else {
      this.$prev.removeClass('keyactive');
    }
    if (list.length > 0) {                 // 判断列表是否有数据
      $.map(list, (n) => {
        html += `<tr>
          <td>${n.createtime}</td>
          <td>${n.title}</td>
          <td>${n.coin_change}</td>
          <td>${n.remarks}</td>
        </tr>`;
      });
      this.$tbody.html(html);
    } else {
      this.$tbody.html('<tr><td>暂无数据</td></tr>');
    }
  },
  getQueryString(name) {
    const reg = new RegExp(`(^|&)${name}=([^&]*)(&|$)`);
    const r = window.location.search.substr(1).match(reg);
    if (r !== null) {
      return unescape(r[2]);
    }
    return null;
  },
};
coinRecord.init();
