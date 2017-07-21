require('../../scss/resource/resource.scss');
require('../bootstrap/modal');

import { getStaticList, deleteStaticPage } from '../model/resource';
const $ = window.$;
const main = {
  init() {
    this.getData();
    this.initDom();
    this.eventFun();
  },
  initDom() {
    this.$tbody = $('.table tbody');
    this.$delOk = $('.delOk');
    this.$msg = $('.msg');
    this.$myModal = $('#myModal');
  },
  eventFun() {
    this.$tbody.on('click', '.delete', this.deleteResource);
  },
  getData() {
    getStaticList({
      key_admin: $.cookie('ukey'),
    }).then(result => {
      let html = '';
      result.data.forEach((el) => {
        html += `<tr><td>${el.title}</td><td><input type="text" value="${
          el.url}" readonly /></td><td>
          <a href="/resource/resourceinfo?sid=${el.id}" class="edit">编辑</a>
          <a href="javascript:;" class="delete" data-toggle="modal"
            data-sid=${el.id}>删除</a></td></tr>`;
      });
      this.$tbody.html(html);
    }, error => {
      alert(error.msg);
    });
  },

  deleteResource() {
    main.$myModal.modal('show');
    main.$delOk.bind('click', () => {
      deleteStaticPage({
        key_admin: $.cookie('ukey'),
        sid: $(this).attr('data-sid'),
      }).then(result => {
        console.log(result);
        main.$myModal.modal('hide');
        main.getData();
      }, error => {
        alert(error.msg);
      });
    });
  },
};
main.init();
