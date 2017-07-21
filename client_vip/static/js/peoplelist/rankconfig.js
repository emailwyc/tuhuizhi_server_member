require('../../scss/peoplelist/rankconfig.scss');
  // 接口配置, editCardLevel
import { memberLevelList, createMemberLevel, delCardLevel, editCardLevel } from '../model';

const $ = window.$;
require('../modules/cookie')($);
const keyadmin = $.cookie('ukey'); // key_admin
// console.log(keyadmin);
const userList = {
  init() {
    this.initDom();
    this.initstate();
    this.initEvent();
  },
  initDom() {
    this.$addrank = $('.addrank');       // 添加卡样按钮
    this.$tbody = $('tbody');            // tbody，用作添加数据 和 删除操作
    this.$rankbox = $('.rankbox');       // 遮罩层
    this.$codeinput = $('.codeinput');   // 卡样编码输入框
    this.$nameinput = $('.nameinput');   // 卡样名称输入框
    this.$cancel = $('.cancel');         // 取消添加（叉号）
    this.$submit = $('.submit');         // 添加提交按钮
    this.$editbox = $('.editbox');       // 编辑盒
    this.$editcancel = $('.editcancel'); // 编辑取消叉号
    this.$cancelbtn = $('.cancelbtn');   // 编辑取消按钮
    this.$submitbtn = $('.submitbtn'); // 编辑提交按钮
    this.$editcode = $('.editcode');     // 编辑编码输入框
    this.$editname = $('.editname');     // 编辑名称输入框
    this.carid = '';
  },
  initEvent() {
    this.$cancel.on('click', () => {     // 取消添加
      this.$codeinput.val('');
      this.$nameinput.val('');
      this.$rankbox.css({ display: 'none' });
    });
    this.$addrank.on('click', () => {    // 显示遮罩层
      this.$rankbox.css({ display: 'block' });
    });
    this.$submit.on('click', () => {     // 提交
      const codenum = this.$codeinput.val();
      const codename = this.$nameinput.val();
      console.log(codename, codenum);
      if (codenum === '') {
        alert('编码不能为空');
      } else if (codename === '') {
        alert('卡样名称不能为空');
      } else {
        // 调用添加接口，成功之后
        createMemberLevel({
          key_admin: keyadmin,
          code: codenum,
          level: codename,
        }).then(json => {
          console.log(json);
          alert(json.msg);
          location.reload();
        }, json => {
          alert(json.msg);
          console.log(json);
        });
      }
    });
    this.$tbody.on('click', event => {
      const even = event || window.event;
      const target = even.target;
      // console.log(target.parentNode.parentNode);
      // console.log(event);
      // console.log(target.id);
      this.carid = target.id;
      if (target.className === 'delbtn') {
        if (confirm('确定要删除吗')) {
          delCardLevel({                // 删除接口
            key_admin: keyadmin,
            id: this.carid || '',
          }).then(json => {
            console.log(json);
            alert(json.msg);
            location.reload();
          }, json => {
            alert(json.msg);
            console.log(json);
          });
        }
      } else if (target.className === 'editbtn') {
        this.$editcode.val($(target).parent().prevAll().eq(1).text());
        this.$editname.val($(target).parent().prevAll().eq(0).text());
        this.$editbox.css({ display: 'block' });
      }
    });
    this.$editcancel.on('click', () => {
      this.$editbox.css({ display: 'none' });
    });
    this.$cancelbtn.on('click', () => {
      this.$editbox.css({ display: 'none' });
    });
    this.$submitbtn.on('click', () => {
      console.log('tijiao');
      if (this.$editname.val() === '' || this.$editcode.val() === '') {
        alert('输入不能为空');
      } else {
        editCardLevel({
          key_admin: keyadmin,
          id: this.carid || '',
          level: this.$editname.val(),
          code: this.$editcode.val(),
        }).then(json => {
          alert(json.msg);
          location.reload();
        }, json => {
          alert(json.msg);
        });
      }
    });
  },
  initstate() {
    //  调接口初始化数据
    memberLevelList({
      key_admin: keyadmin,
    }).then(json => {
      console.log('成功', json);
      const data = json.data;
      const len = data.length;
      let str = '';
      if (len === 0) {
        this.$tbody.append('暂无数据');
      } else {
        for (let i = 0; i < len; i++) {
          str += `<tr><td>${data[i].code}</td>
                  <td>${data[i].level}</td>
                  <td>
                    <a href="javascript:;" id=${data[i].id} class="editbtn">编辑</a>
                    <a href="javascript:;" id=${data[i].id} class="delbtn">删除</a>
                  </td>
                  </tr>`;
        }
        this.$tbody.append(str);
      }
    }, json => {
      console.log('失败', json);
      this.$tbody.append(json.msg);
    });
  },
};
userList.init();
