require('../../scss/parking/setpark.scss');
// require('../bootstrap/modal');

import { getPayType, deleteMemberFTConf, changePayType } from '../model/parking';
const $ = window.$;
let freetimeData = [];
let freetimeDataLength;

const main = {
  init() {
    this.getData();
    this.initDom();
    this.eventFun();
  },
  initDom() {
    this.outer = $('.parking_box');
    this.power = $('.power_list');
    this.powerBtn = $('.add_btn');
    this.openBtn = $('.can_click .btnn');
    this.deleteBtn = $('.delete_btn');
    this.sureBtn = $('.sure_btn.active');
    // this.is_wechat = $('.is_wechat');
    this.is_score = $('.is_score');
    this.is_reft = $('.is_reft');
    this.is_freetime = $('.is_freetime');
    this.scoreValue = $('#score_inp');
  },
  eventFun() {
    this.openBtn.on('click', this.openFun);
    this.outer.on('click', '.user_power_box.active .add_btn', this.powerAddFun);
    this.power.on('change', 'select', this.changeSelect);
    this.power.on('click', '.delete_btn', this.deletePower);
    this.outer.on('click', '.sure_btn.active', this.saveFun);
    this.power.on('input', 'input', this.focusFun);
    this.scoreValue.on('input', this.changeBtnFun);
  },
  changeBtnFun() {
    const score = main.scoreValue.val();
    const freeHtml = $('.power_item');
    let num = 0;
    for (let i = 0; i < freeHtml.length; i ++) {
      const optionEl = freeHtml.eq(i).find('select option:selected');
      const inputEl = freeHtml.eq(i).find('input');
      if (optionEl.val() === '0' || !inputEl.val()) {
        num ++;
      }
    }
    if (num > 0 || !score) {
      $('.sure_btn').removeClass('active');
    } else {
      $('.sure_btn').addClass('active');
    }
  },
  focusFun(e) {
    const reg = /^[+]?\d*\.?\d{0,1}$/;
    const inputEl = $(e.target);
    if (inputEl.val() !== '') {
      inputEl.val(inputEl.val().match(reg));
    }
    main.changeBtnFun();
  },
  saveFun() {
    const freeConf = [];
    const freeHtml = $('.power_item');
    for (let i = 0; i < freeHtml.length; i ++) {
      const optionEl = freeHtml.eq(i).find('select option:selected');
      const inputEl = freeHtml.eq(i).find('input');
      if (optionEl.val() === '0' || !inputEl.val()) {
        alert('会员减免区域还未完整！');
        return false;
      }
      freeConf.push({ id: optionEl.val(), level: optionEl.text(), val: inputEl.val() });
    }

    const score = main.scoreValue.val();
    if (!score || score === '0') {
      alert('请输入等价积分！');
      return false;
    }
    changePayType({
      key_admin: $.cookie('ukey'),
      is_score: $('.is_score .btnn').hasClass('active') ? '1' : '0',
      is_freetime: $('.is_freetime .btnn').hasClass('active') ? '1' : '0',
      is_reft: $('.is_reft .btnn').hasClass('active') ? '1' : '0',
      free_conf: JSON.stringify(freeConf),
      score,
    }).then(result => {
      alert(result.msg);
      main.getData();
    }, error => {
      alert(error.msg);
    });
    return '';
  },
  deletePower(e) {
    const grade = $(e.target).parents('.power_item').find('option:selected').val();
    if (grade === '0') {
      $(e.target).parents('.power_item').remove();
    } else {
      deleteMemberFTConf({
        key_admin: $.cookie('ukey'),
        grade,
      }).then(result => {
        console.log(result);
        main.getData();
      }, error => {
        alert(error.msg);
      });
    }
  },
  changeSelect() {
    if ($(this).find('option:selected').val() !== '0') {
      $(this).html($(this).find('option:selected'));
    }
    const id = parseInt($(this).find('option:selected').val(), 10);
    for (let i = freetimeData.length - 1; i >= 0; i--) {
      if (freetimeData[i].id === id) {
        freetimeData.splice(i, 1);
      }
    }
    $(this).parents('.power_item').removeClass('noSelected');
    const selectSiblings = $('.noSelected select');
    let optionHtml = '<option value="0">请选择</option>';
    freetimeData.forEach((item) => {
      optionHtml += `<option value="${item.id}">${item.level}</option>`;
    });
    selectSiblings.html(optionHtml);
    main.changeBtnFun();
  },
  powerAddFun() {
    if (freetimeData.length > 0 && $('.power_item').length < freetimeDataLength) {
      let optionHtml = '<option value="0">请选择</option>';
      freetimeData.forEach((item) => {
        optionHtml += `<option value="${item.id}">${item.level}</option>`;
      });
      main.power.append(`<li class="power_item noSelected">
        <label>
          特权条件：<select name="" id="">${optionHtml}</select>
        </label>
        <label>
          ，减免 <input type="num" placeholder="粒度0.5" /> 小时
        </label>
        <a href="javascript:;" class="fr delete_btn">删除</a>
      </li>`);
    } else {
      alert('所有卡已添加完毕！');
    }
    main.changeBtnFun();
  },
  openFun(e) {
    if ($(e.target).hasClass('active')) {
      $(e.target).removeClass('active').html('未开启');
      if ($(e.target).parents('.list_box').find('.user_power_box')) {
        $(e.target).parents('.list_box').find('.user_power_box').removeClass('active');
        main.getData(true);
      }
    } else {
      $(e.target).addClass('active').html('已开启');
      if ($(e.target).parents('.list_box').find('.user_power_box')) {
        $(e.target).parents('.list_box').find('.user_power_box').addClass('active');
        main.getData(true);
      }
    }
  },
  loadHtml(nameEl, data) {
    if (data[nameEl] === '1') {
      this[nameEl].find('a').addClass('active').html('已开启');
      if (this[nameEl].parents('.list_box').find('.user_power_box')) {
        this[nameEl].parents('.list_box').find('.user_power_box').addClass('active');
      }
    } else {
      this[nameEl].find('a').removeClass('active').html('未开启');
      if (this[nameEl].parents('.list_box').find('.user_power_box')) {
        this[nameEl].parents('.list_box').find('.user_power_box').removeClass('active');
      }
    }
  },
  getData(isLoadHtml) {
    const self = this;
    getPayType({
      key_admin: $.cookie('ukey'),
    }).then(result => {
      if (!isLoadHtml) {
        self.loadHtml('is_score', result.data);
        self.loadHtml('is_reft', result.data);
        self.loadHtml('is_freetime', result.data);
      }
      freetimeData = result.data.need_conf;
      freetimeDataLength = result.data.need_conf.length + result.data.old_ft.length;
      let html = '';
      result.data.old_ft.forEach((item) => {
        html += `<li class="power_item">
          <label>
            特权条件：<select name="" id="" readonly>
            <option value="${item.id}">${item.level}</option></select>
          </label>
          <label>
            ，减免 <input type="num" placeholder="粒度0.5" readonly value="${item.val}" /> 小时
          </label>
          <a href="javascript:;" class="fr delete_btn">删除</a>
        </li>`;
      });
      main.power.html(html);
      main.changeBtnFun();
    }, error => {
      alert(error.msg);
    });
  },
};
main.init();
