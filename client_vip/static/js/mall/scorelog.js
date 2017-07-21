require('../../scss/mall/scorelog.scss');
require('../../scss/peoplelist/userlist.scss');
import { lists, searchList, memberExport, getprizesearch, getid, memcardlist } from '../model';
import { buildidList } from '../model/mall';
const $ = window.$;
require('../modules/cookie')($);

// 定义常量
const ZHT_YX = 'ZHT_YX';
const ERP_YX = 'ERP_YX';
// const STATUS = {
//   0: '未发放',
//   1: '已发放',
//   2: '已领取',
//   3: '已核销',
//   4: '已过期',
//   5: '已过期',
//   6: '转赠中',
//   7: '核销中',
//   8: '退款中',
//   9: '已退款',
// };

const STATUS = {
  2: '已领取',
  3: '已核销',
  5: '已过期',
};

const userList = {
  init() {
    userList.lines = 10;
    this.startpage = 1;
    this.allpage = '';
    this.initDom();
    this.initEvent();
    this.activityid = '';

    this._statusList();
    this._typeList();
    this.getid();
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
    this.$Export = $('.export-btn');
    this.$timeBtn = $('.time-btn');

    this.$build = $('.build');
    this.$status = $('.status');
    this.$type = $('.type');
  },
  initEvent() {
    this.$prev.on('click', () => {
      console.log(this.startpage);
      if (this.startpage === 1) {
        alert('已经是第一页了');
      } else {
        this.$pagenum = -- this.startpage;
        this._getprizesearch(this.$pagenum);
      }
    });
    this.$next.on('click', () => {
      if (parseInt(this.pagenum, 10) === parseInt(this.allpage, 10)) {
        alert('已经是最后一页了');
      } else {
        this.$pagenum = ++ this.startpage;
        this._getprizesearch(this.$pagenum);
      }
    });
    this.$searchBtn.on('click', () => {
      this._getprizesearch();
    });
    this.$timeBtn.on('click', () => {
      this._getprizesearch(this.$pagenum);
    });
    this.$build.on('change', () => {
      this._getprizesearch(this.$pagenum);
    });
    this.$type.on('change', () => {
      this._getprizesearch(this.$pagenum);
    });
    this.$status.on('change', () => {
      this._getprizesearch(this.$pagenum);
    });
    // this.$endTime.on('change', () => {
    //   this.getprizesearch(this.$pagenum);
    // });
    // this.$regTime.on('change', () => {
    //   this.getprizesearch(this.$pagenum);
    // });
    this.$gradeBox.on('change', () => {
      this._getprizesearch(this.$pagenum);
    });
    this.$statebox.on('change', () => {
      this._getprizesearch(this.$pagenum);
    });
    // this.$birth_sttime.on('change', () => {
    //   this.list(this.$pagenum);
    // });
    // this.$birth_endtime.on('change', () => {
    //   this.list(this.$pagenum);
    // });
    this.$Export.on('click', () => {
      this._getprizesearch(this.$pagenum, 'yesall');
    });
    this.$gopage_btn.on('click', () => {
      this._getprizesearch(this.$gopage.val());
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
      start_time: `${day.getFullYear()}-${day.getMonth() + 1}-${day.getDate()}`,
      level: this.$gradeBox.val(),
      status: this.$statebox.val(),
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
      $('.pager').css('display', 'block');
    }, json => {
      console.log(json);
      this.$tbody.html(`<td colspan="8">${json.msg}</td>`);
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
      $('.pager').css('display', 'block');
    }, json => {
      console.log(json);
      this.$tbody.html(`<tr><td colspan="8">${json.msg}</td></tr>`).css('color', 'red');
      $('.pager').css('display', 'none');
      setTimeout(() => {
        location.reload();
      }, 1000);
    });
  },
  memcardlist() {
    memcardlist({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      this.cardlist = [];
      $.each(json.data, (i, n) => {
        console.log(n);
        const key = n.id;
        this.cardlist[key] = n.level;
      });
    }, json => {
      console.log(json);
    });
  },
  getid() {
    getid({
      key_admin: $.cookie('ukey'),
      status: 'new',
    }).then(json => {
      console.log(json);
      this.activityList = json.data;
      this._buildidList();
    }, json => {
      console.log(json);
    });
  },
  render(data, buildName) {
    console.log(data);
    $.each(data, (i, v) => {
      const tr = `<tr>
        <td>${buildName || '建筑物'}</td>
        <td>${v.level || ''}</td>
        <td>${v.usermember ? v.usermember : '离线会员'}</td>
        <td>${v.mobile ? v.mobile : ''}</td>
        <td>${v.prize_name ? v.prize_name : ''}</td>
        <td>${v.get_time ? v.get_time : ''}</td>
        <td>${v.integral ? v.integral : ''}</td>
        <td>${STATUS[v.status]}</td>
      </tr>`;
      this.$tbody.append(tr);
    });
    return {};
  },
  _getprizesearch(pagenum, ex = 'no') {
    this.$tbody.empty();
    const tempBuild = this.$build.find('option:selected').attr('data-build');
    const tempType = this.$type.find('option:selected').attr('data-type');
    let tempStatus = this.$status.find('option:selected').attr('data-status');
    if (tempStatus === 'all') tempStatus = '';
    let tempAct = '';
    let buildName = '';
    // if (tempBuild === 'all') {
    //   this.buildList.forEach((v) => {
    //     buildName = v.name;
    //     this.$activityList.forEach((val) => {
    //       if (val.buildid === v.buildid && val.type === tempStatus) {
    //         tempAct = v.activity;
    //         getprizesearch(tempBuild, buildName, tempType, tempStatus, tempAct, pagenum, ex);
    //       }
    //     });
    //   });
    // } else {
    buildName = this.$build.find('option:selected').text();
    $.each(this.activityList, (i, v) => {
      if (v.buildid === tempBuild && v.type === tempType) {
        tempAct = v.activity;
      }
    });
    this.getPrizeSearch(tempBuild, buildName, tempType, tempStatus, tempAct, pagenum, ex);
    // }
  },
  getPrizeSearch(tempBuild, buildName, tempType, tempStatus, tempAct, pagenum, ex) {
    // const day = new Date();
    // day.setDate(day.getDate() - this.$regTime.val());
    // start_time: `${day.getFullYear()}-${day.getMonth() + 1}-${day.getDate()}`,
    getprizesearch({
      key_admin: $.cookie('ukey'),
      page: pagenum || '1',
      lines: userList.lines,
      starttime: this.$regTime.val(),
      endtime: this.$endTime.val(),
      activity_id: tempAct,
      mobile: this.$searchInput.val(),
      buildid: tempBuild,
      type: tempType,
      status: tempStatus,
      // level: this.$gradeBox.val(),
      // status: this.$statebox.val(),
      // birth_sttime: this.$birth_sttime.val(),
      // birth_endtime: this.$birth_endtime.val(),
      export: ex,
    }).then(json => {
      console.log(json);
      if (json.data.url) {
        window.location.href = json.data.url;
      } else {
        this.startpage = json.page - 0;
        this.$currentppage.html(`当前第${json.page}页`);
        this.$total.html(`共${json.sum}条记录`);
        this.allpage = Math.ceil(json.sum / userList.lines);
        console.log(this.allpage);
        const data = json.data;
        this.pagenum = json.page;
        this.render(data, buildName);
        $('.pager').css('display', 'block');
      }
    }, json => {
      console.log(json);
      this.$tbody.html(`<td colspan="8">${json.msg}</td>`);
      $('.pager').css('display', 'none');
    });
  },
  _buildidList() {
    buildidList({
      key_admin: $.cookie('ukey'),
    }).then((json) => {
      console.log(json);
      const data = json.data;
      this.buildList = data;
      if (data === null) {
        return;
      }
      // const tmpAll = '<option data-build="all">全部</option>';
      // this.$build.append(tmpAll);
      data.forEach((v) => {
        const tmp = `<option data-build="${v.buildid}">${v.name}</option>`;
        this.$build.append(tmp);
      });
      this._getprizesearch(this.$pagenum);
    }).catch(err => console.log(err));
  },
  _statusList() {
    const tmpAll = '<option data-status="all">全部</option>';
    this.$status.append(tmpAll);
    $.each(STATUS, (k, v) => {
      const tmp = `<option data-status="${k}">${v}</option>`;
      this.$status.append(tmp);
    });
  },
  _typeList() {
    const tmp = `<option data-type="${ZHT_YX}">营销平台活动</option>
                 <option data-type="${ERP_YX}">第三方活动</option>`;
    this.$type.append(tmp);
  },
};
userList.init();
