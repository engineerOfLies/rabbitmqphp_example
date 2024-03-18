// server.js
/***
const express = require('express');
const amqp = require('amqplib');
const path = require('path');
const app = express();
const PORT = 5500;

// Middleware to serve static files from 'public' directory
app.use(express.static(path.join(__dirname, 'public')));

app.use(express.json());
const amqpURI = 'amqp://guest:guest@192.168.191.133:5672';
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
*/
const express = require('express');
const { MongoClient } = require('mongodb');
const bcrypt = require('bcryptjs'); // For hashing passwords securely
const app = express();
const PORT = 5500;

// MongoDB connection URI and database name
const mongoURI = 'mongodb+srv://yf239:1qaz2wsx!QAZ%40WSX*mongo@cluster0.gzq1w7q.mongodb.net/';
const dbName = 'Intergrated_Visions';

// Connect to MongoDB
const client = new MongoClient(mongoURI, { useNewUrlParser: true, useUnifiedTopology: true });

async function connectDB() {
    try {
        await client.connect();
        console.log("Connected successfully to MongoDB server") 
        //console.log(client.db(It302))
        return client.db(dbName);
    } catch (err) {
        console.error(`Failed to connect to MongoDB: ${err}`);
        process.exit(1);
    }
}

const dbPromise = connectDB();

app.use(express.json());
app.use(express.static('public')); // Serve static files

// API endpoint for login
app.post('/api/login', async (req, res) => {
    const { username, password } = req.body;
    const db = await dbPromise;
    const user = await db.collection('register').findOne({ username });

    if (user && await bcrypt.compare(password, user.password)) {
        res.json({ success: true, message: 'Login successful.' });
    } else {
        res.status(401).json({ success: false, message: 'Invalid credentials.' });
    }
});

// API endpoint for registration
app.post('/api/register', async (req, res) => {
    const { firstName, lastName, username, password } = req.body;
    const db = await dbPromise;
    const existingUser = await db.collection('register').findOne({ username });

    if (existingUser) {
        return res.status(409).json({ success: false, message: 'User already exists.' });
    }

    const hashedPassword = await bcrypt.hash(password, 10); // Hash the password before storing
    await db.collection('login').insertOne({ firstName, lastName, username, password: hashedPassword });
    res.json({ success: true, message: 'Registration successful.' });
});

app.listen(PORT, () => console.log(`Server running on http://localhost:${PORT}`));
