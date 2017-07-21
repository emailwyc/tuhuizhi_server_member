require('../../scss/mall/gift.scss');
import { integralList, buildidList, prizeOffline, cardTypeList } from '../model/mall';
import { out } from '../modules/out.js';
require('../bootstrap/modal');
const $ = window.$;
require('../modules/cookie')($);

// 定义常量
const ZHT_YX = 'ZHT_YX';
const ERP_YX = 'ERP_YX';

const STATUS = {
  0: '进行中',
  3: '已过期',
  4: '已售罄',
};

const gift = {
  init() {
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      alert('未找到ukey');
      location.href = '/user/login';
      return;
    }
    this.state = {
      data: {},
    };
    this._statusList();
    this._typeList();
    this._cardTypeList();
  },
  initDom() {
    this.$tbody = $('.table tbody');
    this.$out = $('.out');
    this.$build = $('.build');
    this.$status = $('.status');
    this.$type = $('.type');
    this.$statusBtn = $('.status-btn');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });

    this.$build.on('change', () => this._search());
    this.$status.on('change', () => this._search());
    this.$type.on('change', () => this._search());

    this.$tbody.on('click', '.status-btn', (e) => {
      const ele = $(e.target);
      const tempBuild = this.$build.find('option:selected').attr('data-build');
      const tempId = ele.attr('data-id');
      const tempStatus = ele.text() === '下线' ? 1 : 2;
      this._prizeOffline(tempBuild, tempId, tempStatus).then((json) => {
        alert(json.msg);
        // const t = ele.text() === '上线' ? '下线' : '上线';
        // ele.text(t);
        location.reload();
      }).catch(err => alert(err.msg));
    });

    this.$tbody.on('click', '.edit-btn', (e) => {
      const tempBuild = this.$build.find('option:selected').attr('data-build');
      const activityType = this.$type.find('option:selected').attr('data-type');
      const tempPid = $(e.target).next().attr('data-id');
      const giftInfo = {
        prizeName: $(e.target).parents('tr').children('td').eq(3).text(),
        activityType,
      };
      window.sessionStorage.setItem('giftInfo', JSON.stringify(giftInfo));
      window.location.href = `editGift?id=${tempPid}&buildid=${tempBuild}`;
    });
  },
  _render(data) {
    if (data === null || data.length === 0) {
      this.$tbody.append('<tr><td colspan="12">暂无数据</td></tr>');
      return;
    }
    let tempArr = [];
    $.each(data, (i, v) => {
      tempArr.push(v);
    });
    tempArr.sort((a, b) => a.des - b.des);
    const tempStatus = this.$status.find('option:selected').attr('data-status');
    if (tempStatus === 4 || tempStatus === '4') {
      tempArr = tempArr.filter(v => parseInt(v.num, 10) === parseInt(v.issue, 10));
    }
    if (tempArr.length === 0) {
      this.$tbody.append('<tr><td colspan="12">暂无数据</td></tr>');
      return;
    }
    $.each(tempArr, (i, v) => {
      let integral = '';
      if (v.discount === 1 || v.discount === '1') {
        integral = parseInt(v.integral, 10) || '暂无';
      } else {
        // const str = v.integral;
        // let obj = '';
        // const reg = /[^,]+:[^,]+/g;
        // const arr = str.match(reg);
        // if (arr) {
        //   arr.forEach((item) => {
        //     const tempArr = item.split(':');
        //     const key = decodeURIComponent(tempArr[0]);
        //     const val = decodeURIComponent(tempArr[1]);
        //     obj += `${key}${val}<br />`;
        //   });
        // }
        // integral = obj;
        $.each(JSON.parse(v.integral), (j, m) => {
          integral += `${j}${m}<br />`;
        });
      }
      const tmp = `<tr>
        <td scope="row">${v.des || '暂无'}</td>
        <td>${v.type_name || '暂无'}</td>
        <td><img src="${v.imgUrl}" alt="暂无"></td>
        <td>${v.main || '暂无'}</td>
        <td>${v.startTime || '暂无'}</td>
        <td>${v.endTime || '暂无'}</td>
        <td>${STATUS[v.status] || '暂无'}</td>
        <td>${v.num || 0}</td>
        <td>${v.issue || 0}</td>
        <td>${v.writeoff_count || 0}</td>
        <td>${integral}</td>
        <td>
          <a href="javascript:;" class="edit-btn">编辑</a>
          <a href="javascript:;" class="status-btn"
          data-id="${v.pid}">${v.is_status === 1 || v.is_status === '1' ? '上线' : '下线'}</a>
        </td>
      </tr>`;
      this.$tbody.append(tmp);
    });
  },
  _search() {
    const tempBuild = this.$build.find('option:selected').attr('data-build');
    const tempStatus = this.$status.find('option:selected').attr('data-status');
    const tempType = this.$type.find('option:selected').attr('data-type');
    this._integralList(tempBuild, tempType, tempStatus);
  },
  __buildList() {
    buildidList({
      key_admin: $.cookie('ukey'),
    }).then((json) => {
      const data = json.data;
      if (data === null) {
        return;
      }
      data.forEach((v) => {
        const tmp = `<option data-build="${v.buildid}">${v.name}</option>`;
        this.$build.append(tmp);
      });
      this._search();
    }).catch((err) => {
      alert(err.msg);
    });
  },
  _typeList() {
    const tmp = `<option data-type="${ZHT_YX}">营销平台活动</option>
                 <option data-type="${ERP_YX}">第三方活动</option>`;
    this.$type.append(tmp);
  },
  _statusList() {
    const tmpAll = '<option data-status="all">全部</option>';
    this.$status.append(tmpAll);
    $.each(STATUS, (k, v) => {
      const tmp = `<option data-status="${k}">${v}</option>`;
      this.$status.append(tmp);
    });
    this.$status.find("option[data-status='0']").attr('selected', true);
    this.__buildList();
  },
  _integralList(buildid, type, status) {
    this.$tbody.empty();
    let tempData = {};
    if (status === 'all' || status === 4 || status === '4') {
      tempData = {
        key_admin: $.cookie('ukey'),
        buildid,
        type,
      };
    } else {
      tempData = {
        key_admin: $.cookie('ukey'),
        buildid,
        type,
        status,
      };
    }
    // console.log(`${buildid}@@@${type}@@@${status}`);
    integralList(tempData).then((json) => {
      console.log(json);
      const data = json.data;
      this._render(data);
    }).catch((err) => {
      console.log(err);
      this.$tbody.append(`<tr><td colspan="12">${err.msg || '暂无数据'}</td></tr>`);
    });
  },
  _prizeOffline(buildid, pid, isStatus) {
    return new Promise((resolve, reject) => {
      prizeOffline({
        key_admin: $.cookie('ukey'),
        buildid,
        pid,
        is_status: isStatus,
      }).then((json) => {
        resolve(json);
      }).catch((err) => {
        reject(err);
      });
    });
  },
  _cardTypeList() {
    cardTypeList({
      key_admin: $.cookie('ukey'),
    }).then((json) => {
      console.log(json);
    }).catch(err => console.log(err));
  },
};
gift.init();
