use it490db;

CREATE TABLE IF NOT EXISTS users (
    id int not null AUTO_INCREMENT,
    email varchar(255) UNIQUE NOT NULL,
    username varchar(255) UNIQUE NOT NULL,
    user_pass varchar(255) NOT NULL,
    PRIMARY KEY(id)
);

INSERT INTO users(username, user_pass)
VALUES ("it490","root");

/* Basic setup
 * 
 * 1. Open your terminal and enter into mysql (`sudo mysql`)
 * 2. Open another terminal and navigate to this project (e.g `cd it490-spring2023`)
 * 3. Navigate to /backend (`cd backend`)
 * 4. type `pwd` copy and add init.sql at the end (e.g. "/home/it490/Desktop/it490-spring2023/backend/migrations/init.sql")
 * 5. If you're using vscode or other IDE, right-click 'init.sql' and select 'Copy Path'
 * 6. In your mysql terminal type `source path-to-init.sql'
 * 7. VIOLAAAA
 *
*/