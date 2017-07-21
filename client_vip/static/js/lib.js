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
window.$ = require('jquery');
const navBar = require('./modules/navbar').navBar;
// window._ = require('lodash');
// import { navBar } from './modules/navbar';
navBar.init();
