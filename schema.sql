CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    email TEXT NOT NULL,
    password TEXT,
    name TEXT NOT NULL,
    CONSTRAINT users_unique_email UNIQUE (email)
);

CREATE TABLE access_tokens (
    user_id BIGINT NOT NULL,
    access_token TEXT NOT NULL,
    CONSTRAINT access_tokens_pkey PRIMARY KEY (user_id, access_token),
    CONSTRAINT access_tokens_users_fkey FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE
);
