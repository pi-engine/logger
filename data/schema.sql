CREATE TABLE `logger_log` (
                               `id` int(11) NOT NULL,
                               `user_id` int(11) NOT NULL DEFAULT 0,
                               `item_id` int(11) NOT NULL DEFAULT 0,
                               `action` varchar(255) NOT NULL DEFAULT '',
                               `type` varchar(255) NOT NULL DEFAULT '',
                               `event` varchar(255) NOT NULL DEFAULT '',
                               `date` varchar(255) NOT NULL DEFAULT '',
                               `time_create` int(11) NOT NULL DEFAULT 0,
                               `time_update` int(11) NOT NULL DEFAULT 0,
                               `time_delete` int(11) NOT NULL DEFAULT 0
);