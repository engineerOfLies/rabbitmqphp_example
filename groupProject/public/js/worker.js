const amqp = require('amqplib');
const { MongoClient } = require('mongodb');

const amqpURI = 'amqp://guest:guest@192.168.191.133:5672';
const mongoURI = 'mongodb+srv://yf239:1qaz2wsx!QAZ%40WSX*mongo@cluster0.gzq1w7q.mongodb.net/';
const dbName = 'Intergraded_Visions';

async function startWorker() {
    const conn = await amqp.connect(amqpURI);
    const channel = await conn.createChannel();
    await channel.assertQueue('userRequests');

    const client = new MongoClient(mongoURI);
    await client.connect();
    console.log("Connected successfully to MongoDB server");
    const db = client.db(dbName);

    channel.consume('userRequests', async msg => {
        if (msg !== null) {
            const { action, data } = JSON.parse(msg.content.toString());

            if (action === 'register') {
                await handleRegister(db, data);
            }
            // Acknowledge the message as processed
            channel.ack(msg);
        }
    });
}

async function handleRegister(db, { firstName, lastName, username, password }) {
    const collection = db.collection('users');
    try {
        const result = await collection.insertOne({ firstName, lastName, username, password });
        console.log(`A document was inserted with the _id: ${result.insertedId}`);
    } catch (error) {
        console.error('Error inserting document:', error);
    }
}

startWorker().catch(err => console.error(err));
