CREATE TABLE `question` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `question` TEXT NOT NULL,
    `user_id` int(11) NOT NULL,
    `video` char(19) not null,
    `created` datetime not null,
    `edited` datetime,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `video` (`video`),
    CONSTRAINT `question_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
);

create table `reply` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `answer` TEXT NOT NULL,
    `user_id` int(11) NOT NULL,
    `question_id` bigint(20) not null,
    `created` datetime not null,
    `edited` datetime,
    PRIMARY KEY (`id`),
    KEY `user_id` (`user_id`),
    KEY `question_id` (`question_id`),
    CONSTRAINT `answer_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
    CONSTRAINT `answer_question_id` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`)
);

create table `question_vote` (
    `id` bigint(20) not null AUTO_INCREMENT,
    `question_id` bigint(20) not null,
    `user_id` int(11) not null,
    `vote` tinyint not null,
    PRIMARY KEY (`id`),
    KEY `question_id` (`question_id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `question_vote_question_id` FOREIGN KEY (`question_id`) REFERENCES `question` (`id`),
    CONSTRAINT `question_vote_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
);

create table `reply_vote` (
    `id` bigint(20) not null AUTO_INCREMENT,
    `reply_id` bigint(20) not null,
    `user_id` int(11) not null,
    `vote` tinyint not null,
    PRIMARY KEY (`id`),
    KEY `reply_id` (`reply_id`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `reply_vote_reply_id` FOREIGN KEY (`reply_id`) REFERENCES `reply` (`id`),
    CONSTRAINT `reply_vote_user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
);

ALTER TABLE `question_vote` ADD UNIQUE `unique_question_user` (`question_id`, `user_id`);
ALTER TABLE `reply_vote` ADD UNIQUE `unique_reply_user` (`reply_id`, `user_id`);

ALTER TABLE `question` CHANGE `question` `text` TEXT NOT NULL;
ALTER TABLE `reply` CHANGE `answer` `text` TEXT NOT NULL;

alter table view change `start` `start` timestamp not null default current_timestamp;
UPDATE view set type = 0 where type = 'vid';
UPDATE view set type = 1 where type = 'pdf';
alter table view change `type` `pdf` tinyint;

ALTER TABLE view ADD too_long TINYINT(1) default 0 AFTER stop; 
UPDATE view as v set v.too_long = 1 where v.stop - v.start > 1800;
UPDATE view as v set v.too_long = 0 where v.too_long IS NULL;