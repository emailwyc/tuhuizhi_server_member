require('../../scss/micromall/facility.scss');
require('../bootstrap/modal');
import { getUploadToken, adList, adUpdate, adOperate, adDel,
editBackground, getBgColor } from '../model/micromall';
import { upload } from '../modules/qiniuupload';
const UM = window.UM;
const $ = window.$;
const conf = window.conf;
const um = UM.getEditor('myEditor', {
  initialFrameWidth: 570,
  initialFrameHeight: 360,
  autoHeightEnabled: false,
  focus: true,
});
um.ready(() => {
  // um.setContent();
  um.getContent();
});
require('../modules/cookie')($);
const facility = {
  init() {
    this.initDom();
    this.initEvent();
    getUploadToken().then(d => {
      upload(d.data);
      window.QINIU_TOKEN = d.data;
      window.QINIU_BUCKET_DOMAIN = 'img.rtmap.com';
    });
    this.adList();
    this.statue = {
      ctg: {},
    };
    this.getBgColor();
  },
  initDom() {
    this.$domain = $('.domain');
    this.$tbody = $('.table tbody');
    this.$name = $('.name');
    this.$author = $('.author');
    this.$link = $('.link');
    this.$mycolor = $('.mycolor');
    this.$save = $('.save');
    this.$msg = $('.msg');
    this.$bgcolor = $('.bgcolor');
    this.$savebg = $('.savebg');
  },
  initEvent() {
    this.$tbody.on('click', 'a.edit', (e) => {
      const target = $(e.target);
      console.log(target);
      this.statue.ctg = {
        id: target.data('id'),
        sort: target.data('sort'),
      };
      if (this.statue.ctg.id) {
        this.adUpdate();
        console.log(1);
      } else {
        this.$mycolor.val('');
        this.$link.val('');
        this.$name.val('');
        um.ready(() => {
          um.setContent('');
        });
      }
    });
    this.$tbody.on('click', 'a.empty', (e) => {
      const target = $(e.target);
      console.log(target);
      this.statue.ctg = {
        id: target.data('id'),
      };
      this.adDel();
    });
    this.$save.on('click', () => {
      this.adOperate();
    });

    this.$savebg.on('click', () => {
      this.editBackground();
    });
  },
  adList() {
    adList({
      key_admin: $.cookie('ukey'),
      position: conf.position,
    }).then(json => {
      console.log(json);
      let data = '';
      let tr = '';
      $.each(json.data, (i, v) => {
        data += `<div class="facilityname${i}" style="background:${v.property};"><p>
        <a href="javascript:;">${v.name}</a></p></div>`;
        tr += `<tr><td>${v.sort}</td><td>${v.name}</td><td>${v.content}</td>
        <td>
        <a href="javascript:;" data-toggle="modal" data-id="${v.id}" data-sort="${v.sort}"
        data-target="#myModal" class="edit">编辑</a>
        <a href="javascript:;" class="empty" data-id="${v.id}">清空</a></td></tr>`;
      });
      this.$domain.html(data);
      this.$tbody.html(tr);
    }, json => {
      console.log(json);
    });
  },

  adUpdate() {
    adUpdate({
      key_admin: $.cookie('ukey'),
      ad_id: this.statue.ctg.id,
      position: conf.position,
    }).then(json => {
      console.log(json);
      this.$name.val(json.data.name);
      this.$link.val(json.data.link);
      this.$author.val(json.data.author);
      this.$mycolor.val(json.data.property);
      um.ready(() => {
        um.setContent(json.data.content);
      });
    }, json => {
      console.log(json);
    });
  },

  adOperate() {
    adOperate({
      key_admin: $.cookie('ukey'),
      status: '2',
      ad_id: this.statue.ctg.id,
      position: conf.position,
      name: this.$name.val(),
      author: this.$author.val(),
      link: this.$link.val(),
      property: this.$mycolor.val(),
      content: um.getContent(),
      sort: this.statue.ctg.sort,
    }).then(json => {
      console.log(json);
      alert(json.msg);
      location.reload();
    }, json => {
      console.log(json);
      alert(json.msg);
    });
  },

  adDel() {
    adDel({
      key_admin: $.cookie('ukey'),
      ad_id: this.statue.ctg.id,
    }).then(json => {
      console.log(json);
      alert(json.msg);
      location.reload();
    }, json => {
      console.log(json);
      $('#gridSystemModal').modal('hide');
    });
  },

  editBackground() {
    editBackground({
      key_admin: $.cookie('ukey'),
      bg_color: this.$bgcolor.val(),
    }).then(json => {
      console.log(json);
      location.reload();
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
facility.init();
