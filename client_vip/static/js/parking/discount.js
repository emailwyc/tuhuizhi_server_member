require('../../scss/parking/discount.scss');
import { getUploadToken, memberRights } from '../model';
console.log(getUploadToken, memberRights);
const $ = window.$;
require('../modules/cookie')($);
import { designParkIntro, getParkIntro } from '../model/order';
getUploadToken().then(token => {
  const UM = window.UM;
  const um = UM.getEditor('myEditor', {
    // initialFrameWidth: 800,
    initialFrameHeight: 650,
    imagePopup: false,
    scaleEnabled: false,
    minFrameWidth: 400,
    autoHeightEnabled: false,
  });
  window.QINIU_TOKEN = token.data;
  window.QINIU_BUCKET_DOMAIN = 'img.rtmap.com';
  const $biaozhun = $('.shoufeibiaozhun');
  getParkIntro(`key_admin=${$.cookie('ukey')}`).then(res => {
    if (res.code === 200) {
      $biaozhun.html(res.data.function_name);
      const umtext = $biaozhun.text();
      um.setContent(umtext);
      console.log(res);
    } else {
      console.log();
    }
  }
);
  $('.obtain').click(
    () => {
      const info = {
        key_admin: $.cookie('ukey'),
        content: um.getContent(),
      };
      console.log(um.getContent());
      designParkIntro(info).then(res => {
        if (res.code === 200) {
          $('.prompt').show();
        } else {
          console.log();
        }
      }
    );
    }
    );
});
