export const getParkObj = (state = {
  list: [],
  logo: '',
}, action) => {
  switch (action.type) {
    case 'GETPARKREQUEST':
      return Object.assign({}, state, { list: [], logo: '' });
    case 'GETPARKSUCCESS':
      return Object.assign({}, state, { list: action.data.data, logo: action.data.logo });
    case 'GETPARKERROR':
      return Object.assign({}, state, { code: 1, logo: '' });
    default:
      return state;
  }
};
