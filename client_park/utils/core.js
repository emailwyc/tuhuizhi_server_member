function makeArray(likeArr) {
  return Array.prototype.slice.call(likeArr);
}

export default {
  makeArray,
};
