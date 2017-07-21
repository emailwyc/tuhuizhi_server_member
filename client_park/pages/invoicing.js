import React from 'react';
import { render } from 'react-dom';
// import { createStore, combineReducers, applyMiddleware } from 'redux';
// import { Provider } from 'react-redux';
// import thunk from 'redux-thunk';

import './invoicing.scss';
import Invoicing from '../containers/invoicing';
// invoicedetail
// import { myTicketInfo } from '../reducers/shareticket';


// const createStoreWithMiddleware = applyMiddleware(thunk)(createStore);
// const store = createStoreWithMiddleware(
// combineReducers({
//   myTicketInfo,
// }),
// {},
// window.devToolsExtension && window.devToolsExtension());

const rootElement = document.getElementById('main');

render(
  // <Provider store={store}>
    <Invoicing />,
  // </Provider>,
  rootElement
);
