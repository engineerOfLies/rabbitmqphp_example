// models/User.js
const mongoose = require('mongoose');

const userSchema = new mongoose.Schema({
  username: String,
  password: String, // NOTE: In production, use hashed passwords instead
  Confirmation_password: String, // NOTE: In production, use hashed passwords instead
});

module.exports = mongoose.model('User', userSchema);
