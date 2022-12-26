# link-qr
# Config
Create a file `app.conf` with this content
```
base_url = 'https://your-domain.orch/'
default_url = 'https://de.wikipedia.org/'

[db]
server = p:localhost
name = 'schema'
user = 'user'
passwort = 'pass'
```


## Tables and views
```sql
CREATE TABLE `code` (
  `user_id` int(10) unsigned NOT NULL DEFAULT 0,
  `code` char(5) COLLATE latin1_bin NOT NULL,
  `url` varchar(4096) COLLATE latin1_bin DEFAULT NULL,
  `last_used` datetime NOT NULL DEFAULT current_timestamp(),
  `hits` int(10) unsigned NOT NULL DEFAULT 0,
  `url_sha` char(64) GENERATED ALWAYS AS (sha2(`url`,256)) STORED,
  PRIMARY KEY (`code`) USING HASH,
  UNIQUE KEY `ix_code_url_sha` (`url_sha`)

) ENGINE=Aria DEFAULT CHARSET=latin1;
ALTER TABLE `code` ADD FULLTEXT KEY `ix_code_url` (`url`);

CREATE TABLE `user` (
  `id` int(10) NOT NULL,
  `username` varchar(50) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `vorname` varchar(30) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `nachname` varchar(30) COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `hash` varchar(255) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `uuid` varchar(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `email` varchar(255) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `last_update` datetime NOT NULL DEFAULT current_timestamp(),
  `last_login` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=Aria DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ix_user_username` (`username`),
  ADD UNIQUE KEY `ix_user_email` (`email`);

CREATE ALGORITHM=UNDEFINED DEFINER=`link_qr`@`localhost` SQL SECURITY DEFINER VIEW `used`  AS SELECT count(`code`.`url`) AS `used`, count(0) AS `total` FROM `code``code`  ;


DELIMITER $$
CREATE FUNCTION `get_url`(`c` CHAR(5)) RETURNS varchar(4096)
    DETERMINISTIC
    SQL SECURITY INVOKER
begin
    declare result varchar(4096);
    update code
	set hits = hits + 1,last_used=current_timestamp()
        where code = c;
    return (
	select url
        from code
        where code = c
    );
end$$
DELIMITER ;


DELIMITER $$
CREATE FUNCTION `set_url`(`the_user_id` INT, `the_url` VARCHAR(4096)) RETURNS char(5)
    DETERMINISTIC
    SQL SECURITY INVOKER
begin
    declare result char(5);

    select `code` into result from `code` where url = the_url limit 1;
    if result is null then
        select `code` into result from `code` where url is null limit 1;
		update `code` set user_id=the_user_id, url=the_url where `code`=result;
    end if;
    return result;
end$$
DELIMITER ;

```
