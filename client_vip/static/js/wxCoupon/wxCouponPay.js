/* 添加广告 */
require('../../scss/wxCoupon/wxCouponPay.scss');
import { appletPayList } from '../model/wxCoupon';
const $ = window.$;
require('../modules/cookie')($);
const keyadmin = $.cookie('ukey'); // key_admin
console.log(keyadmin);

const wxCouponPay = {
  init() {
    this.initDom();
    this.getUserList();
    this.initEvent();
  },
  initDom() {
    this.$tbody = $('tbody');
    this.$checkbtn = $('.checkbtn'); // 查询
    this.$prev = $('.prev'); // 上一页
    this.$next = $('.next'); // 下一页
    this.$gopage = $('.gopage'); // 跳转页码
    this.$sure = $('.sure'); // 跳转确定
    this.$exportdataBtn = $('.exportdataBtn');
    this.page = 1;
    this.countpage = '';
    this.curtpage = 1;
  },
  initEvent() {
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
    this.$exportdataBtn.on('click', () => {
      this.getUserList('yes');
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
  getUserList(isno) {
    appletPayList({
      key_admin: keyadmin,
      page: this.page || 1,
      orderno: $('.searchinput').val(),
      lines: '',
      shopname: $('.shopname').val(),
      starttime: $('.startime').val(),
      endtime: $('.endtime').val(),
      export: isno || '',
    }).then(json => {
      this.renderDom(json.data);
    }, json => {
      this.$tbody.html(`<tr><td>${json.msg}</td></tr>`);
      $('.pages').css({ display: 'none' });
      $('.exportdataBtn').css({ display: 'none' });
    });
  },
  renderDom(data) {
    console.log(data.url);
    if (data.url) {
      window.location.href = data.url;
    }
    const list = data.data;
    let html = '';
    let payStatus = '';
    if (data.page_num > 1) {                // 是否显示分页功能-总条数
      $('.pages').css({ display: 'block' });
    } else {
      $('.pages').css({ display: 'none' });
    }
    if (data.page >= data.page_num) {     // 下一页按钮设为灰色表示不可点击
      this.$next.addClass('keyactive');
      this.$prev.removeClass('keyactive');
    } else {
      this.$next.removeClass('keyactive');
    }
    if (data.page <= '1') {               // 上一页按钮设为灰色表示不可点击
      this.$prev.addClass('keyactive');
    } else {
      this.$prev.removeClass('keyactive');
    }
    if (list.length > 0) {
      $('.totalpage').html(data.page_num); // 总页数
      $('.currentpage').html(data.page); // 当前页
      this.$gopage.val(data.page);
      this.countpage = data.page_num;
      $.map(list, (n, i) => {
        if (n.status && n.status !== null) {
          if (n.status === '0') {
            payStatus = '未支付';
          } else if (n.status === '1') {
            payStatus = '已支付';
          } else {
            payStatus = '';
          }
        } else {
          payStatus = '';
        }
        html += `<tr>
          <td>${i + 1}</td>
          <td>${n.marketname && n.marketname !== null ? n.marketname : ''}</td>
          <td>${n.shopname && n.shopname !== null ? n.shopname : ''}</td>
          <td>${n.orderno && n.orderno !== null ? n.orderno : ''}</td>
          <td>${n.couponqr && n.couponqr !== null ? n.couponqr : ''}</td>
          <td>${n.couponprice && n.couponprice !== null ? n.couponprice : ''}</td>
          <td>${n.mount && n.mount !== null ? n.mount : ''}</td>
          <td>${n.amount && n.amount !== null ? n.amount : ''}</td>
          <td>${payStatus}</td>
          <td>${n.datetime && n.datetime !== null ? n.datetime : ''}</td>
        </tr>`;
      });
      this.$tbody.html(html);
      // console.log(html);
    } else {
      this.$tbody.html('<tr><td>无数据</td></tr>');
      $('.pages').css({ display: 'none' });
      $('.exportdataBtn').css({ display: 'none' });
    }
  },
};
wxCouponPay.init();
