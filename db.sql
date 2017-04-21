/**
 * The SQL script for creating necessary database and tables.
 * 
 * @author  Khoa Le
 */


create database if not exists php_db;

use php_db;

create table if not exists customer (
    id int not null auto_increment,
    name varchar(256) not null,
    password varchar(256) not null,
    email varchar(125) not null,
    phone varchar(32),
    primary key (id),
    unique key ix_email (email)
);

create table if not exists request (
    id int not null auto_increment,
    customer_id int not null,
    request_date date not null,
    description varchar(256),
    weight decimal(12,2),
    pickup_address varchar(256),
    pickup_suburb varchar(64),
    pickup_time datetime,
    receiver_name varchar(128),
    receiver_address varchar(256),
    receiver_suburb varchar(64),
    receiver_state varchar(8),
    primary key (id),
    foreign key (customer_id) references customer(id)
);
