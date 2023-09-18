CREATE TABLE `log_inventory`
(
    `id`           INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `timestamp`    DATETIME         NOT NULL,
    `priority`     INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `priorityName` VARCHAR(255)     NOT NULL DEFAULT '',
    `message`      VARCHAR(255)     NOT NULL DEFAULT '',
    `extra_data`   JSON,
    PRIMARY KEY (`id`)
);

CREATE TABLE `log_user`
(
    `id`          INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `time_create` INT(10) UNSIGNED NOT NULL DEFAULT '0',
    `state`       VARCHAR(32)      NOT NULL DEFAULT '',
    `information` JSON,
    PRIMARY KEY (`id`)
);