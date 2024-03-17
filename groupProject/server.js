// server.js

const express = require('express');
const amqp = require('amqplib');
const path = require('path');
const app = express();
const PORT = 3000;

// Middleware to serve static files from 'public' directory
app.use(express.static(path.join(__dirname, 'public')));

app.use(express.json());

const amqpURI = 'amqp://guest:guest@XXX.XXX.XXX.XXX:5672';
let amqpChannel = null;

amqp.connect(amqpURI)
  .then(connection => connection.createChannel())
  .then(channel => {
    amqpChannel = channel;
    return amqpChannel.assertQueue('userRequests');
  })
  .catch(err => console.error('Error connecting to RabbitMQ:', err));

// API endpoint for login
app.post('/api/login', (req, res) => {
  const { username, password } = req.body;

  if (amqpChannel) {
    amqpChannel.sendToQueue('userRequests', Buffer.from(JSON.stringify({
      type: 'login',
      username,
      password
    })));
    res.json({ success: true });
  } else {
    res.status(500).json({ success: false, message: 'Error connecting to RabbitMQ' });
  }
});

// API endpoint for registration
app.post('/api/register', (req, res) => {
  const { firstName, lastName, username, password } = req.body;

  if (amqpChannel) {
    amqpChannel.sendToQueue('userRequests', Buffer.from(JSON.stringify({
      type: 'register',
      firstName,
      lastName,
      username,
      password
    })));
    res.json({ success: true });
  } else {
    res.status(500).json({ success: false, message: 'Error connecting to RabbitMQ' });
  }
});

// Start the server
app.listen(PORT, () => console.log(`Server running on http://localhost:${PORT}`));
