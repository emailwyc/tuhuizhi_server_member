require('../../scss/peoplelist/integrallog.scss');
require('../../scss/mall/scorelog.scss');
require('../../scss/peoplelist/userlist.scss');
import { getScoreList } from '../model';
const $ = window.$;
require('../modules/cookie')($);
const integralLog = {
  init() {
    integralLog.lines = 10;
    this.startpage = 1;
    this.allpage = '';
    this.initDom();
    this.getScoreList();
    this.initEvent();
    this.activityid = '';
  },
  initDom() {
    this.$tbody = $('.table tbody');
    this.$next = $('.next');
    this.$prev = $('.prev');
    this.$page_num = $('.pagenum');
    this.$whole = $('.whole');
    this.$searchBtn = $('.search_btn');
    this.$searchInput = $('.searchinput');
    this.$currentppage = $('.currentpage');
    this.$total = $('.total');
    this.$regTime = $('.regTime');
    this.$endTime = $('.endTime');
    this.$gradeBox = $('.gradeBox');
    this.$statebox = $('.statebox');
    this.$birth_sttime = $('.birth_sttime');
    this.$birth_endtime = $('.birth_endtime');
    this.$gopage = $('.gopage');
    this.$gopage_btn = $('.gopage_btn');
    this.$Export = $('.Export');
  },
  initEvent() {
    this.$whole.on('click', () => {
      location.reload();
    });
    this.$prev.on('click', () => {
      console.log(this.startpage);
      if (this.startpage === 1) {
        alert('已经是第一页了');
      } else {
        this.$pagenum = -- this.startpage;
        this.getScoreList(this.$pagenum);
      }
    });
    this.$next.on('click', () => {
      console.log(this.pagenum);
      if (this.$pagenum === this.allpage) {
        alert('已经是最后一页了');
      } else {
        this.$pagenum = ++ this.startpage;
        this.getScoreList(this.$pagenum);
      }
    });
    this.$searchBtn.on('click', () => {
      // if (this.$searchInput.val() === '') {
      //   return;
      // }
      this.getScoreList();
    });
    this.$endTime.on('change', () => {
      this.getScoreList(this.$pagenum);
    });
    // this.$regTime.on('change', () => {
    //   this.getScoreList(this.$pagenum);
    // });
    this.$Export.on('click', () => {
      this.getScoreList(this.$pagenum, 1);
    });
    this.$gopage_btn.on('click', () => {
      this.getScoreList(this.$gopage.val());
    });
  },
  render(data) {
    console.log(data);
    let tr = '';
    $.each(data, (i, v) => {
      tr += `<tr>
        <td>${v.id}</td>
        <td>${v.cardno}</td>
        <td>${v.scorenumber}</td>
        <td>${v.why}</td>
        <td>${v.datetime}</td>
        <td>${v.cutadd === '2' ? '增加积分' : '扣除积分'}</td>
        <td>${v.scorecode}</td>
      </tr>`;
    });
    this.$tbody.html(tr);
    return {};
  },
  getScoreList(pagenum, ex = 'no') {
    // const day = new Date();
    // day.setDate(day.getDate() - this.$regTime.val());
    // start_time: `${day.getFullYear()}-${day.getMonth() + 1}-${day.getDate()}`,
    getScoreList({
      key_admin: $.cookie('ukey'),
      page: pagenum || '1',
      lines: integralLog.lines,
      starttime: this.$regTime.val(),
      endtime: this.$endTime.val(),
      export: ex,
    }).then(json => {
      console.log(json);
      if (json.data.path) {
        window.location.href = json.data.path;
      } else {
        $('.pager').css('display', 'block');
        this.startpage = json.data.page - 0;
        // this.$whole.html(`全部会员(${json.data.total} 个会员)`);
        this.$currentppage.html(`当前第${json.data.page}页`);
        this.$total.html(`共${json.data.count}条记录`);
        this.allpage = Math.ceil(json.data.count / integralLog.lines);
        console.log(this.allpage);
        const data = json.data.datas;
        this.pagenum = json.data.page;
        this.render(data);
      }
    }, json => {
      console.log(json);
      this.$tbody.html(`<td colspan="7">${json.msg}</td>`);
      $('.pager').css('display', 'none');
    });
  },
};
integralLog.init();
