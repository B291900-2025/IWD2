CREATE DATABASE IF NOT EXISTS s2793337_website;
USE s2793337_website;

CREATE TABLE IF NOT EXISTS Runs (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    session_token  VARCHAR(64)  NOT NULL,
    protein_family VARCHAR(150) NOT NULL,
    taxon          VARCHAR(150) NOT NULL,
    num_sequences  INT          NOT NULL DEFAULT 0,
    status         VARCHAR(20)  NOT NULL DEFAULT 'pending',
    created_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS Sequences (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    run_id     INT UNSIGNED NOT NULL,
    accession  VARCHAR(30)  NOT NULL,
    species    VARCHAR(200) NOT NULL,
    sequence   MEDIUMTEXT   NOT NULL,
    seq_length INT          NOT NULL DEFAULT 0,
    FOREIGN KEY (run_id) REFERENCES Runs(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS Results (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    run_id      INT UNSIGNED NOT NULL,
    result_type VARCHAR(50)  NOT NULL,
    file_path   VARCHAR(300) NOT NULL,
    summary     TEXT,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (run_id) REFERENCES Runs(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ExampleDataset (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    accession  VARCHAR(30)  NOT NULL,
    species    VARCHAR(200) NOT NULL,
    sequence   MEDIUMTEXT   NOT NULL,
    seq_length INT          NOT NULL DEFAULT 0,
    protein    VARCHAR(150) NOT NULL DEFAULT 'glucose-6-phosphatase',
    taxon      VARCHAR(150) NOT NULL DEFAULT 'Aves'
);

SELECT 'Tables created successfully.' AS status;
