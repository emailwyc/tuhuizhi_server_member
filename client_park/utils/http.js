export default (userCfg) => new Promise((resolve, reject) => {
  const dt = window.dt;
  const dataType = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
  const time = +(new Date());

  const _cfg = Object.assign({}, {
    url: '',
    data: {},
    type: 'POST',
    dataType,
    xhrFields: {
      withCredentials: true,
    },
    crossDomain: true,
    isSuccessShow: false,
  }, userCfg);

  function showMsg(msg, isSuccess) {
    return new Promise((_resolve) => {
      if (!!isSuccess) {
        if (!!_cfg.isSuccessShow) {
          // console.log('操作成功');
          // alert('操作成功');
          _resolve();
        } else {
          _resolve();
        }
      } else {
        // console.error(`操作失败，信息：${msg || '无'}`);
        // alert(`操作失败，信息：${msg || '无'}`);
        alert(msg || '无');
        _resolve();
      }
    });
  }

  // handle event
  _cfg.beforeSend = function onBeforeSend(xhr) {
    if (typeof userCfg.beforeSend === 'function') {
      userCfg.beforeSend.call(this, xhr);
    }
  };

  _cfg.success = function onSuccess(res, state, xhr) {
    // global success
    console.log(res);
    if (res.code === 200) {
      // success
      dt('api', { url: _cfg.url, params: _cfg.data, res, time: (+(new Date()) - time) });
      showMsg(res.msg, true).then(() => {
        resolve(res, state, xhr);
      });
    } else {
      // fail
      showMsg(res.msg).then(() => {
        reject(res, state, xhr);
      });
    }
  };

  _cfg.error = function onError(xhr, state, error) {
    if (xhr.readyState === 0 && xhr.status === 0 && xhr.statusText === 'error') {
      console.log('');
    } else {
      showMsg(xhr.status);
    }
    if (typeof userCfg.error === 'function') {
      userCfg.error.call(this, xhr, state, error);
    }
  };
  const $ = window.$;
  $.ajax(_cfg);
});
