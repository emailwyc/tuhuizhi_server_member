require('../../scss/Yadmin/banner.scss');
import { bannerList, bannerDel, bannerUp } from '../model/Ycurrency';
const $ = window.$;
require('../modules/cookie')($);
const banner = {
  init() {
    this.bannerList();
    this.initDom();
    this.initEvent();
    this.state = {
      ctg: {},
    };
    console.log(this.state.ctg.id);
  },
  initDom() {
    this.$tbody = $('.table tbody');
    this.$all = $('.all');
    this.$allDel = $('.allDel');
  },
  initEvent() {
    // this.$all.on('click', () => {
    //   if (this.$all.prop('checked')) {
    //     $('input[name=allInput]').prop('checked', true);
    //   } else {
    //     $('input[name=allInput]').prop('checked', false);
    //   }
    // });
    // this.$tbody.on('click', $('input[name=allInput]'), () => {
    //   if ($('input[name=allInput]').length === $('input[name=allInput]:checked').length) {
    //     this.$all.prop('checked', true);
    //   } else {
    //     this.$all.prop('checked', false);
    //   }
    // });

    this.$tbody.on('click', 'a.del', (e) => {
      const target = $(e.target);
      this.state.ctg = {
        id: target.data('id'),
      };
      if (confirm('确定要删除吗')) {
        this.bannerDel();
      } else {
        alert('已取消删除');
      }
    });

    this.$tbody.on('click', 'a.top', (e) => {
      const target = $(e.target);
      this.state.ctg = {
        id: target.data('id'),
      };
      this.bannerUp();
    });
    // this.$allDel.on('click', () => {
    //   let valstr = '';
    //   $.map($('input[name=allInput]:checked'), (i) => {
    //     console.log($(i).val());
    //     valstr += `${$(i).val()},`;
    //   });
    //   if (valstr.length > 0) {
    //     valstr = valstr.substring(0, valstr.length - 1);
    //   }
    //   this.bannerDel(valstr);
    // });
  },

  bannerUp() {
    bannerUp({
      key_admin: $.cookie('ukey'),
      banner_id: this.state.ctg.id,
    }).then(json => {
      console.log(json);
      alert(json.msg);
    }, json => {
      console.log(json);
    });
  },
  bannerList() {
    bannerList({
      key_admin: $.cookie('ukey'),
    }).then(json => {
      console.log(json);
      let tr = '';
      $.each(json.data, (i, v) => {
        tr += `<tr>
          <td>
            ${v.id}
          </td>
          <td>
            ${v.banner_name}
          </td>
          <td>
            <img src="${v.url}" alt="banner"/>
          </td>
          <td>
          ${v.jump_url}
          </td>
          <td>
            <a href="/Yadmin/addBanner?id=${v.id}" data-id="${v.id}">编辑</a>
            <a href="javascript:;" data-id="${v.id}" class="top">置顶</a>
            <a href="javascript:;" data-id="${v.id}" class="del">删除</a>
          </td>
        </tr>`;
      });
      this.$tbody.html(tr);
    }, json => {
      console.log(json);
    });
  },
  bannerDel(valstr) {
    bannerDel({
      key_admin: $.cookie('ukey'),
      banner_id: this.state.ctg.id || valstr,
    }).then(json => {
      console.log(json);
      alert(json.msg);
      this.bannerList();
    }, json => {
      alert(json.msg);
    });
  },
};
banner.init();
