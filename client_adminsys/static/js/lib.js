require('../scss/bootstrap/bootstrap.scss');
window.onerror = function onError(errorMessage, scriptURI, lineNumber, columnNumber, errorObj) {
  const arr = {
    errorMessage,
    scriptURI,
    lineNumber,
    columnNumber,
    errorObj,
  };
  console.log(JSON.stringify(arr));
};
window.$ = require('./modules/jquery');
window._ = require('./modules/lodash');
