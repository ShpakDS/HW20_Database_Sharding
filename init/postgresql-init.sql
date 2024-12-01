CREATE TABLE books
(
    id             SERIAL PRIMARY KEY,
    title          VARCHAR(255),
    author         VARCHAR(255),
    price          NUMERIC,
    published_date DATE
);