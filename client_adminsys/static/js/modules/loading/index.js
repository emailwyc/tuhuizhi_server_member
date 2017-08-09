require('./loading_white.scss');
const loading = require('./loading_white.html');
const $ = window.$;

class Loading {
  constructor() {
    this._loading = loading;
  }
  show() {
    const loadingToast = $('#loadingToast')[0];
    if (!loadingToast) {
      $('body').append(loading);
    }
    const $loadingToast = $('#loadingToast');
    $loadingToast.show();
  }
  hide() {
    const $loadingToast = $('#loadingToast');
    $loadingToast.hide();
  }
}

module.exports = new Loading();
