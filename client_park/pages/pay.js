import React from 'react';
import { render } from 'react-dom';
import { createStore, combineReducers, applyMiddleware } from 'redux';
import { Provider } from 'react-redux';
import thunk from 'redux-thunk';
import './pay.scss';
import Pay from '../containers/pay';
import { park, order, pay, pointOrder, cansel } from '../reducers/pay';


const createStoreWithMiddleware = applyMiddleware(thunk)(createStore);
const store = createStoreWithMiddleware(
combineReducers({
  park,
  order,
  pay,
  pointOrder,
  cansel,
}),
{},
window.devToolsExtension && window.devToolsExtension());

const rootElement = document.getElementById('main');

render(
  <Provider store={store}>
    <Pay />
  </Provider>,
  rootElement
);
