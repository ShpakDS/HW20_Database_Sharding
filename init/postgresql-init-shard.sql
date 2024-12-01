-- Create shard table
CREATE TABLE books
(
    id             SERIAL PRIMARY KEY,
    title          VARCHAR(255),
    author         VARCHAR(255),
    price          NUMERIC,
    published_date DATE
);

-- Enable FDW
CREATE
EXTENSION IF NOT EXISTS postgres_fdw;

-- Foreign server for shard1
CREATE SERVER shard1 FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS (host 'postgresql_b1', dbname 'shard1', port '5432');

-- Foreign server for shard2
CREATE SERVER shard2 FOREIGN DATA WRAPPER postgres_fdw
    OPTIONS (host 'postgresql_b2', dbname 'shard2', port '5432');

-- User mapping
CREATE USER MAPPING FOR user SERVER shard1 OPTIONS (user 'user', password 'password');
CREATE USER MAPPING FOR user SERVER shard2 OPTIONS (user 'user', password 'password');

-- Foreign tables
CREATE
FOREIGN TABLE books_shard1 (
    id SERIAL,
    title VARCHAR(255),
    author VARCHAR(255),
    price NUMERIC,
    published_date DATE
) SERVER shard1 OPTIONS (table_name 'books');

CREATE
FOREIGN TABLE books_shard2 (
    id SERIAL,
    title VARCHAR(255),
    author VARCHAR(255),
    price NUMERIC,
    published_date DATE
) SERVER shard2 OPTIONS (table_name 'books');