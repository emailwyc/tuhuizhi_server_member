require('../../scss/mall/editGift.scss');
import { getUploadToken } from '../model';
import { integralListOnce, integralOperation, cardTypeList, integralTypeList } from '../model/mall';
import { out } from '../modules/out.js';
const $ = window.$;
require('../modules/cookie')($);
const stor = sessionStorage;
const conf = window.conf;

const UM = window.UM;
const um = UM.getEditor('myEditor', {
  initialFrameHeight: 300,
  autoHeightEnabled: false,
  focus: false,
  imageScaleEnabled: false,
});

// 专区兑换
const KEYADMIN = {
  taiguli: 'c24bb91b6766b3c9c430c776cee9e7cf',
  taiguliceshi: '7405e4c36390377fa9f6f13eb503d437',
  zhihuitu: '202cb962ac59075b964b07152d234b70',
};

if ($.cookie('ukey') === KEYADMIN.taiguli || $.cookie('ukey') === KEYADMIN.taiguliceshi || $.cookie('ukey') === KEYADMIN.zhihuitu) {
  $('.vip-area').show();
}

const editGift = {
  init() {
    this.initDom();
    this.initEvent();
    getUploadToken().then(json => {
      stor.setItem('token', json.data);
      window.QINIU_TOKEN = json.data;
      window.QINIU_BUCKET_DOMAIN = 'img.rtmap.com';
    });
    this._integralTypeList();
    this._cardTypeList();
  },
  initDom() {
    this.$out = $('.out');
    this.$type = $('.type');
    this.$exchangeRadios = $('.form-check-input:radio[name=exchangeRadios]');
    this.$radios1 = $('#exchangeRadios1');
    this.$radios2 = $('#exchangeRadios2');
    this.$exchangeInput = $('.exchange-input');
    this.$exampleNumber = $('#example-number-input');
    this.$subBtn = $('.subBtn');
    this.$sort = $('.sort');
    this.$checkBox = $('.check-box');
    this.$vipArea = $('.vip-area');
    this.$inputVip = $('#inputVip');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });

    this.$exchangeRadios.on('change', (e) => {
      const ele = $(e.target).attr('id');
      if (ele === 'exchangeRadios1') {
        this.$exampleNumber.attr('disabled', false);
        this.$checkBox.find('.exchange-input').attr('disabled', true);
      } else {
        this.$checkBox.find('.exchange-input').attr('disabled', false);
        this.$exampleNumber.attr('disabled', true);
      }
    });

    this.$inputVip.on('change', (e) => {
      const ele = $(e.target);
      if (ele.val() === '1') {
        // 开启vip
        ele.val('2');
      } else {
        ele.val('1');
      }
    });

    this.$subBtn.on('click', () => {
      this._integralOperation();
    });
  },
  _cardTypeList() {
    cardTypeList({
      key_admin: $.cookie('ukey'),
    }).then((json) => {
      console.log(json);
      const data = json.data;
      $.each(data, (i, v) => {
        const tem = `<label class="form-check-label" for="exchangeCheck${i}">
          <input class="form-check-input exchange-input" type="checkbox" name="exchangeRadios1"
          id="exchangeCheck${i}" value="2">${v.name}
        </label>
        <input class="form-control exchange-input" type="number" value="">`;
        this.$checkBox.append(tem);
      });
      this.$checkBox.append('<label for="example-number-input" class="col-form-label">积分</label>');
      this._integralListOnce();
    }).catch(err => console.log(err.msg));
  },
  _integralTypeList() {
    integralTypeList({
      key_admin: $.cookie('ukey'),
    }).then((json) => {
      console.log(json);
      const data = json.data;
      data.forEach((v) => {
        const tmp = `<option data-id="${v.id}">${v.type_name}</option>`;
        this.$type.append(tmp);
      });
    }).catch(err => console.log(err.msg));
  },
  _integralListOnce() {
    integralListOnce({
      key_admin: $.cookie('ukey'),
      pid: conf.id,
      buildid: conf.buildid,
    }).then((json) => {
      console.log(json);
      const data = json.data;
      const typeList = this.$type.children();
      $.each(typeList, (i, v) => {
        const id = $(v).attr('data-id');
        if (id === data.type_id || ('' + id) === data.type_id) {
          $(v).attr('selected', 'selected');
        }
      });
      this.$sort.val(data.des);
      // if (data.activity_type === 'ERP_YX') {
      //   this.$exchangeRadios.off('change').attr('disabled', true);
      //   this.$exampleNumber.parent().append('<label for="example-number-input" style="color:red" class="col-form-label">第三方平台不允许切换</label>');
      // }
      if (data.discount === '1' || data.discount === 1) {
        this.$radios1.attr('checked', true);
        this.$exampleNumber.attr('disabled', false).val(parseInt(data.integral, 10));
        this.$checkBox.find('.exchange-input').attr('disabled', true);
      } else if (data.discount === '2' || data.discount === 2) {
        this.$radios2.attr('checked', true);
        this.$checkBox.find('.exchange-input').attr('disabled', false);
        this.$exampleNumber.attr('disabled', true);

        // const str = data.integral;
        // const obj = {};
        // const reg = /[^,]+:[^,]+/g;
        // const arr = str.match(reg);
        // if (arr) {
        //   arr.forEach((item) => {
        //     const tempArr = item.split(':');
        //     const key = decodeURIComponent(tempArr[0]);
        //     const val = decodeURIComponent(tempArr[1]);
        //     obj[key] = val;
        //   });
        // }
        const tempCheck = this.$checkBox.find('.exchange-input:checkbox');
        $.each($(tempCheck), (i, v) => {
          $.each(data.integral, (j, m) => {
            const type = ($(v).parent().text()).trim();
            const tempNum = $(v).parent().next();
            if (type === j) {
              $(v).attr('checked', true);
              tempNum.val(parseInt(m, 10));
            }
          });
        });
      } else {
        this.$radios1.attr('checked', true);
        this.$exampleNumber.attr('disabled', false);
        this.$checkBox.find('.exchange-input').attr('disabled', true);
      }
      um.setContent(data.content);

      try {
        if (data.vip_area === '2') {
          this.$inputVip.attr('checked', true);
        }
      } catch (err) {
        console.log(err);
      }
    }).catch((err) => {
      console.log(err.msg);
      // const giftInfo = JSON.parse(window.sessionStorage.getItem('giftInfo'));
      // const activityType = giftInfo.activityType;
      // if (activityType === 'ERP_YX') {
      //   this.$exchangeRadios.off('change').attr('disabled', true);
      //   this.$exampleNumber.parent().append('<label for="example-number-input" style="color:red" class="col-form-label">第三方平台不允许切换</label>');
      //   this.$radios1.attr('checked', true);
      //   this.$exampleNumber.attr('disabled', false);
      //   this.$checkBox.find('.exchange-input').attr('disabled', true);
      // }
      this.$radios1.attr('checked', true);
      this.$exampleNumber.attr('disabled', false);
      this.$checkBox.find('.exchange-input').attr('disabled', true);
    });
  },
  _integralOperation() {
    const giftInfo = JSON.parse(window.sessionStorage.getItem('giftInfo'));
    const des = this.$sort.val() || 1000;
    const typeId = this.$type.find('option:selected').attr('data-id');
    const prizeName = giftInfo.prizeName;
    const activityType = giftInfo.activityType;
    const content = um.getContent();
    const tempId = this.$exchangeRadios.filter(':checked').attr('id');
    const vipArea = this.$inputVip.val();
    let discount = null;
    let integral = null;
    if (tempId === 'exchangeRadios1') {
      discount = 1;
      integral = this.$exampleNumber.val();
      if (integral.trim() === '') {
        return alert('积分不能为空');
      }
    } else {
      discount = 2;
      integral = {};
      const tempCheck = this.$checkBox.find('.exchange-input').filter(':checkbox:checked');
      $.each(tempCheck, (i, v) => {
        const tempType = ($(v).parent().text()).trim();
        const tempNum = $(v).parent().next().val();
        integral[tempType] = tempNum;
      });
      let hasProp = true;
      for (const prop in integral) {
        if (integral[prop] === '') {
          hasProp = true;
          break;
        } else {
          hasProp = false;
        }
      }
      if (hasProp) {
        return alert('积分不能为空');
      }
    }
    integralOperation({
      key_admin: $.cookie('ukey'),
      pid: conf.id,
      des,
      integral,
      type_id: typeId,
      prize_name: prizeName,
      activity_type: activityType,
      discount,
      buildid: conf.buildid,
      content,
      vip_area: vipArea,
    }).then((json) => {
      alert(json.msg);
      location.href = 'gift';
    }).catch(err => alert(err.msg));
  },
};
editGift.init();
