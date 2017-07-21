import React from 'react';
import { render } from 'react-dom';
import { createStore, combineReducers, applyMiddleware } from 'redux';
import { Provider } from 'react-redux';
import thunk from 'redux-thunk';
import './index.scss';
import Index from '../containers/index';
import { getParkObj } from '../reducers';


const createStoreWithMiddleware = applyMiddleware(thunk)(createStore);
const store = createStoreWithMiddleware(
combineReducers({
  getParkObj,
}),
{},
window.devToolsExtension && window.devToolsExtension());

const rootElement = document.getElementById('main');

render(
  <Provider store={store}>
    <Index />
  </Provider>,
  rootElement
);
