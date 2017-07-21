
const Qiniu = window.Qiniu;
let fileimg = '';
const $ = window.$;
export const upload = token => {
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
        fileimg = `https://img.rtmap.com/${file.target_name}`;
        $('.image').attr('src', fileimg);
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
};
