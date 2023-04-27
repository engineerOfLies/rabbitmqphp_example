# Logging Consumer
import logging
import pika
import logging.config

logging.config.fileConfig('/var/www/sample/logging.conf')

# Set up logging configuration
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')

# Set up RabbitMQ connection and channel
creds = pika.PlainCredentials('test', 'test')
parameters = pika.ConnectionParameters(host='localhost', virtual_host='testauth', credentials=creds)
connection = pika.BlockingConnection(parameters)
channel = connection.channel()

# Declare exchange and queue
exchange_name = 'logging_exchange'
queue_name = 'logging_queue'

# Declare queue and bind to exchange
channel.queue_declare(queue=queue_name, durable=True)
channel.queue_bind(queue=queue_name, exchange=exchange_name, routing_key='logging')

# Define callback function to handle log messages
def callback(ch, method, properties, body):
    logging.info('Received log message: %s', body.decode())

# Start consuming messages from queue
channel.basic_consume(queue=queue_name, on_message_callback=callback, auto_ack=True)
logging.info('Waiting for log messages...')
channel.start_consuming()
