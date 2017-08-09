const $ = window.$;
module.exports = {
  init($form) {
    // console.log('validate init');
    this.initDom($form);
    this.initEvent();
  },
  initDom($form) {
    this.$form = $form;
    this.$inputs = this.$form.find('input');
    this.$inputs.each((i, ele) => {
      // console.log(ele);
      this.setValidity($(ele));
    });
  },
  initEvent() {
    this.$inputs.on('input', (e) => {
      // console.log(e.target);
      this.setValidity($(e.target));
    });
  },
  setValidity($el) {
    const i = $el[0];
    const v = i.validity;
    if (!!v.valueMissing) {
      i.setCustomValidity($el.data('required'));
    } else {
      if (v.patternMismatch) {
        i.setCustomValidity($el.data('mismatch'));
      } else {
        i.setCustomValidity('');
      }
    }
  },
};
