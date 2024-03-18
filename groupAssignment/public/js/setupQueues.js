const { setupQueue } = require('./rabbitmqService');

setupQueue().then(() => {
  console.log('Queue setup completed');
  process.exit(0);
}).catch(error => {
  console.error('Failed to setup the queue:', error);
  process.exit(1);
});
