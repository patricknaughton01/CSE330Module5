# Database Schema
Database name: calendar
## Table: users
| id | username | hash |
|----|----------|------|
|mediumint unsigned primary key auto_increment not null | varchar(200) unique not null | varchar(255) not null |

## Table: events
| id | user_id | title | start_time | end_time | description | location | 
|----|---------|-------|------------|----------|-------------|----------|
|mediumint unsigned primary key auto_increment not null | mediumint unsigned foreign key not null | varchar(100) not null | datetime not null | datetime not null | text not null | varchar(100) |
