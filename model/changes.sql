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

-- DONE
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

------------- 29th of July 2022
ALTER TABLE question RENAME comment;
ALTER TABLE question_vote RENAME comment_vote;
ALTER TABLE comment_vote CHANGE question_id comment_id BIGINT;
ALTER TABLE reply CHANGE question_id comment_id BIGINT;

------------- 30th of July 2022
CREATE TABLE IF NOT EXISTS `manalabs`.`quiz` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `day_id` INT NOT NULL,
  `start` TIMESTAMP NOT NULL,
  `stop` TIMESTAMP NOT NULL,
  `visible` BOOLEAN NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_quiz_day1_idx` (`day_id` ASC) VISIBLE,
  CONSTRAINT `fk_quiz_day1`
    FOREIGN KEY (`day_id`)
    REFERENCES `manalabs`.`day` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `manalabs`.`question` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `quiz_id` INT UNSIGNED NOT NULL,
  `text` TEXT NOT NULL,
  `modelAnswer` TEXT NULL,
  `points` INT UNSIGNED NOT NULL,
  `seq` INT UNSIGNED NOT NULL,
  `type` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_question_quiz1_idx` (`quiz_id` ASC) VISIBLE,
  CONSTRAINT `fk_question_quiz1`
    FOREIGN KEY (`quiz_id`)
    REFERENCES `manalabs`.`quiz` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `manalabs`.`answer` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `text` TEXT NOT NULL,
  `question_id` BIGINT UNSIGNED NOT NULL,
  `user_id` INT NOT NULL,
  `created` TIMESTAMP NOT NULL,
  `updated` TIMESTAMP NULL,
  `points` FLOAT NULL,
  `comment` TEXT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_answer_question2_idx` (`question_id` ASC) VISIBLE,
  INDEX `fk_answer_user2_idx` (`user_id` ASC) VISIBLE,
  CONSTRAINT `fk_answer_question2`
    FOREIGN KEY (`question_id`)
    REFERENCES `manalabs`.`question` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_answer_user2`
    FOREIGN KEY (`user_id`)
    REFERENCES `manalabs`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `manalabs`.`quiz_event` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `timestamp` TIMESTAMP NOT NULL,
  `type` CHAR(5),
  `quiz_id` INT UNSIGNED NOT NULL,
  `user_id`INT NOT NULL,
  PRIMARY KEY(`id`),
  KEY `fk_take_quiz_idx` (`quiz_id`),
  KEY `fk_take_user_idx` (`user_id`),
  CONSTRAINT `fk_take_quiz_id` 
    FOREIGN KEY (`quiz_id`)
    REFERENCES `manalabs`.`quiz` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_take_user_id` 
    FOREIGN KEY (`user_id`)
    REFERENCES `manalabs`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

------------- 4th of Feb 2023
ALTER TABLE offering ADD COLUMN `hasQuiz` TINYINT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE offering ADD COLUMN `hasLab` TINYINT UNSIGNED NOT NULL DEFAULT 0;

------------- 5th of Feb 2023
ALTER TABLE `session` RENAME class_session;

------------- 9th of Feb 2023
ALTER TABLE `offering` ADD COLUMN `active` TINYINT UNSIGNED NOT NULL DEFAULT 1;

------------- 10th of Feb 2023
ALTER TABLE `user` ADD COLUMN `isAdmin` TINYINT UNSIGNED NOT NULL DEFAULT 0;
ALTER TABLE `user` ADD COLUMN `isFaculty` TINYINT UNSIGNED NOT NULL DEFAULT 0;
UPDATE `user` SET isAdmin = 1 WHERE id = 5;
UPDATE `user` SET isFaculty = 1 WHERE type = 'admin';
ALTER TABLE `user` DROP `type`;
ALTER TABLE `user` DROP hasPicture; -- cleanup, still not using this 

ALTER TABLE `enrollment` ADD COLUMN `auth` VARCHAR(45) NOT NULL DEFAULT "observer";
UPDATE `enrollment` SET `auth` = "student";
INSERT INTO `enrollment` (id, user_id, offering_id, auth) SELECT NULL, `fac_user_id`, `id`, "instructor" FROM `offering`;
ALTER TABLE `offering` DROP `fac_user_id`;

-- add indexes to: user.isAdmin user.isFaculty and enrollment.auth
CREATE INDEX user_isAdmin ON user (isAdmin);
CREATE INDEX user_isFaculty ON user (isFaculty);
CREATE INDEX enrollment_auth ON enrollment (auth);

-- 21st of May 2023 updates to offering to help MSD admissions
ALTER TABLE `offering` ADD COLUMN `showDates` TINYINT UNSIGNED NOT NULL DEFAULT 1;
ALTER TABLE `offering` ADD COLUMN `usesFlowcharts` TINYINT UNSIGNED NOT NULL DEFAULT 0;

-- 26th of May 2023 create excused table
CREATE TABLE IF NOT EXISTS `manalabs`.`excused` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `class_session_id`INT UNSIGNED NOT NULL,
  `teamsName` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_class_session_idx` (`class_session_id`),
  CONSTRAINT `fk_class_session_id1`
    FOREIGN KEY (`class_session_id`)
    REFERENCES `class_session` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
)ENGINE = InnoDB;

-- 1st of June 2023
ALTER TABLE `excused` ADD COLUMN `reason` VARCHAR(45) NOT NULL;

-- 14th of June 2023
UPDATE `question` SET `type` = 'text' WHERE `type` = "markdown" OR `type` = "plain_text";
ALTER TABLE `answer` ADD COLUMN `hasMarkDown` TINYINT UNSIGNED DEFAULT 0;
UPDATE `answer` SET `hasMarkDown` = 1;
ALTER TABLE `question` ADD COLUMN `hasMarkDown` TINYINT UNSIGNED DEFAULT 0;
ALTER TABLE `question` ADD COLUMN `mdlAnsHasMD` TINYINT UNSIGNED DEFAULT 0;
UPDATE `question` SET `hasMarkDown` = 1, `mdlAnsHasMD` = 1 WHERE `type` = 'text';

--- 19 June 2023
CREATE TABLE IF NOT EXISTS `CAMS` (
  `offering_id` INT NOT NULL,
  `username` VARCHAR(45) NOT NULL,
  `course_id` INT UNSIGNED ,
  `AM_id` INT UNSIGNED ,
  `PM_id` INT UNSIGNED ,
  `SAT_id` INT UNSIGNED ,
  INDEX `fk_CAMS_offering1_idx` (`offering_id` ASC) VISIBLE,
  PRIMARY KEY (`offering_id`),
  CONSTRAINT `fk_CAMS_offering1`
    FOREIGN KEY (`offering_id`)
    REFERENCES `offering` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

ALTER TABLE `offering` ADD COLUMN `hasCAMS` TINYINT UNSIGNED DEFAULT 0;
ALTER TABLE `class_session` CHANGE `type` `type` CHAR(3);

-- 30 Sept 2023 Lab subsystem
-- -----------------------------------------------------
-- Table `manalabs`.`lab`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `manalabs`.`lab` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `day_id` INT NOT NULL,
  `name` VARCHAR(45) NOT NULL DEFAULT "",
  `desc` TEXT NOT NULL DEFAULT "",
  `hasMarkDown` TINYINT NOT NULL DEFAULT 0,
  `start` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `stop` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `visible` TINYINT NOT NULL DEFAULT 0,
  `type` VARCHAR(45) NOT NULL DEFAULT 'Individual',
  PRIMARY KEY (`id`),
  INDEX `fk_lab_day1_idx` (`day_id` ASC) VISIBLE,
  CONSTRAINT `fk_lab_day1`
    FOREIGN KEY (`day_id`)
    REFERENCES `manalabs`.`day` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `manalabs`.`submission`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `manalabs`.`submission` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lab_id` BIGINT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `group` VARCHAR(45) NULL,
  `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `lab_duration` TIME NOT NULL DEFAULT "00:00:00",
  `stuComment` TEXT NOT NULL DEFAULT "",
  `stuCmntHasMD` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `gradeComment` TEXT NULL,
  `gradeCmntHasMD` TINYINT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_hw_submit_lab1_idx` (`lab_id` ASC) VISIBLE,
  INDEX `fk_hw_submit_user1_idx` (`user_id` ASC) VISIBLE,
  CONSTRAINT `fk_hw_submit_lab1`
    FOREIGN KEY (`lab_id`)
    REFERENCES `manalabs`.`lab` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_hw_submit_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `manalabs`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `manalabs`.`deliverable`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `manalabs`.`deliverable` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lab_id` BIGINT UNSIGNED NOT NULL,
  `type` VARCHAR(45) NOT NULL DEFAULT "text",
  `seq` INT UNSIGNED NOT NULL DEFAULT 1,
  `desc` TEXT NOT NULL DEFAULT "",
  `hasMarkDown` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `points` TINYINT UNSIGNED NOT NULL DEFAULT 10, 
  PRIMARY KEY (`id`),
  INDEX `fk_deliverable_lab1_idx` (`lab_id` ASC) VISIBLE,
  CONSTRAINT `fk_deliverable_lab1`
    FOREIGN KEY (`lab_id`)
    REFERENCES `manalabs`.`lab` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `manalabs`.`delivers`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `manalabs`.`delivers` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `deliverable_id` INT UNSIGNED NOT NULL,
  `submission_id` BIGINT UNSIGNED NOT NULL,
  `completion` TINYINT UNSIGNED NOT NULL,
  `text` TEXT NOT NULL DEFAULT "",
  `hasMarkDown` TINYINT NULL,
  `points` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_delivers_hw_submit1_idx` (`submission_id` ASC) VISIBLE,
  INDEX `fk_delivers_deliverable1_idx` (`deliverable_id` ASC) VISIBLE,
  CONSTRAINT `fk_delivers_hw_submit1`
    FOREIGN KEY (`submission_id`)
    REFERENCES `manalabs`.`submission` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_delivers_deliverable1`
    FOREIGN KEY (`deliverable_id`)
    REFERENCES `manalabs`.`deliverable` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `manalabs`.`attachment`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `manalabs`.`attachment` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `lab_id` BIGINT UNSIGNED NOT NULL,
  `file` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_attachment_lab1_idx` (`lab_id` ASC) VISIBLE,
  CONSTRAINT `fk_attachment_lab1`
    FOREIGN KEY (`lab_id`)
    REFERENCES `manalabs`.`lab` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;
