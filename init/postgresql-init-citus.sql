CREATE
EXTENSION IF NOT EXISTS citus;

-- Create distributed table
SELECT master_create_distributed_table('books', 'id');