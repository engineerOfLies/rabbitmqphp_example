const mysql = require('mysql2');

// create a connection to the database
const connection = mysql.createConnection({
  host: 'localhost',
  user: 'user490',
  password: 'it490',
  database: 'userData'
});

// connect to the database
connection.connect(function(err) {
  if (err) {
    console.error('error connecting: ' + err.stack);
    return;
  }
  console.log('connected as id ' + connection.threadId);
});
