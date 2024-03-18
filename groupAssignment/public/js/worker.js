// worker.js
const amqp = require('amqplib');
const { MongoClient } = require('mongodb');
const bcrypt = require('bcryptjs');

const mongoURI = 'mongodb://localhost:27017/mydatabase';

async function connectToDB() {
    const client = new MongoClient(mongoURI, { useNewUrlParser: true, useUnifiedTopology: true });
    await client.connect();
    return client.db();
}

async function startWorker() {
    const db = await connectToDB();
    const connection = await amqp.connect('amqp://localhost'); // Update your RabbitMQ connection string
    const channel = await connection.createChannel();
    await channel.assertQueue('userQueue');

    channel.consume('userQueue', async (msg) => {
        if (msg) {
            const { action, username, password, ...rest } = JSON.parse(msg.content.toString());
            
            if (action === 'login') {
                // Implement login logic, check username and password against the database
            } else if (action === 'register') {
                const hashedPassword = await bcrypt.hash(password, 10);
                await db.collection('users').insertOne({ username, password: hashedPassword, ...rest });
            }

            channel.ack(msg);
        }
    });
}

startWorker().catch(console.error);
