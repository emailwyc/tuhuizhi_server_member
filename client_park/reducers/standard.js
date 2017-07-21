export const getInfoObj = (state = {
  obj: {},
}, action) => {
  switch (action.type) {
    case 'GETINFOREQUEST':
      return Object.assign({}, state, { obj: {} });
    case 'GETINFOSUCCESS':
      return Object.assign({}, state, { obj: action.data });
    case 'GETINFOERROR':
      return Object.assign({}, state, { code: 1 });
    default:
      return state;
  }
};
