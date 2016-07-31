DROP TABLE IF EXISTS comment;
CREATE TABLE comment (
    id                  varchar(32) NOT NULL,
    post_id             varchar(32) NOT NULL,
    parent_id           varchar(32) NOT NULL,
    author_id           varchar(32) NULL,
    body                TEXT NOT NULL,
    ip                  varchar(128),
    date_created        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_deleted        TIMESTAMP NULL,
    PRIMARY KEY (id)
) ENGINE=MyISAM;


DROP TABLE IF EXISTS user;
CREATE TABLE user (
    id                  varchar(32) NOT NULL,
    email               varchar(200) NOT NULL,
    username            varchar(200) NOT NULL,
    full_name           varchar(200) NULL,
    bio                 varchar(200) NULL,
    avatar              varchar(200) NULL,
    password            varchar(200) NOT NULL,
    salt                varchar(128) NOT NULL,
    session_token       varchar(64) NULL,
    date_created        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_deleted        TIMESTAMP NULL,
    PRIMARY KEY (id)
) ENGINE=MyISAM;

DROP TABLE IF EXISTS post;
CREATE TABLE post (
    id              varchar(32) NOT NULL,
    user_id         varchar(32) NOT NULL,
    title           varchar(255) NOT NULL,
    body            TEXT NOT NULL,
    ip              varchar(128),
    date_created    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_deleted    TIMESTAMP NULL,
    PRIMARY KEY (id)
) ENGINE=MyISAM;


-- -- DROP TABLE IF EXISTS topic;
-- -- CREATE TABLE topic (
-- --     id              TEXT PRIMARY KEY,
-- --     title           TEXT NOT NULL,
-- --     description     TEXT NULL,
-- --     date_created    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
-- -- );
-- --
-- -- INSERT INTO topic VALUES (1, 'Building Product', 'Give advise about the design and developemnt of products');
-- -- INSERT INTO topic VALUES (2, 'Founder Support', 'Give advise to other entereneurs who are going through low times');
-- -- INSERT INTO topic VALUES (3, 'Fundraising & Pitching', 'Give advise about raising captital and presenting your big idea');
-- -- INSERT INTO topic VALUES (4, 'Idea Validation', "Give advice on people's big ideas to help them make them better");
-- -- INSERT INTO topic VALUES (5, 'Marketing & Growth', 'Give advice on communicating, distributing, and growing companies and products');
-- -- INSERT INTO topic VALUES (6, 'Team Building', 'Give advice on growing, managing, and finding a team');
-- -- INSERT INTO topic VALUES (7, 'Cofi', 'Ask and reply to requests about our community of entrepreneurs');
-- -- --
