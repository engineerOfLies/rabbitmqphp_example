// rabbitmqService.js
const amqp = require('amqplib');
const rabbitmqURL = process.env.RABBITMQ_URL; // Make sure you set this in your .env

async function publishToQueue(queue, data) {
    const conn = await amqp.connect(rabbitmqURL);
    const channel = await conn.createChannel();
    await channel.assertQueue(queue, { durable: true });
    channel.sendToQueue(queue, Buffer.from(JSON.stringify(data)));
    console.log(`Data sent to queue ${queue}`);

    setTimeout(() => {
        channel.close();
        conn.close();
    }, 500); // Close connection after a short delay
}

module.exports = { publishToQueue };
