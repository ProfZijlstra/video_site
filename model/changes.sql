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

ALTER TABLE view ADD speed FLOAT default 1.0 after stop;

ALTER TABLE user ADD studentID INT UNSIGNED AFTER email;
ALTER TABLE user ADD knownAs VARCHAR(45) AFTER lastname;
ALTER TABLE user ADD teamsName VARCHAR(45) AFTER studentID;
ALTER TABLE user ADD hasPicture TINYINT(1) NOT NULL DEFAULT 0;
CREATE INDEX studentID ON user(studentID);
CREATE INDEX teamsName ON user(teamsName);

-- -----------------------------------------------------
-- Table `cs472`.`meeting`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `cs472`.`meeting` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `day_id` INT NOT NULL,
  `title` VARCHAR(45) NOT NULL,
  `date` DATE NOT NULL,
  `start` TIME NOT NULL,
  `stop` TIME NOT NULL,
  `sessionWeight` FLOAT NOT NULL DEFAULT 0.5,
  PRIMARY KEY (`id`),
  INDEX `fk_meeting_day1_idx` (`day_id` ASC) VISIBLE,
  CONSTRAINT `fk_meeting_day1`
    FOREIGN KEY (`day_id`)
    REFERENCES `cs472`.`day` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `cs472`.`attendance_data`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `cs472`.`attendance_data` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `meeting_id` BIGINT UNSIGNED NOT NULL,
  `teamsName` VARCHAR(45) NOT NULL,
  `start` TIME NOT NULL,
  `stop` TIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_attendance_meeting1_idx` (`meeting_id` ASC) VISIBLE,
  CONSTRAINT `fk_attendance_meeting1`
    FOREIGN KEY (`meeting_id`)
    REFERENCES `cs472`.`meeting` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `cs472`.`attendance`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `cs472`.`attendance` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `meeting_id` BIGINT UNSIGNED NOT NULL,
  `teamsName` VARCHAR(45) NOT NULL,
  `notEnrolled` TINYINT(1) NOT NULL DEFAULT 0,
  `absent` TINYINT(1) NOT NULL DEFAULT 0,
  `arriveLate` TINYINT(1) NOT NULL DEFAULT 0,
  `leaveEarly` TINYINT(1) NOT NULL DEFAULT 0,
  `middleMissing` TINYINT(1) NOT NULL DEFAULT 0,
  `inClass` TINYINT(1) NOT NULL DEFAULT 0,
  INDEX `fk_attendance_report_meeting1_idx` (`meeting_id` ASC) VISIBLE,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_attendance_report_meeting1`
    FOREIGN KEY (`meeting_id`)
    REFERENCES `cs472`.`meeting` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

update user set teamsName = CONCAT(TRIM(firstname), " ", TRIM(lastname));

ALTER TABLE attendance ADD COLUMN excused tinyint(1) NOT NULL DEFAULT 0;

CREATE TABLE `cs472`.`session` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `day_id` INT(11) NOT NULL,
  `type` CHAR(2) NOT NULL DEFAULT 'AM',
  `exported` TINYINT(8) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_session_day_id` FOREIGN KEY (`day_id`) REFERENCES `cs472`.`day`(`id`)
) ENGINE = InnoDB;

ALTER TABLE meeting ADD COLUMN session_id INT(10) UNSIGNED;
ALTER TABLE meeting ADD FOREIGN KEY (session_id) REFERENCES `session`(id);
ALTER TABLE meeting DROP sessionWeight;

ALTER TABLE attendance_data MODIFY teamsName varchar(90);
ALTER TABLE attendance MODIFY teamsName varchar(90);

ALTER TABLE meeting DROP FOREIGN KEY fk_meeting_day1;
ALTER TABLE meeting DROP day_id;

ALTER TABLE view DROP too_long;

-- TODO
ALTER TABLE `session` DROP exported;
ALTER TABLE `session` ADD COLUMN `status` VARCHAR(45);
ALTER TABLE `session` ADD COLUMN `start` TIME;
ALTER TABLE `session` ADD COLUMN `stop` TIME;
ALTER TABLE `session` ADD COLUMN `generated` tinyint UNSIGNED;

CREATE TABLE `attendance_export` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `studentID` int(10) unsigned NOT NULL,
  `status` VARCHAR(45) NOT NULL,
  `inClass` TINYINT(1) NOT NULL,
  `comment` VARCHAR(255) NOT NULL,
  `session_id` INT(10) unsigned NOT NULL,
  PRIMARY KEY(`id`),
  KEY `fk_session_id` (`session_id`),
  CONSTRAINT `fk_session_id` FOREIGN KEY(`session_id`) REFERENCES `session`(`id`) 
);

ALTER TABLE `attendance` ADD COLUMN `start` TIME;
ALTER TABLE `attendance` ADD COLUMN `stop` TIME;

UPDATE `attendance` AS a SET `start` = 
  (SELECT MIN(d.start) 
  FROM attendance_data AS d 
  WHERE a.meeting_id = d.meeting_id 
  AND a.teamsName = d.teamsName
  GROUP BY d.teamsName);

UPDATE `attendance` AS a SET `stop` = 
  (SELECT MAX(d.stop) 
  FROM attendance_data AS d 
  WHERE a.meeting_id = d.meeting_id 
  AND a.teamsName = d.teamsName
  GROUP BY d.teamsName);

ALTER TABLE attendance_data RENAME attendance_import;
------------- 29th of May 2022
ALTER TABLE user ADD COLUMN `badge` BIGINT;
------------- 3rd of June 2022
ALTER TABLE offering ADD COLUMN `fac_user_id` INT;
CREATE INDEX fac_user_id ON offering(fac_user_id);
CREATE INDEX `type` ON user(`type`);
UPDATE offering SET fac_user_id = 5;

------------- 13th of June 2022
ALTER TABLE offering ADD COLUMN `daysPerLesson` TINYINT UNSIGNED NOT NULL;
ALTER TABLE offering ADD COLUMN `lessonsPerPart` TINYINT UNSIGNED NOT NULL;
ALTER TABLE offering ADD COLUMN `lessonParts` TINYINT UNSIGNED NOT NULL;
ALTER TABLE offering DROP COLUMN `stop`;
UPDATE offering set daysPerLesson = 1, lessonsPerPart = 7, lessonParts = 4;

------------- 3rd of July 2022
UPDATE user set type='student' WHERE type='user';