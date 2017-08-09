const store = localStorage;
const qs = require('../modules/querystring').parse;
const $ = window.$;

module.exports = {
  isLogin() {
    return !!store.getItem('user');
  },
  auth(rtnUrl, wxId = 'wxb5e69065eb3d67ce') {
    const encodeUrl = encodeURIComponent(rtnUrl);
    location.href = 'http://weix.rtmap.com/redirect/oauth/' +
      `${wxId}/snsapi_userinfo?redirectURL=${encodeUrl}`;
  },
  login(rtnUrl) {
    if (this.isLogin()) {
      location.href = rtnUrl;
    } else {
      this.auth(rtnUrl);
      return;
    }
  },
  getUserInfo() {
    return JSON.parse(store.getItem('user'));
  },
  saveUser(cb = () => {}) {
    const userData = qs(location.search.slice(1));
    if (userData && userData.openid
      && userData.nickname && userData.headimgurl) {
      userData.nickname = decodeURIComponent(userData.nickname);
      store.setItem('user', JSON.stringify(userData));
      $.ajax({
        url: 'http://open.weixin.rtmap.com/lhc/Home/Index/saveuser',
        data: {
          openid: userData.openid,
          uname: userData.nickname,
          headimgurl: userData.headimgurl,
        },
        type: 'get',
        xhrFields: {
          withCredentials: true,
        },
        crossDomain: true,
        success(json) {
          cb(json);
        },
        error(json) {
          cb(json);
        },
      });
    } else {
      this.auth(`${location.protocol}//${location.hostname}${location.pathname}${location.search}`);
    }
  },
};
