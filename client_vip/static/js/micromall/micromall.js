require('../../scss/micromall/micromall.scss');
import { navigationlist, navigationTop, navigationFoot,
  navigationStatus, adList, getBgColor } from '../model/micromall';
const $ = window.$;
// const conf = window.conf;
require('../modules/cookie')($);
const micromall = {
  init() {
    this.initDom();
    this.initEvent();
    if (!$.cookie('ukey')) {
      location.href = '/user/login';
      return;
    }
    this.navigationlist();
    this.navigationTop();
    this.navigationFoot();
    this.adList();
    this.statue = {
      ctg: {},
    };
    this.getBgColor();
  },
  initDom() {
    this.$tbody = $('.setconfig .table tbody');
    this.$topad = $('.topad');
    this.$bottomad = $('.bottomad');
    this.$domain = $('.domain');
  },
  initEvent() {
    this.$tbody.on('click', 'a.btn', (e) => {
      const target = $(e.target);
      // target.html(target.html() === '已启用' ? '已停用' : '已启用');
      e.preventDefault();
      this.statue.ctg = {
        type: target.data('type'),
      };
      console.log(this.statue.ctg.type);
      // this.navigationStatus(this.statue.ctg.type);
      navigationStatus({
        key_admin: $.cookie('ukey'),
        position: this.statue.ctg.type,
      }).then(json => {
        console.log(json);
        target.html(target.html() === '已启用' ? '已停用' : '已启用');
        location.reload();
      }, json => {
        console.log(json);
      });
    });
  },
  navigationlist() {
    navigationlist({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      let tr = '';
      $.each(json.data, (i, v) => {
        tr += `<tr><td>${v.name}</td><td><a href="${v.url}?position=${v.position}" >编辑</a></td>
        <td>${v.position === 'center' ? '' : `<a href="javascript:;"
        class="btn btn-link" data-type="${v.position}">
        ${v.status === '1' ? '已启用' : '已停用'}</a>`}</td></tr>`;
      });
      this.$tbody.html(tr);
    }, json => {
      console.log(json);
    });
  },

  navigationTop() {
    navigationTop({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      if (json.data.status === '1') {
        this.$topad.css('display', 'block').find('img').attr('src', json.data.url);
        return;
      }
    }, json => {
      console.log(json);
    });
  },
  navigationFoot() {
    navigationFoot({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      if (json.data.status === '1') {
        this.$bottomad.css('display', 'block').find('img').attr('src', json.data.url);
      }
    }, json => {
      console.log(json);
    });
  },

  adList() {
    adList({
      key_admin: $.cookie('ukey'),
      position: 'center',
    }).then(json => {
      console.log(json);
      let data = '';
      $.each(json.data, (i, v) => {
        data += `<div class="facilityname${i}" style="background:${v.property}"><p>
        <a href="${v.link}">${v.name}</a></p></div>`;
      });
      this.$domain.html(data);
    }, json => {
      console.log(json);
    });
  },

  getBgColor() {
    getBgColor({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      $('.domain').css('backgroundColor', `${json.data.bg_color}`);
    }, json => {
      console.log(json);
    });
  },
};
micromall.init();
