require('../../scss/peoplelist/userlist.scss');
import { lists, searchList, memberExport } from '../model';
const $ = window.$;
require('../modules/cookie')($);
const userList = {
  init() {
    userList.lines = 10;
    this.startpage = 1;
    this.initDom();
    this.list();
    this.initEvent();
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
        this.list(this.$pagenum);
      }
    });
    this.$next.on('click', () => {
      console.log(this.pagenum);
      if (this.$pagenum === this.pagenum) {
        alert('已经是最后一页了');
      } else {
        this.$pagenum = ++ this.startpage;
        this.list(this.$pagenum);
      }
    });
    this.$searchBtn.on('click', () => {
      if (this.$searchInput.val() === '') {
        return;
      }
      this.searchList();
    });
    this.$endTime.on('change', () => {
      this.list(this.$pagenum);
    });
    this.$regTime.on('change', () => {
      this.list(this.$pagenum);
    });
    this.$gradeBox.on('change', () => {
      this.list(this.$pagenum);
    });
    this.$statebox.on('change', () => {
      this.list(this.$pagenum);
    });
    this.$birth_sttime.on('change', () => {
      this.list(this.$pagenum);
    });
    this.$birth_endtime.on('change', () => {
      this.list(this.$pagenum);
    });
    this.$Export.on('click', () => {
      this.memberExport();
    });
    this.$gopage_btn.on('click', () => {
      this.list(this.$gopage.val());
    });
  },
  memberExport() {
    const day = new Date();
    day.setDate(day.getDate() - this.$regTime.val());
    const exportpage = $('.currentpage').text();
    const exportpage2 = exportpage.substring(3, 4);
    memberExport({
      key_admin: $.cookie('ukey'),
      page: exportpage2,
      lines: userList.lines,
      start_time: this.$regTime.val(),
      end_time: this.$endTime.val(),
      level: this.$gradeBox.val(),
      status: this.$statebox.val(),
      birth_sttime: this.$birth_sttime.val(),
      birth_endtime: this.$birth_endtime.val(),
    }).then(json => {
      console.log(json);
      location.href = json.data.path;
    }, json => {
      console.log(json);
      alert(json.msg);
    });
  },
  list(pagenum) {
    // const day = new Date();
    // day.setDate(day.getDate() - this.$regTime.val());
    // start_time: `${day.getFullYear()}-${day.getMonth() + 1}-${day.getDate()}`,
    lists({
      key_admin: $.cookie('ukey'),
      page: pagenum || '1',
      lines: userList.lines,
      start_time: this.$regTime.val(),
      end_time: this.$endTime.val(),
      level: this.$gradeBox.val(),
      status: this.$statebox.val(),
      birth_sttime: this.$birth_sttime.val(),
      birth_endtime: this.$birth_endtime.val(),
    }).then(json => {
      console.log(json);
      this.startpage = json.data.page;
      this.$whole.html(`全部会员(${json.data.total} 个会员)`);
      this.$currentppage.html(`当前第${json.data.page}页`);
      this.$total.html(`共${json.data.total}条记录`);
      const data = json.data.data;
      this.pagenum = json.data.pagenum;
      this.render(data);
    }, json => {
      console.log(json);
      this.$tbody.html(`<td colspan="7">${json.msg}</td>`);
      $('.pager').css('display', 'none');
    });
  },
  searchList() {
    searchList({
      key_admin: $.cookie('ukey'),
      page: '1',
      lines: userList.lines,
      usermember: this.$searchInput.val(),
    }).then(json => {
      console.log(json);
      const data = json.data.data;
      this.render(data);
      this.$currentppage.html(`当前第${json.data.page}页`);
      this.$total.html(`共${json.data.total}条记录`);
    }, json => {
      console.log(json);
      this.$tbody.html(`<tr><td colspan="6">${json.msg}</td></tr>`).css('color', 'red');
      // $('.pager').css('display', 'none');
      setTimeout(() => {
        location.reload();
      }, 1000);
    });
  },
  render(data) {
    console.log(data);
    let tr = '';
    $.each(data, (i, v) => {
      tr += `<tr>
        <td>
          <div class="user" data-cardno="${v.cardno}">
            <a href="/peoplelist/userdetails?cardno=${v.cardno}">
              <img class="face" src="${v.headerimg}" />
              <p class="username">${v.usermember}</p>
              <p class="remarks">${v.remark}</p>
            </a>
          </div>
        </td>
        <td>${v.cardno}</td>
        <td>${v.level}</td><td>${v.status === '1' ? '有效' : '冻结'}</td>
        <td>${v.sex === '1' ? '男' : '女'}</td>
        <td>${v.score_num}</td>
        <td>${v.getcarddate}</td>
        <td>
          <a href="/peoplelist/userdetails?cardno=${v.cardno}" data-cardno="${v.cardno}">详情</a>
        </td>
      </tr>`;
    });
    this.$tbody.html(tr);
    return {};
  },
};
userList.init();
