require('../../scss/mall/campaign.scss');
import { buildidList, obtainAct, actAdd } from '../model/mall';
import { out } from '../modules/out.js';
// import { code } from '../modules/code502';
const $ = window.$;
require('../modules/cookie')($);
const campaign = {
  init() {
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      alert('未找到ukey');
      location.href = '/user/login';
      return;
    }
    this._buildidList();
  },
  initDom() {
    this.$marketinginput = $('.marketinginput');
    this.$erpinput = $('.erpinput');
    this.$out = $('.out');
    this.$close = $('.close');
    this.$table = $('.table tbody');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });

    this.$table.on('click', '.marketing-btn', (e) => {
      e.preventDefault();
      const tempVal = $(e.target).siblings('.marketinginput').val();
      const tempBuild = $(e.target).parents('tr').children().eq(1).text();
      this._actAdd(tempBuild, 'ZHT_YX', tempVal);
    });

    this.$table.on('click', '.erp-btn', (e) => {
      e.preventDefault();
      const tempVal = $(e.target).siblings('.erpinput').val();
      const tempBuild = $(e.target).parents('tr').children().eq(1).text();
      this._actAdd(tempBuild, 'ERP_YX', tempVal);
    });

    this.$close.on('click', (e) => {
      $(e.target).parents('.alert').hide();
    });
  },
  _actAdd(build, type, val) {
    actAdd({
      key_admin: $.cookie('ukey'),
      activity: val,
      type,
      buildid: build,
    }).then(json => {
      console.log(json);
      alert('修改成功');
    }, json => {
      console.log(json);
      alert(json.msg);
      if (json.code === 502) {
        alert(json.msg);
        location.href = '/user/login';
        return;
      }
    });
  },
  _buildidList() {
    buildidList({
      key_admin: $.cookie('ukey'),
    }).then((json) => {
      console.log(json);
      const data = json.data;
      data.forEach((v) => {
        const tmp = `<tr>
          <td>${v.name}</td>
          <td class="build-id">${v.buildid}</td>
          <td class="form-inline">
            <div class="form-group">
              <input type="text" class="form-control marketinginput"
              name="ZHT_YX" placeholder="" value="">
              <button type="button" class="btn btn-secondary marketing-btn">确定</button>
            </div>
          </td>
          <td class="form-inline">
            <div class="form-group">
              <input type="text" class="form-control erpinput"
              name="ERP_YX" placeholder="" value="">
              <button type="button" class="btn btn-secondary erp-btn">确定</button>
            </div>
          </td>
        </tr>`;
        this.$table.append(tmp);
      });
      this._obtainAct();
    }).catch((err) => {
      alert(err.msg);
    });
  },
  _obtainAct() {
    // 获取活动id
    obtainAct({
      key_admin: $.cookie('ukey'),
      status: 'new',
    }).then((json) => {
      console.log(json);
      const data = json.data;
      $.each(data, (i, v) => {
        const $buildId = $('.build-id');
        $buildId.each((j, val) => {
          if ($(val).text() === v.buildid) {
            if (v.type === 'ZHT_YX') {
              $(val).next().find('input').val(v.activity);
            } else if (v.type === 'ERP_YX') {
              $(val).next().next().find('input').val(v.activity);
            }
          }
        });
      });
    }).catch((err) => {
      console.log(err);
    });
  },
};
campaign.init();
