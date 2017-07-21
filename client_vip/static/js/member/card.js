require('../../scss/member/card.scss');
require('../bootstrap/modal');
import { out } from '../modules/out.js';
import { saveMemberCode, getUploadToken, getMemberCode, deleteMemberCode } from '../model/index';
const $ = window.$;
const Qiniu = window.Qiniu;
require('../modules/cookie')($);

const card = {
  init() {
    this.initDom();
    this.initEvent();
    this.file = '';
    getUploadToken().then(d => {
      console.log(d);
      this.uploader(d.data);
    });
    this.getcardImgList();
    this.state = {
      ctg: {
        // cardtypeno: '',
        // cardtypename: '',
        // cardtypeurl: '',
      },
    };
  },
  initDom() {
    this.$out = $('.out');
    this.$cardnumber = $('.cardnumber');
    this.$cardname = $('.cardname');
    this.$inputFiles = $('.files2');
    this.$image = $('#image');
    this.$btnform = $('.btnform');
    this.$tbody = $('.table tbody');
    this.$delOk = $('.delOk');
    this.$myModal = $('#myModal');
    this.$sort = $('.sort');
  },
  initEvent() {
    this.$out.on('click', () => {
      out();
    });
    this.$btnform.on('click', () => {
      this.uploadImage();
    });
    this.$tbody.on('click', ('a.del'), (e) => {
      const target = $(e.target);
      console.log(target);
      this.state.ctg = {
        cardtypeno: target.data('cardtypeno'),
        cardtypename: target.data('cardtypename'),
        cardtypeurl: target.data('cardtypeurl'),
        status: target.data('status'),
        id: target.data('id'),
      };
      console.log(this.state.ctg.cardtypeurl);
      this.$cardnumber.val(this.state.ctg.cardtypeno);
      this.$cardname.val(this.state.ctg.cardtypename);
    });
    this.$delOk.on('click', () => {
      this.deleteMemberCode();
    });
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
      uptoken: token, // 若未指定uptoken_url,则必须指定 uptoken ,uptoken由其他程序生成
      unique_names: true, // 默认 false，key为文件名。若开启该选项，SDK为自动生成上传成功后的key（文件名）。
      // save_key: true,   // 默认 false。若在服务端生成uptoken的上传策略中指定了 `sava_key`，则开启，SDK会忽略对key的处理
      domain: 'http://qiniu-plupload.qiniudn.com/',   // bucket 域名，下载资源时用到，**必需**
      get_new_uptoken: false,  // 设置上传文件的时候是否每次都重新获取新的token
      // container: 'container',           // 上传区域DOM ID，默认是browser_button的父元素
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
      // Key: (up, file) => {
      //   // console.log(file);
      //   // 若想在前端对每个文件的key进行个性化处理，可以配置该函数
      //   // 该配置必须要在 unique_names: false , save_key: false 时才生效
      //   const key = "";
      //   // do something with key here
      //   return key
      // }
      },
    });
  },
  uploadImage() {
    saveMemberCode({
      key_admin: $.cookie('ukey'),
      code: this.$cardnumber.val(),
      name: this.$cardname.val(),
      imgurl: this.file || this.state.ctg.cardtypeurl,
      id: this.state.ctg.id || '',
      is_register: $('.register').find('input[name="register"]:checked').val(),
      is_default: $('.default').find('input[name="default"]:checked').val(),
      sort: this.$sort.val(),
    }).then(json => {
      console.log(json);
      alert(json.msg);
      location.href = '/member/card';
    }, json => {
      console.log(json);
    });
  },
  getcardImgList() {
    getMemberCode({
      key_admin: $.cookie('ukey'),
      // tid: 5,
    }).then(json => {
      console.log(json);
      let tr = '';
      $.each(json.data, (i, v) => {
        tr += `<tr>
        <td>${v.code}</td>
        <td>${v.name}</td>
        <td>${v.sort}</td>
        <td>${v.is_register === '1' ? '是' : '否'}</td>
        <td><img src="${v.imgurl}" /></td>
        <td>${v.is_default === '1' ? '是' : '否'}</td>
        <td>
        <a href="cardedit?id=${v.id}"
        class="edit"
        data-id="${v.id}" data-cardtypeno="${v.code}" data-cardtypename="${v.name}"
        data-cardtypeurl="${v.imgurl}" data-sort="${v.sort}" data-register="${v.is_register}"
        data-default="${v.is_default}">编辑</a>
        <a href="javascript:;"
        class="del" data-toggle="modal" data-target="#myModal"
        data-status="F" data-cardtypeno="${v.code}"
        data-cardtypename="${v.name}"
        data-cardtypeurl="${v.imgurl}" data-id="${v.id}" data-sort="${v.sort}">删除</a></td></tr>`;
      });
      this.$tbody.html(tr);
    }, json => {
      console.log(json);
    });
  },
  deleteMemberCode() {
    deleteMemberCode({
      key_admin: $.cookie('ukey'),
      id: this.state.ctg.id,
    }).then(json => {
      console.log(json);
      location.reload();
    }, json => {
      console.log(json);
      alert(json.msg);
    });
  },
};
card.init();
