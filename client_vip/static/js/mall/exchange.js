require('../../scss/mall/exchange.scss');
import { staticpage, integralsave, integralstatus } from '../model';
import { getIntegralColor, integralColorAdd } from '../model/mall';
import { out } from '../modules/out.js';
const $ = window.$;
require('../modules/cookie')($);
// pubApi.getQinNiuToken.then(token => {
// const UM = window.UM;
const COLORS = {
  blue: '#58a8ff',
  pink: '#ff8e87',
  brown1: '#b29873',
  brown2: '#c49f6b',
  orange: '#ff6905',
  cyan: '#40d3bc',
};
// 隐藏太古里颜色配置
const KEYADMIN = {
  taiguli: 'c24bb91b6766b3c9c430c776cee9e7cf',
  zhihuitu: '202cb962ac59075b964b07152d234b70',
};

if ($.cookie('ukey') === KEYADMIN.taiguli) {
  $('.color-box').hide();
}
const exchange = {
  init() {
    this.initDom();
    this.initEvent();
    this.integralstatus();
    this.colorList();
  },
  initDom() {
    this.$out = $('.out');
    this.$title = $('.title');
    this.$subBtn = $('.subBtn');
    this.$updata = $('.updata');
    this.$inlineRadio1 = $('#inlineRadio1');
    this.$inlineRadio2 = $('#inlineRadio2');
    this.$exampleInputEmail1 = $('#exampleInputEmail1');
    this.$info = $('.info');
    this.$color = $('#colorSelect');
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
      if (this.$inlineRadio1[0].checked && !this.$exampleInputEmail1.val()) {
        this.$info.css('display', 'inline-block');
      } else {
        this.integralsave();
        this._integralColorAdd();
      }
    });
  },
  colorList() {
    // $.each(COLORS, (i, v) => {
    //   const tmp = `<option style="background-color:${v}">${i}</option>`;
    //   this.$color.append(tmp);
    // });
    this._getIntegralColor();
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
  integralsave() {
    integralsave({
      key_admin: $.cookie('ukey'),
      function_name: $('input[name="inlineRadioOptions"]:checked').val() - 0,
      description: this.$exampleInputEmail1.val(),
    }).then(json => {
      console.log(json);
      alert(json.msg);
    }, json => {
      console.log(json);
    });
  },
  integralstatus() {
    integralstatus({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      $(`input:radio[value=${json.data[0].function_name}]`).attr('checked', 'checked');
      this.$exampleInputEmail1.val(json.data[0].description);
    }, json => {
      console.log(json);
    });
  },
  _getIntegralColor() {
    getIntegralColor({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      const data = json.data;
      if (data.function_name !== '') {
        // const color = this.$color.children();
        // $.each(color, (i, v) => {
        //   const id = $(v).text();
        //   if (id === data.function_name) {
        //     $(v).attr('selected', 'selected');
        //   }
        // });
        this.$color.val(data.function_name);
      }
    }).catch(err => console.log(err.msg));
  },
  _integralColorAdd() {
    integralColorAdd({
      key_admin: $.cookie('ukey'),
      color: this.$color.val(),
    }).then(json => {
      console.log(json);
    }).catch(err => alert(err.msg));
  },
};
exchange.init();
