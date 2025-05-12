CREATE TABLE partner_types (
    id SERIAL PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE partners (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type_id INTEGER REFERENCES partner_types(id) ON DELETE SET NULL,
    rating INTEGER NOT NULL CHECK (rating >= 0),
    address TEXT,
    director_name VARCHAR(100),
    phone VARCHAR(20) CHECK (phone ~ '^\+?[0-9\s\-\(\)]+$'),
    email VARCHAR(100) CHECK (email ~* '^[A-Za-z0-9._%-]+@[A-Za-z0-9.-]+[.][A-Za-z]+$')
);

CREATE TABLE sales_history (
    id SERIAL PRIMARY KEY,
    partner_id INTEGER NOT NULL REFERENCES partners(id) ON DELETE CASCADE,
    sale_date DATE NOT NULL,
    amount DECIMAL(10, 2) NOT NULL CHECK (amount >= 0)
);