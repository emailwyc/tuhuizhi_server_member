const $ = window.$;
module.exports = function wxconf(id, cb) {
  const url = 'http://open.weixin.rtmap.com/Wechat/Wechatjs/getsignature';
  $.ajax({
    url,
    type: 'get',
    data: {
      build: id,
      url: encodeURIComponent(location.href),
    },
    xhrFields: {
      withCredentials: true,
    },
    dataType: 'jsonp',
    crossDomain: true,
    success: (json) => {
      // alert(JSON.stringify(json));
      cb(json);
    },
    error: () => {},
  });
};
