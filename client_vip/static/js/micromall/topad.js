require('../../scss/micromall/microtopad.scss');
require('../bootstrap/modal');
import { getUploadToken, adList, adDel, adOperate, adUpdate, adTop } from '../model/micromall';
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

const microtopad = {
  init() {
    this.initDom();
    this.initEvent();
    this.statue = {
      ctg: {},
    };
    getUploadToken().then(d => {
      upload(d.data);
      window.QINIU_TOKEN = d.data;
      window.QINIU_BUCKET_DOMAIN = 'img.rtmap.com';
    });
    this.adList();
  },
  initDom() {
    this.$tbody = $('.table tbody');
    this.$delOk = $('.delOk');
    this.$name = $('.name');
    this.$link = $('.link');
    this.$author = $('.author');
    this.$image = $('.image');
    this.$subBtn = $('.subBtn');
    this.$save = $('.save');
    this.$msg = $('.msg');
  },
  initEvent() {
    this.$tbody.on('click', 'a.del', (e) => {
      const target = $(e.target);
      this.statue.ctg = {
        id: target.data('id'),
      };
      console.log(this.statue.ctg.id);
    });
    this.$delOk.on('click', () => {
      this.adDel();
    });
    this.$tbody.on('click', ('a.edit'), (e) => {
      const target = $(e.target);
      this.statue.ctg = {
        id: target.data('id'),
      };
      console.log(this.statue.ctg.id);
      this.adUpdate();
    });
    this.$save.on('click', () => {
      this.adOperate();
    });

    this.$tbody.on('click', ('a.setTop'), (e) => {
      const target = $(e.target);
      this.statue.ctg = {
        id: target.data('id'),
      };
      this.adTop();
    });
  },
  adList() {
    adList({
      key_admin: $.cookie('ukey'),
      position: 'top',
    }).then(json => {
      console.log(json);
      let tr = '';
      $.each(json.data, (i, v) => {
        // console.log(v);
        tr += `<tr><td>${i + 1}</td><td>${v.name}</td><td><img src="${v.property}" />
        </td><td><div class="adtext">${v.content}</div>
          </td><td><a href="javascript:;" class="setTop" data-id="${v.id}">置顶</a>
        <a href="javascript:;" data-id="${v.id}" data-toggle="modal" class="edit"
        data-target="#myModal">编辑</a>
        <a href="javascript:;" data-id="${v.id}" class="del"
        data-toggle="modal" data-target="#gridSystemModal">删除</a></td></tr>`;
      });
      this.$tbody.html(tr);
      $('tbody tr').first().find('.setTop').text('').append('<span class="marig"></span>');
    }, json => {
      console.log(json);
    });
  },

  adDel() {
    adDel({
      key_admin: $.cookie('ukey'),
      ad_id: this.statue.ctg.id,
    }).then(json => {
      console.log(json);
      location.reload();
    }, json => {
      console.log(json);
      $('#gridSystemModal').modal('hide');
    });
  },
  adOperate() {
    adOperate({
      key_admin: $.cookie('ukey'),
      status: '2',
      ad_id: this.statue.ctg.id,
      position: conf.position,
      name: this.$name.val(),
      link: this.$link.val(),
      author: this.$author.val(),
      property: this.$image.attr('src'),
      content: um.getContent(),
    }).then(json => {
      console.log(json);
      alert(json.msg);
      location.reload();
    }, json => {
      console.log(json);
      alert(json.msg);
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
      this.$image.attr('src', json.data.property);
      um.ready(() => {
        um.setContent(json.data.content);
        // um.getContent();
      });
    }, json => {
      console.log(json);
    });
  },

  adTop() {
    adTop({
      key_admin: $.cookie('ukey'),
      ad_id: this.statue.ctg.id,
    }).then(json => {
      console.log(json);
      setTimeout(() => {
        this.$msg.html(json.msg);
      }, 1000);
      location.reload();
    }, json => {
      console.log(json);
    });
  },
};
microtopad.init();
