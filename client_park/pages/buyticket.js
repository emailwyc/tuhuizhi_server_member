import React from 'react';
import { render } from 'react-dom';
import { createStore, combineReducers, applyMiddleware } from 'redux';
import { Provider } from 'react-redux';
import thunk from 'redux-thunk';

import './buyticket.scss';
import Buyticket from '../containers/buyticket';
import { buyTicketInfo, orderInfo } from '../reducers/buyticket';


const createStoreWithMiddleware = applyMiddleware(thunk)(createStore);
const store = createStoreWithMiddleware(
combineReducers({
  buyTicketInfo,
  orderInfo,
}),
{},
window.devToolsExtension && window.devToolsExtension());

const rootElement = document.getElementById('main');

render(
  <Provider store={store}>
    <Buyticket />
  </Provider>,
  rootElement
);
