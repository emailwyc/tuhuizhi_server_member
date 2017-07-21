/* Y币配置 */
require('../../scss/coinConfig/coinConfig.scss');
import { getCoinSetting, editCoinSetting, editCoinStatusSetting } from '../model/coinConfig';
const $ = window.$;
require('../modules/cookie')($);
const keyadmin = $.cookie('ukey'); // key_admin
const coinConfig = {
  init() {
    this.initDom();
    this.getCoinSetting();
    this.initEvent();
  },
  initDom() {
    this.$tbody = $('tbody');
    this.$headsubmit = $('.headsubmit'); // 每日Y币累计上限
    this.$sure = $('.sure'); // 确定
    this.$cancel = $('.cancel'); // 取消
    this.$markCancel = $('.markCancel'); // 弹窗-叉号
    this.$alertCancel = $('.alertCancel'); // 弹窗-取消
    this.$alertSure = $('.alertSure'); // 弹窗-确定
    this.keyswitch = 0;
  },
  initEvent() {
    // 头部-提交
    this.$headsubmit.on('click', () => {
      const headList = [];
      const list = {
        mark: 'limit',
        num: $('.limitinput').val(),
      };
      headList.push(list);
      this.editCoinSetting(headList);
    });
    // 下方-确定
    this.$sure.on('click', () => {
      const tbodyList = [];
      $.map($('tbody').find('input[type=number]'), (n) => {
        const list = {
          mark: $(n).attr('data-mark'),
          num: $(n).val(),
        };
        tbodyList.push(list);
      });
      this.editCoinSetting(tbodyList);
    });
    // 下方-取消
    this.$cancel.on('click', () => {
      window.location.reload();
    });
    // 弹窗-叉号
    this.$markCancel.on('click', () => {
      this.alertCancel();
    });
    // 弹窗-取消
    this.$alertCancel.on('click', () => {
      this.alertCancel();
    });
    // 弹窗-确定
    this.$alertSure.on('click', () => {
      this.editCoinStatusSetting();
    });
    // 按键事件
    $(document).keydown((event) => {
      console.log(this.keyswitch);
      if (this.keyswitch === 1) {
        if (event.keyCode === 37) { // 左键
          this.$alertSure.addClass('alertActive');
          this.$alertCancel.removeClass('alertActive');
        } else if (event.keyCode === 39) { // 右键
          this.$alertCancel.addClass('alertActive');
          this.$alertSure.removeClass('alertActive');
        } else if (event.keyCode === 13) { // enter键
          event.preventDefault();
          event.stopPropagation();
          if (this.$alertSure.hasClass('alertActive')) {
            this.editCoinStatusSetting();
          } else {
            this.alertCancel();
          }
        }
      }
    });
    // 启用/禁用
    this.$tbody.on('click', (event) => {
      const e = event || window.event;
      const elemt = e.target.tagName || e.srcElement;
      if (elemt.toLowerCase() === 'a') {
        $('.alertContent').html(`确定要${$(e.target).attr('data-status') === '1' ? '禁用' : '启用'}？`);
        $('.alertSure').attr('data-status', `${$(e.target).attr('data-status') === '1' ? '0'
         : '1'}`);
        $('.alertSure').attr('data-mark', `${$(e.target).attr('data-mark')}`);
        this.keyswitch = 1;
        $('.styles').css({ display: 'block' });
      }
    });
  },
  alertCancel() {
    this.$alertCancel.removeClass('alertActive');
    this.$alertSure.addClass('alertActive');
    this.keyswitch = 0;
    $('.styles').css({ display: 'none' });
    console.log(this.keyswitch);
  },
  getCoinSetting() {
    getCoinSetting({
      key_admin: keyadmin,
    }).then(json => {
      console.log(json);
      this.renderDom(json.data);
    }, json => {
      console.log(json);
      $('.options').css({ display: 'none' });
      this.$tbody.html(`<tr><td class="bodyOrder">${json.msg}</td></tr>`);
    });
  },
  editCoinSetting(data) {
    editCoinSetting({
      key_admin: keyadmin,
      setnumlist: data,
    }).then(json => {
      console.log(json);
      window.location.reload();
    }, json => {
      console.log(json);
      $(`${data}`).html(json.msg);
    });
  },
  editCoinStatusSetting() {
    editCoinStatusSetting({
      key_admin: keyadmin,
      mark: $('.alertSure').attr('data-mark'),
      status: $('.alertSure').attr('data-status'),
    }).then(json => {
      console.log(json);
      window.location.reload();
    }, json => {
      console.log(json);
    });
  },
  renderDom(data) {
    console.log(data);
    let html = '';
    $.map(data, (n, i) => {
      html += `<tr>
        <td class="bodyOrder">${i + 1}</td>
        <td>${n.title}</td>
        <td><input type="number" name="" value="${n.num}" data-mark="${n.mark}"></td>
        <td class="bodyStatus">
          <a href="javascript:;" id="${n.id}" data-mark="${n.mark}" data-status="${n.status}">
          ${n.status === '1' ? '启用' : '禁用'}
          </a>
        </td>
      </tr>`;
      if (n.mark === 'limit') {
        $('.limitinput').val(n.num);
      }
    });
    this.$tbody.html(html);
  },
};
coinConfig.init();
