require('../../scss/member/modifyidcard.scss');
import { staticpage, setChangeConfig, getChangeConfig } from '../model';
import { out } from '../modules/out.js';
const $ = window.$;
require('../modules/cookie')($);
// pubApi.getQinNiuToken.then(token => {
// const UM = window.UM;
const modifyidcard = {
  init() {
    this.initDom();
    this.initEvent();
    this.getChangeConfig();
  },
  initDom() {
    this.$out = $('.out');
    this.$title = $('.title');
    this.$subBtn = $('.subBtn');
    this.$updata = $('.updata');
    this.$inlineRadio1 = $('#inlineRadio1');
    this.$inlineRadio2 = $('#inlineRadio2');
    this.$idnumber = $('input[name="inlineRadioOptions"]');
    this.$birthday = $('input[name="inlineRadioOptions2"]');
    this.$url = $('.url');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$subBtn.on('click', () => {
      this.submMemberRights();
    });
    this.$updata.on('click', (e) => {
      e.preventDefault();
      this.setChangeConfig();
    });
  },
  submMemberRights() {
    staticpage({
      key_admin: $.cookie('ukey'),
      tid: 1,
      title: this.$title.val(),
    }).then(json => {
      console.log(json);
      alert(json.msg);
      location.reload();
    }, json => {
      console.log(json);
    });
  },
  setChangeConfig() {
    let par = {};
    const idnumber = this.$idnumber.filter(':checked');
    const birthday = this.$birthday.filter(':checked');
    const setting = {
      idnumber: (idnumber.val() || 0),
      birthday: (birthday.val() || 0),
    };
    const url = this.$url.val();
    par = {
      key_admin: $.cookie('ukey'),
      setting: JSON.stringify(setting),
      url,
    };
    setChangeConfig(par).then(json => {
      console.log(json);
      alert(json.msg);
    }, json => {
      console.log(json);
    });
  },
  getChangeConfig() {
    getChangeConfig({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      let data = json.data;
      if (typeof data === 'object') {
        const idBirth = JSON.parse(data.id_birth);
        this.$idnumber.filter(`:radio[value=${idBirth.idnumber}]`).attr('checked', 'checked');
        this.$birthday.filter(`:radio[value=${idBirth.birthday}]`).attr('checked', 'checked');
        this.$url.val(data.url);
      } else if (typeof data === 'number') {
        this.$idnumber.filter(`:radio[value=${data}]`).attr('checked', 'checked');
      } else if (typeof data === 'string' && data.length > 1) {
        data = JSON.parse(data);
        this.$idnumber.filter(`:radio[value=${data.idnumber}]`).attr('checked', 'checked');
        this.$birthday.filter(`:radio[value=${data.birthday}]`).attr('checked', 'checked');
      }
    }, json => {
      console.log(json);
    });
  },
};
modifyidcard.init();
