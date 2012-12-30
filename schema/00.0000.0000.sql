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

CREATE TABLE pvt_results (
    user_id BIGINT NOT NULL,
    timestamp BIGINT NOT NULL,
    average_response_time NUMERIC NOT NULL,
    error_count SMALLINT NOT NULL DEFAULT (0),
    CONSTRAINT pvt_results_pkey PRIMARY KEY (user_id, timestamp),
    CONSTRAINT pvt_results_users_fkey FOREIGN KEY (user_id) REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE pvt_results_response_times (
    user_id BIGINT NOT NULL,
    timestamp BIGINT NOT NULL,
    sequence SMALLINT NOT NULL,
    response_time NUMERIC,
    CONSTRAINT pvt_results_response_times_pkey PRIMARY KEY (user_id, timestamp, sequence),
    CONSTRAINT pvt_results_response_times_pvt_results_fkey FOREIGN KEY (user_id, timestamp) REFERENCES pvt_results (user_id, timestamp) ON UPDATE CASCADE ON DELETE CASCADE
);
