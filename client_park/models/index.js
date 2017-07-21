const $ = window.$;
const dataType = location.href.indexOf('127.0.0.1') > 0 ? 'jsonp' : 'json';
const apiPath = location.href.indexOf('h5.rtmap.com') > 0 ?
  'http://groupon.rtmap.com/parking-web' : 'http://123.56.103.28/parking-web';

export const cancel = params => {
  alert(params.orderId);
  return new Promise((resolve, reject) => {
    const url = `${apiPath}/pay/cancel`;
    $.ajax({
      url,
      dataType,
      data: params,
      type: 'GET',
      contentType: 'application/json;charset=UTF-8',
      xhrFields: {
        withCredentials: true,
      },
      crossDomain: true,
      success: (res) => {
        if (res.code === 200) {
          resolve(res);
        } else {
          reject(res);
        }
      },
      error: (json) => reject(json),
    });
  });
};
