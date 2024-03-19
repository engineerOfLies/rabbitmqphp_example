const express = require('express');
const amqp = require('amqplib/callback_api');
const mongoose = require('mongoose');
const path = require('path');
const bodyParser = require('body-parser');
const app = express();
const PORT = process.env.PORT || 3000;
require('dotenv').config();
// Serve static files from the public folder
app.use(express.static(path.join(__dirname, 'public')));
app.use(bodyParser.json());
// MongoDB connection URL
const MONGODB_URI = process.env.MONGODB_URI;
// Connect to MongoDB
mongoose.connect(MONGODB_URI)
    .then(() => {
        console.log('Connected to MongoDB');
    })
    .catch((error) => {
        console.error('Error connecting to MongoDB:', error);
    });
// Define a Mongoose schema for form data
const userDataSchema = new mongoose.Schema({
    firstName: String,
    lastName: String,
    username: String,
    password: String
    // Add other fields as needed
});
const UserData = mongoose.model('UserData', userDataSchema);
// RabbitMQ connection URL
const rabbitUrl = 'amqp://test:test@192.168.191.133:5672';
// Connect to RabbitMQ
amqp.connect(rabbitUrl, function(error0, connection) {
    if (error0) {
        throw error0;
    }
    // Create a channel
    connection.createChannel(function(error1, channel) {
        if (error1) {
            throw error1;
        }
        // Declare a queue
        const queue = 'hello';
        channel.assertQueue(queue, {
            durable: false
        });
        // Set up API endpoint to send message to RabbitMQ
        app.post('/send', (req, res) => {
            const message = 'Hello RabbitMQ!';
            channel.sendToQueue(queue, Buffer.from(message));
            res.send('Message sent to RabbitMQ');
        });
    });
});
// Handle POST requests to the root URL ("/")
app.post('/', (req, res) => {
// Assuming the form sends data in the request body
    const formData = req.body;
// Create a new UserData document with the form data
    const userData = new UserData({
        firstName: formData.firstName,
        lastName: formData.lastName,
        username: formData.username,
        password: formData.password
    // Assign other fields as needed
    });
    // Save the UserData document to MongoDB
    userData.save()
        .then(() => {
            console.log('Data saved to MongoDB');
            res.send('Form submitted successfully');
        })
        .catch((error) => {
            console.error('Error saving data to MongoDB:', error);
            res.status(500).send('Error submitting form');
        });
});
// Start server
app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});
