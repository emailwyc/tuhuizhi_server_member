require('../../scss/dashboard/memberformlist.scss');
const $ = window.$;
import { formList, createForm, deleteForm } from './model';
const memberformlist = {
  init() {
    this.initDom();
    this.getlist();
    this.initEvent();
  },
  initDom() {
    this.$tbody = $('tbody');
    this.$newbtn = $('.newbtn');                    // 新建
    this.$addkey = $('.addkey');                    // 添加key
    this.$delkey = $('.delkey');                    // 删除key
    this.$useredit = $('.useredit');                // 查看
    this.$userdel = $('.userdel');                  // 删除
    this.$newbox = $('.newbox');                    // 新建-盒子
    this.$checkedbox = $('.checkedbox');            // 查看-盒子
    this.$cancel = $('.cancel');                    // 取消
    this.$submitbtn = $('.submitbtn');              // 提交
    this.$checksure = $('.checksure');              // 查看-确定
    this.basedata = '';
  },
  initEvent() {
    // 删除
    this.$tbody.on('click', '.userdel', (event) => {
      console.log(event.target.id);
      if (confirm('你确定要删除吗？')) {
        this.dellist(event.target.id);
      } else {
        console('取消删除');
      }
    });
    // 查看
    this.$tbody.on('click', '.useredit', (event) => {
      console.log(event.target.id);
      this.checkinfo(this.basedata, event.target.id);
    });
    // 新建
    this.$newbtn.on('click', () => {
      this.$newbox.fadeIn('fast');
    });
    // 添加key
    this.$addkey.on('click', () => {
      $('.addlabel').append(`<p><span>默认值：</span>
              <input type="number" name="defaultkey" value="" class="addinput" placeholder="默认key">
              <input type="text" name="defaultname" value="" class="addinput" placeholder="默认名">
              <a href="javascript:;"class="delkey">删除</a>
                  </p>`);
    });
    // 删除key
    $('.addlabel').on('click', '.delkey', (event) => {
      $(event.target).parents('p').remove();
    });
    // 取消
    this.$cancel.on('click', () => {
      this.$newbox.fadeOut('fast');
      this.clearinput();
    });
    // 查看-确定
    this.$checksure.on('click', () => {
      this.$checkedbox.slideUp('fast');
    });
    // 提交
    this.$submitbtn.on('click', () => {
      const name = $('.newbox').find('input[name=name]').val();
      const types = $('.newbox').find('select').val();
      const keys = $('.newbox').find('input[name=keys]').val();
      const functions = $('.newbox').find('input[name=functions]').val();
      console.log(name, types, keys, functions);
      let defaultarr = '';
      this.defaultswitch = 0;
      if ($('.addlabel').find('p').length !== 0) {
        $.map($('.addlabel').find('p'), n => {
          if ($(n).find('input[type=text]').val() !== '' &&
              $(n).find('input[type=number]').val() !== '') {
            this.defaultswitch = 1;
          } else {
            this.defaultswitch = 2;
          }
        });
        if (this.defaultswitch === 1) {
          $.map($('.addlabel').find('p'), n => {
            defaultarr += `${$(n).find('input[type=number]').val()}|
                           ${$(n).find('input[type=text]').val()},`;
          });
        }
      }
      if (name !== '' && types !== '' && keys !== '') {
        if (this.defaultswitch === 1) { // 提交带有default;
          console.log(defaultarr);
          this.addform({
            content: name,
            content_type: types,
            content_key: keys,
            function_name: functions,
            content_default: defaultarr,
          });
        } else if (this.defaultswitch === 2) {
          alert('默认名和默认key均不能为空');
        } else { // 提交不带有default
          this.addform({
            content: name,
            content_type: types,
            content_key: keys,
            function_name: functions,
            content_default: '',
          });
        }
      } else if (name === '') {
        alert('请输入名字');
      } else if (types === '') {
        alert('请输入类型');
      } else if (keys === '') {
        alert('请输入key');
      }
    });
  },
  // 获取列表
  getlist() {
    formList().then(json => {
      console.log(json);
      this.basedata = json.data;
      let html = '';
      $.map(json.data, (n, i) => {
        html += `<tr>
          <td class="name">${n.content}</td>
          <td class="">${n.content_key}</td>
          <td class="">${n.content_type}</td>
          <td class="">
            <a href="javascript:;"class="useredit" id = ${i}>查看</a>
            <a href="javascript:;"class="userdel" id = ${n.id}>删除</a>
          </td>
        </tr>`;
      });
      this.$tbody.html(html);
    }, json => {
      console.log(json);
      this.$tbody.html(json.msg);
    });
  },
  // 新建表单
  addform(data) {
    createForm(data).then(json => {
      console.log(json);
      this.$newbox.fadeOut('fast');
      this.clearinput();
      window.location.reload();
      alert(json.msg);
    }, json => {
      console.log(json);
      alert(json.msg);
    });
  },
  // 查看信息
  checkinfo(data, dataid) {
    console.log(data[dataid]);
    let html = '';
    $('.checkedbox').find('input[name=name]').val(data[dataid].content);
    $('.checkedbox').find('input[name=types]').val(data[dataid].content_type);
    $('.checkedbox').find('input[name=key]').val(data[dataid].content_key);
    if (data[dataid].content_default) {
      $.map(data[dataid].content_default, n => {
        html += `<p>${n.default_content}(${n.default_content_key})</p>`;
      });
    }
    $('.choices').html(html);
    this.$checkedbox.slideDown('fast');
  },
  dellist(id) {
    console.log(id);
    deleteForm({
      id,
    }).then(json => {
      console.log(json);
      window.location.reload();
      alert(json.msg);
    }, json => {
      console.log(json);
    });
  },
  clearinput() {
    $.map($('.newbox').find('input'), n => {
      $(n).val('');
    });
    $('.addlabel').html('<span>key</span><input type="text" name="" value="" placeholder="必填项">');
  },
};
memberformlist.init();
