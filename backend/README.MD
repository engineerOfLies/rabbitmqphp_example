# Database MYSQL Setup

## Instructions

To replicate locally, please make sure you have the appropriate packages installed.

### Creating a Database and User

1. Open your terminal and open the mysql cli by typing `sudo mysql`.
2. Create a new database using `CREATE DATABASE db_name;` **Please refer to the database.ini file for the credentials**
3. Create a new user by typing `CREATE USER 'username'@'localhost' IDENTIFIED BY 'password';` **Please refer to the database.ini file for the credentials**
4. Grant privileges for the user you just created by typing `GRANT ALL PRIVILEGES ON database_name.* TO 'username'@'localhost';`

### Adding Tables
