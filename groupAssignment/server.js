require('dotenv').config();
const express = require('express');
const bcrypt = require('bcryptjs');
const mongoose = require('mongoose');
const { publishToQueue } = require('./public/js/rabbitmqService');
const amqp = require('amqplib');

const User = require('./public/models/User'); // Ensure this path is correct
const queueName = 'userRegistrationQueue';
const app = express();
app.use(express.json());
app.use(express.static('public'));

// MongoDB connection (update the URI according to your setup)
mongoose.connect(process.env.MONGODB_URI)
  .then(() => console.log('MongoDB connected'))
  .catch(err => console.error(err));
app.post('/api/register', async (req, res) => {
    try {
        const { username, password } = req.body;

        // Check if the user already exists
        const userExists = await User.findOne({ username });
        if (userExists) {
            return res.status(400).json({ message: 'User already exists.' });
        }

        const hashedPassword = await bcrypt.hash(password, 10);
        const userData = { username, password: hashedPassword };

        // Publish user data to RabbitMQ queue
        await publishToQueue(queueName, userData);
        res.json({ message: 'Registration request received. Processing...' });
    } catch (error) {
        console.error(error);
        res.status(500).send('Server error');
    }
});

async function startConsumer() {
    const conn = await amqp.connect(process.env.RABBITMQ_URL);
    const channel = await conn.createChannel();
    await channel.assertQueue(queueName, { durable: true });

    channel.consume(queueName, async (msg) => {
        if (msg !== null) {
            const { username, password } = JSON.parse(msg.content.toString());

            // Process registration (since user existence is checked before queueing, this is for redundancy)
            const newUser = new User({ username, password });
            await newUser.save();
            console.log(`User ${username} registered successfully`);

            channel.ack(msg);
        }
    }, { noAck: false });

    console.log('Consumer started');
}

app.listen(5000, () => {
    console.log('Server started on port 3000');
    startConsumer();
});
