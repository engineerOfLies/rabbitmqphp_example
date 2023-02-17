use it490db;

CREATE TABLE IF NOT EXISTS users (
    id int not null AUTO_INCREMENT,
    email varchar(255),
    username varchar(255) UNIQUE NOT NULL,
    user_pass varchar(255) NOT NULL,
    is_private BOOLEAN default false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY(id)
);

INSERT INTO users(username, user_pass, email)
VALUES ("it490","root", "it490@email.com");

CREATE TABLE IF NOT EXISTS bookmarks (
    bookmark_id int not null AUTO_INCREMENT,
    user_id int, 
    movie_id varchar(255),
    title varchar(255),
    poster varchar(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(user_id) REFERENCES users(id),
    PRIMARY KEY(bookmark_id)
)
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