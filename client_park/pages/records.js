import React from 'react';
import { render } from 'react-dom';
import { createStore, combineReducers, applyMiddleware } from 'redux';
import { Provider } from 'react-redux';
import thunk from 'redux-thunk';
import './records.scss';
import Records from '../containers/records';
import { getRecordsObj } from '../reducers/records';


const createStoreWithMiddleware = applyMiddleware(thunk)(createStore);
const store = createStoreWithMiddleware(
combineReducers({
  getRecordsObj,
}),
{},
window.devToolsExtension && window.devToolsExtension());

const rootElement = document.getElementById('main');

render(
  <Provider store={store}>
    <Records />
  </Provider>,
  rootElement
);
