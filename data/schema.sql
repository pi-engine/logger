CREATE TABLE `log`
(
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `timestamp`    DATETIME         NOT NULL,
    `priority`     INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `priorityName` VARCHAR(255)     NOT NULL DEFAULT '',
    `message`      VARCHAR(255)     NOT NULL DEFAULT '',
    `extra_data`        JSON,
    PRIMARY KEY (`id`)
);