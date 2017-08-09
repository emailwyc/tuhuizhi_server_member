const $ = window.$;
require('../modules/cookie')($);
export const cookieTime = ukey => {
  const date = new Date();
  const expires = new Date(date.getTime() + 25 * 60 * 1000);
  $.cookie('ukey', ukey, { expires, path: '/' });
  // console.log($.cookie('ukey'));
  // location.href = '/dashboard';
};
