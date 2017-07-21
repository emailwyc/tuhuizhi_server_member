import React from 'react';
import { render } from 'react-dom';

import './buyterms.scss';
import Buyterms from '../containers/buyterms';

const rootElement = document.getElementById('main');

render(
    <Buyterms />,
  rootElement
);
