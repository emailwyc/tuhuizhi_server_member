require('../../scss/Yadmin/Yexchange.scss');
import { getPrizeSearch, obtainAct } from '../model/Ycurrency';
const $ = window.$;
require('../modules/cookie')($);
const stor = sessionStorage;
const Yexchange = {
  init() {
    this.pages = 1;
    this.lines = 10;
    this.obtainAct();
    this.initDom();
    this.initEvent();
  },
  initDom() {
    this.$status = $('.status');
    this.$openid = $('.openid');
    this.$starttime = $('.starttime');
    this.$endtime = $('.endtime');
    this.$export = $('.export');
    this.$tbody = $('.table tbody');
    this.$searchbtn = $('.search_btn');
    this.$total = $('.total');
    this.$prev = $('.prev');
    this.$next = $('.next');
    this.$currentpage = $('.currentpage');
    this.$gopageBtn = $('.gopage_btn');
    this.$gopage = $('.gopage');
  },
  initEvent() {
    this.$export.on('click', () => {
      this.getPrizeSearch('yes');
    });
    this.$searchbtn.on('click', () => {
      this.getPrizeSearch('no');
    });
    this.$status.on('change', () => {
      this.getPrizeSearch('no');
    });
    this.$prev.on('click', () => {
      if (this.pages === 1) {
        alert('已经是第一页了');
      } else {
        this.pages --;
        this.getPrizeSearch('no');
      }
    });
    this.$next.on('click', () => {
      if (this.pages === this.total) {
        alert('已经是最后一页了');
      } else {
        this.pages ++;
        this.getPrizeSearch('no');
      }
    });

    this.$gopageBtn.on('click', () => {
      if (this.$gopage.val() > this.total) {
        alert('已超过总页数！');
        this.$gopage.val('');
      } else {
        this.pages = parseInt(this.$gopage.val(), 10);
        this.getPrizeSearch('no');
      }
    });
  },
  obtainAct() {
    obtainAct({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      stor.setItem('activity', json.data.activity);
      this.getPrizeSearch('no');
    }, json => {
      console.log(json);
    });
  },
  getPrizeSearch(ex) {
    getPrizeSearch({
      key_admin: $.cookie('ukey'),
      activity_id: stor.getItem('activity'),
      page: this.pages,
      lines: this.lines,
      status: this.$status.val(),
      openid: this.$openid.val(),
      starttime: this.$starttime.val(),
      endtime: this.$endtime.val(),
      export: ex,
    }).then(json => {
      console.log(json);
      if (json.data.url) {
        window.location.href = json.data.url;
      } else {
        // console.log(Math.ceil(json.sum / this.lines));
        let tr = '';
        $.each(json.data, (i, v) => {
          tr += `
          <tr>
            <td>${i + 1}</td>
            <td>
              <img class="face" src="${v.headimg}" />
            </td>
            <td>
            ${v.usermember}
            </td>
            <td>
            ${v.openid}
            </td>
            <td>
              ${v.prize_name}
            </td>
            <td>
              ${v.starttime}
            </td>
            <td>
              ${v.integral}
            </td>
            <td>
              ${v.status === '2' ? '已领取' : '未发放'}
            </td>
          </tr>
          `;
        });
        this.$tbody.html(tr);
      }
      if (json.data.length > 0) {
        $('.pager').css('display', 'block');
        this.$currentpage.html(`当前第${json.page}页`);
        this.total = Math.ceil(json.sum / this.lines);
        this.$total.html(`共${this.total}页`);
        this.$gopage.val('');
      }
    }, json => {
      console.log(json);
      this.$tbody.html(`<tr><td colspan="8">${json.msg}</td></tr>`);
      $('.pager').css('display', 'none');
    });
  },
};
Yexchange.init();
