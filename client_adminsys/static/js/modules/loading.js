const $ = window.$;
module.exports = {
  show() {
    // console.log('show');
    $('#loadingToast').show();
  },
  hide() {
    // console.log('hide');
    $('#loadingToast').hide();
  },
};
