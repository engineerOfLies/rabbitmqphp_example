const amqp = require('amqplib');
const pool = require('./dbConfig');

const amqpURI = 'amqp://localhost'; // Adjust as necessary

async function listenForMessages() {
  const connection = await amqp.connect(amqpURI);
  const channel = await connection.createChannel();
  const queue = 'userQueue';

  await channel.assertQueue(queue, { durable: false });
  console.log(`Waiting for messages in ${queue}. To exit press CTRL+C`);

  channel.consume(queue, async (msg) => {
    if (msg !== null) {
      console.log('Received:', msg.content.toString());
      const userData = JSON.parse(msg.content.toString());

      const queryText = 'INSERT INTO users(username, email) VALUES($1, $2) RETURNING *';
      const values = [userData.username, userData.email];
      console.log (queryText)

      try {
        const res = await pool.query(queryText, values);
        console.log('User saved to database:', res.rows[0]);
      } catch (err) {
        console.error('Error saving user to database:', err.stack);
      }

      channel.ack(msg);
    }
  });
}

listenForMessages();

