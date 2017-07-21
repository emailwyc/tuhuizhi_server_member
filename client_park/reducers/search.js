const db = localStorage;
const conf = window.conf;
export const searchCar = (state = {
  list: [],
  code: '',
}, action) => {
  switch (action.type) {
    case 'SEARCHCARREQUEST':
      return Object.assign({}, state, { code: '', list: [] });
    case 'SEARCHCARSUCCESS':
      return Object.assign({}, state, { code: '', list: action.data });
    case 'SEARCHCARERROR':
      return Object.assign({}, state, { code: action.data.code, list: [] });
    default:
      return state;
  }
};

const mycars = db.getItem(`${conf.key}park_mycars`);
export const myCar = (state = {
  list: mycars ? JSON.parse(mycars) : [],
}, action) => {
  switch (action.type) {
    default:
      return state;
  }
};
