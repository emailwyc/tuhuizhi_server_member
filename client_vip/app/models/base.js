var Schema = require('mongoose').Schema;
var base = new Schema({
  key: String,
  date: Date,
  service: Number,
  pv: Number,
  totalpv: Number,
  uv: Number,
  totaluv: Number,
}, { collection: 'base' });
module.exports = db.model('base', base);
