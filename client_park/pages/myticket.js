import React from 'react';
import { render } from 'react-dom';
import { createStore, combineReducers, applyMiddleware } from 'redux';
import { Provider } from 'react-redux';
import thunk from 'redux-thunk';

import './myticket.scss';
import Myticket from '../containers/myticket';
import { myTicketList } from '../reducers/myticket';


const createStoreWithMiddleware = applyMiddleware(thunk)(createStore);
const store = createStoreWithMiddleware(
combineReducers({
  myTicketList,
}),
{},
window.devToolsExtension && window.devToolsExtension());

const rootElement = document.getElementById('main');

render(
  <Provider store={store}>
    <Myticket />
  </Provider>,
  rootElement
);
