# Database MYSQL Setup

## Instructions

To replicate locally, please make sure you have the appropriate packages installed.

### Creating a Database and User

1. Open your terminal and open the mysql cli by typing `sudo mysql`.
2. Create a new database using `CREATE DATABASE db_name;` **Please refer to the database.ini file for the credentials**
3. Create a new user by typing `CREATE USER 'username'@'localhost' IDENTIFIED BY 'password';` **Please refer to the database.ini file for the credentials**
4. Grant privileges for the user you just created by typing `GRANT ALL PRIVILEGES ON database_name.* TO 'username'@'localhost';`

### Adding Tables
 
 1. Open your terminal and enter into mysql (`sudo mysql`)
 2. Open another terminal and navigate to this project (e.g `cd it490-spring2023`)
 3. Navigate to /backend (`cd backend`)
 4. type `pwd` copy and add init.sql at the end (e.g. "/home/it490/Desktop/it490-spring2023/backend/migrations/init.sql")
 5. If you're using vscode or other IDE, right-click 'init.sql' and select 'Copy Path'
 6. In your mysql terminal type `source PATH-TO-INIT.SQL'
 7. VIOLAAAA
