# Logging Producer
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

# Declare exchange and routing key
exchange_name = 'logging_exchange'
routing_key = 'logging'

# Declare queue and bind to exchange
channel.queue_declare(queue='logging_queue', durable=True)
channel.queue_bind(queue='logging_queue', exchange=exchange_name, routing_key=routing_key)

# Publish log message
channel.basic_publish(exchange=exchange_name, routing_key=routing_key, body='Hello, world! There are no errors to report')
logging.info('Sent log message')

# Close connection
connection.close()


