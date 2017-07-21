/* 建筑物管理 */
require('../../scss/buildManage/buildManage.scss');
import { buildList, editBuild, delBuild, getbuild } from '../model/buildManage';
import { getUploadToken } from '../model/';   // 七牛token
const Qiniu = window.Qiniu;
const $ = window.$;
require('../modules/cookie')($);
const keyadmin = $.cookie('ukey'); // key_admin
console.log(keyadmin);
const storage = window.sessionStorage;
const buildManage = {
  init() {
    this.initDom();
    this.file = '';
    getUploadToken().then(d => {
      // console.log(d);
      this.uploader(d.data);
    });
    this.buildList();
    this.initEvent();
  },
  initDom() {
    this.$tbody = $('tbody');
    this.$editSave = $('.editSave');
    this.$image = $('#image');
  },
  initEvent() {
    // 编辑按钮
    this.$tbody.on('click', '.editBtn', (event) => {
      const e = event || window.event;
      const target = e.target || e.srcElement;
      getbuild({
        key_admin: keyadmin,
        id: target.id,
      }).then(json => {
        if (json.code === 200) {
          this.editDom(json.data);
        } else {
          alert(`执行错误，错误代码为：${json.code}`);
        }
      }, json => {
        alert(`执行错误，错误代码为：${json.code}`);
      });
    });
    // 删除按钮
    this.$tbody.on('click', '.delBtn', (event) => {
      const e = event || window.event;
      const target = e.target || e.srcElement;
      if (confirm('确定要执行此操作吗?')) {
        this.delBuild(target.id);
      } else {
        return;
      }
    });
    // 编辑保存
    this.$editSave.on('click', () => {
      if ($('input[type="radio"]:checked').val() === '1') {
        if ($('.shortname').val() === '') {
          alert('开启推荐，则简称不能为空');
        } else {
          this.editBuild();
        }
      } else {
        this.editBuild();
      }
    });
  },
  // 编辑接口
  editBuild() {
    editBuild({
      key_admin: keyadmin,
      id: this.$editSave.attr('id'),
      cus_id: $('.editMBuildId').val(),
      url: $('.editBuildUrl').val(),
      introduction: $('.editIntroduction').val(),
      img: $('.editBuildImg').attr('src'),
      short_name: $('.shortname').val(),
      promote: $('input[type="radio"]:checked').val(),
    }).then(json => {
      console.log(json);
      if (json.code === 200) {
        window.location.reload();
      } else {
        alert(`执行错误，错误代码为：${json.code}`);
      }
    }, json => {
      alert(`执行错误，错误代码为：${json.code}`);
    });
  },
  // 建筑列表
  buildList() {
    buildList({
      key_admin: keyadmin,
      childid: storage.getItem('childid') || '',
    }).then(json => {
      if (json.code === 200) {
        this.renderDom(json.data);
      } else {
        alert(`执行错误，错误代码为：${json.code}`);
      }
    }, json => {
      alert(`执行错误，错误代码为：${json.code}`);
    });
  },
  delBuild(id) {
    delBuild({
      key_admin: keyadmin,
      id,
    }).then(json => {
      console.log(json);
      window.location.reload();
    }, json => {
      console.log(json);
      alert('操作失败');
    });
  },
  renderDom(data) {
    let html = '';
    $.map(data, n => {
      html += `<tr>
        <td title="${n.buildid}">
          <p class="buildId">${n.buildid !== null ? n.buildid : ''}</p>
        </td>
        <td title="${n.name}"><p class="buildName">${n.name !== null ? n.name : ''}</p></td>
        <td><p class="buildShortName">${n.short_name !== null ? n.short_name : ''}</p></td>
        <td title="${n.customerbid}">
          <p class="marketBuildId">${n.customerbid !== null ? n.customerbid : ''}</p>
        </td>
        <td title="经度：${n.long},纬度：${n.lat}">
          <span class="latAndLong">${n.long !== null ? n.long : '00'}</span>,
          <span class="latAndLong">${n.lat !== null ? n.lat : '00'}</span>
        </td>
        <td class="img_td">
          <img src="${n.buildimg !== null ? n.buildimg : ''}" alt="" title="商场图片" class="buildImg">
        </td>
        <td title="${n.introduction}">
          <p class="introduction">${n.introduction !== null ? n.introduction : ''}</p>
        </td>
        <td title="${n.url}" class="buildUrl">
          <p class="urltext">${n.url !== null ? n.url : ''}</p>
        </td>
        <td>${n.is_promote !== null && n.is_promote === '1' ? '是' : '否'}</td>
        <td>
          <a href="javascript:;" class="editBtn" id="${n.id}">编辑</a>
          <a href="javascript:;" class="delBtn" id="${n.id}">删除</a>
        </td>
      </tr>`;
    });
    this.$tbody.html(html);
  },
  editDom(data) {
    $('.header').find('h3').html('编辑');
    $('.editBuildId').html(data.buildid);
    $('.editBuildName').html(data.name);
    $('.editMBuildId').val(data.customerbid);
    $('.editBuildUrl').val(data.url);
    $('.editIntroduction').val(data.introduction);
    $('.shortname').val(data.short_name);
    $('.editBuildImg').attr('src', data.buildimg);
    $('.editSave').attr('id', data.id);
    if (data.is_promote === '1') {
      $(`input[name='isopen'][value=${data.is_promote}]`).attr('checked', true);
    }
    $('.list').css({ display: 'none' });
    $('.editWrap').css({ display: 'block' });
  },
  uploader(token) {
    // domain 为七牛空间（bucket)对应的域名，选择某个空间后，可通过"空间设置->基本设置->域名设置"查看获取
    // uploader 为一个plupload对象，继承了所有plupload的方法，参考http://plupload.com/docs
    this.qiniuupload(token);
  },
  qiniuupload(token) {
    Qiniu.uploader({
      runtimes: 'html5,flash,html4',    // 上传模式,依次退化
      browse_button: 'pickfiles',       // 上传选择的点选按钮，**必需**
      // uptoken_url: token,            // Ajax请求upToken的Url，**强烈建议设置**（服务端提供）
      uptoken: token,                   // 若未指定uptoken_url,则必须指定 uptoken ,uptoken由其他程序生成
      unique_names: true,               // 默认 false，key为文件名。若开启该选项，SDK为自动生成上传成功后的key（文件名）。
      // save_key: true,                // 默认 false。若在服务端生成uptoken的上传策略中指定了 `sava_key`，则开启，SDK
                                        // 会忽略对key的处理
      domain: 'http://qiniu-plupload.qiniudn.com/',   // bucket 域名，下载资源时用到，**必需**
      get_new_uptoken: false,           // 设置上传文件的时候是否每次都重新获取新的token
      // container: 'container',        // 上传区域DOM ID，默认是browser_button的父元素
      max_file_size: '100mb',           // 最大文件体积限制
      flash_swf_url: 'js/plupload/Moxie.swf',  // 引入flash,相对路径
      max_retries: 3,                   // 上传失败最大重试次数
      dragdrop: true,                   // 开启可拖曳上传
      drop_element: 'container',        // 拖曳上传区域元素的ID，拖曳文件或文件夹后可触发上传
      chunk_size: '4mb',                // 分块上传时，每片的体积
      auto_start: true,                 // 选择文件后自动上传，若关闭需要自己绑定事件触发上传
      init: {
        FilesAdded: (up, files) => {
          console.log(files);
            // plupload.each(files, function(file) {
            //     // 文件添加进队列后,处理相关的事情
            // });
        },
        BeforeUpload: (up, file) => {
          console.log(file);
               // 每个文件上传前,处理相关的事情
          // this.contactusCode(file);
        },
        UploadProgress: (up, file) => {
          // const timestamp =Date.parse(new Date());
          console.log(file);
               // 每个文件上传时,处理相关的事情
        },
        FileUploaded: (up, file, info) => {
          console.log(info);
          this.file = `https://img.rtmap.com/${file.target_name}`;
          this.$image.attr('src', this.file);
          console.log(`${this.file}`);
        },
        Error: (up, err, errTip) => {
          console.log(errTip);
               // 上传出错时,处理相关的事情
        },
        UploadComplete: (up, file, data) => {
               // 队列文件处理完毕后,处理相关的事情
          console.log(file);
          console.log(data);
        },
      },
    });
  },
};
buildManage.init();
