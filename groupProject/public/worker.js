// worker.js

const amqp = require('amqplib');
const { Pool } = require('pg');

const amqpURI = 'amqp://guest:guest@192.168.191.133:5672';
const pool = new Pool({
  // PostgreSQL connection settings
  user: 'dbuser',
  host: 'database.server.ip',
  database: 'mydb',
  password: 'dbpassword',
  port: 5432,
});

async function startWorker() {
  const conn = await amqp.connect(amqpURI);
  const channel = await conn.createChannel();

  await channel.assertQueue('userRequests');

  channel.consume('userRequests', async msg => {
    if (msg !== null) {
      const content = JSON.parse(msg.content.toString());
      
      // Perform the database operation based on the message type
      if (content.type === 'login') {
        // ... login logic, checking database for user ...
      } else if (content.type === 'register') {
        // ... register logic, inserting user into database ...
      }

      // Acknowledge the message
      channel.ack(msg);
    }
  });
}

startWorker().catch(err => console.error(err));

