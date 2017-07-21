/* 微信小程序活动配置 */
require('../../scss/wxCoupon/wxCoupon.scss');
import { activitiesList, delActivity } from '../model/wxCoupon';
const $ = window.$;
require('../modules/cookie')($);
const keyadmin = $.cookie('ukey'); // key_admin
const storage = window.sessionStorage;
console.log(keyadmin);
const wxCoupon = {
  init() {
    this.initDom();
    this.activitiesList();
    this.initEvent();
  },
  initDom() {
    this.$tbody = $('tbody');
    this.$editBtn = $('.editBtn'); // 编辑按钮
    this.$delBtn = $('.delBtn'); // 删除
    this.$cofigAdd = $('.cofigAdd'); // 添加
  },
  initEvent() {
    // 添加
    this.$cofigAdd.click('click', () => {
      window.location.href = '/wxCoupon/wxCouponAdd';
    });
    // 删除
    this.$tbody.on('click', '.delBtn', (event) => {
      const e = event || window.event;
      const target = e.target || e.srcElement;
      if (confirm('确定执行此操作吗？')) {
        this.delActivity(target.id);
      } else {
        return;
      }
    });
  },
  activitiesList() {
    activitiesList({
      key_admin: keyadmin,
      type_id: '1',
      childid: storage.getItem('childid') || '',
    }).then(json => {
      console.log(json);
      this.renderDom(json.data);
    }, json => {
      this.$tbody.html(json.msg);
    });
  },
  delActivity(id) {
    delActivity({
      key_admin: keyadmin,
      id,
    }).then(json => {
      console.log(json);
      window.location.reload();
    }, json => {
      console.log(json);
      alert('操作失败');
    });
  },
  renderDom(data) {
    // console.log(data);
    let html = '';
    $.map(data, n => {
      html += `<tr>
        <td class="buildId">${n.buildid}</td>
        <td class="buildName">${n.name}</td>
        <td class="activityId">${n.activeid}</td>
        <td>
          <a href="/wxCoupon/wxCouponEdit?buildid=${n.buildid}&activeid=${n.activeid}&id=${n.id}"
          class="editBtn">编辑</a>
          <a href="javascript:;" class="delBtn" id="${n.id}">删除</a>
        </td>
      </tr>`;
    });
    this.$tbody.html(html);
  },
};
wxCoupon.init();
