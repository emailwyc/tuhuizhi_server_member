require('../../scss/donationpoint/record.scss');
import { intergalLIst } from '../model/donationpoint';
const $ = window.$;
require('../modules/cookie')($);
import { getDates } from '../modules/timestamp';
const lines = {
  line: 10,
  page: 1,
};
const record = {
  init() {
    this.initDom();
    this.initEvent();
    this.getIntergalList();
    // this.lines = 20;
  },
  initDom() {
    this.$totalnum = $('.totalnum');
    this.$single = $('.single');
    this.$prev = $('.prev');
    this.$next = $('.next');
    this.$tbody = $('.table tbody');
    this.$status = $('.status');
    this.$startdate = $('.startdate');
    this.$enddate = $('.enddate');
    this.$sharermobile = $('.sharermobile');
    this.$receivermobile = $('.receivermobile');
    this.$scorenum = $('.scorenum');
    this.$currentpage = $('.currentpage');
    this.$searchbtn = $('.searchBtn');
    this.$exportbtn = $('.exportbtn');
  },
  initEvent() {
    this.$searchbtn.on('click', () => {
      this.getIntergalList(lines.page, 1);
    });
    this.$exportbtn.on('click', () => {
      this.getIntergalList(lines.page, 2);
    });
    this.$prev.on('click', () => {
      if (lines.page <= 1) {
        alert('已经是第一页了');
        lines.page = 1;
      } else {
        lines.page --;
        this.getIntergalList(lines.page);
      }
    });
    this.$next.on('click', () => {
      console.log(lines.page >= (Math.ceil(this.$totalnum.html() / lines.line)));
      if (lines.page >= (Math.ceil(this.$totalnum.html() / lines.line))) {
        alert('已经是最后一页了');
        lines.page = Math.ceil(this.$totalnum.html() / lines.line);
      } else {
        lines.page ++;
        this.getIntergalList(lines.page);
      }
    });
  },
  getIntergalList(pagenum, exportnum) {
    intergalLIst({
      key_admin: $.cookie('ukey'),
      status: this.$status.val(),
      startdate: this.$startdate.val(),
      enddate: this.$enddate.val(),
      sharer: this.$sharermobile.val(),
      receiver: this.$receivermobile.val(),
      scorenum: this.$scorenum.val(),
      page: pagenum,
      rows: lines.line,
      export: exportnum,
    }).then(json => {
      console.log(json);
      this.$totalnum.html(json.data.count);
      this.$single.html(`${lines.line}条/页`);
      this.$currentpage.html(pagenum);
      if (json.data.path) {
        location.href = json.data.path;
        $('.msg').html('下载成功');
        setTimeout(() => {
          $('.msg').hide();
        }, 2000);
        $('.msg').show();
        return;
      }
      this.$exportbtn.show();
      let tr = '';
      $.each(json.data.data, (i, v) => {
        // console.log(i);
        const id = i + 1;
        // isreceive  0未领取，1已领取，2已过期
        let receive = '';
        if (v.isreceive === '0') {
          receive = '未领取';
        } else if (v.isreceive === '1') {
          receive = '已领取';
        } else {
          receive = '已过期';
        }
        tr += `<tr>
          <td>${id}</td>
          <td>${getDates(v.sharetime)}</td>
          <td>${v.sharermobile}</td>
          <td>${receive}</td>
          <td>${v.scorenumber}</td>
          <td>${v.receivermobile}</td>
          <td>${v.receivetime === '0' ? '' : getDates(v.receivetime)}</td>
        </tr>`;
      });
      this.$tbody.html(tr);
    }, json => {
      console.log(json);
      if (json.code === 102) {
        this.$tbody.html(`<tr><td colspan="7">${json.msg}</td></tr>`);
        this.$exportbtn.hide();
      }
    });
  },
};
record.init();
