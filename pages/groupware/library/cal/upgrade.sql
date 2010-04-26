



-- THIS SQL FILE WILL UPGRADE YOU'RE 0.8 DATABASE TO REECE-CALENDAR 0.9
-- If you're using 0.7 for some reason, this will *not* upgrade. you
--
-- TO RUN THIS FILE, SIMPLY RUN THE COMMAND BELOW ON THE COMMAND LINE:
-- mysql -u root -p thenameofthedatabase < reececalendar.sql
-- OR YOU CAN COPY AND PASTE THIS FILE INTO MYPHPADMIN OR WHATEVER.



-- changes the description to TEXT instead of varchar
ALTER TABLE `cal_events` CHANGE `description` `description` TEXT NULL DEFAULT NULL;

-- this adds a fulltext index to the event data so we can search it
ALTER TABLE `cal_events` ADD FULLTEXT (
`subject` ,
`description`
);

-- this will add the modifcation columns to the event table
ALTER TABLE `cal_events` ADD `mod_id` INT NULL default NULL,
ADD `mod_username` VARCHAR( 50 ) NULL default NULL,
ADD `mod_stamp` DATETIME NULL default NULL;

-- add unique constraint to username
ALTER TABLE `cal_accounts` ADD UNIQUE ( `user` );

-- add index to username just in case you want like a billion users :)
ALTER TABLE `cal_accounts` ADD INDEX ( `user` );






