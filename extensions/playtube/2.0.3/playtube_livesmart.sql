INSERT INTO `config` (`name`) SELECT 'livesmart_url' FROM config WHERE NOT EXISTS (SELECT * FROM `config` WHERE `name`='livesmart_url' LIMIT 1) LIMIT 1;


INSERT INTO `langs` (`lang_key`, `type`, `english`, `arabic`, `dutch`, `french`, `german`, `russian`, `spanish`, `turkish`) 
  SELECT 'livesmart', 'livesmart', 'LiveSmart Video Chat', 'LiveSmart Video Chat', 'LiveSmart Video Chat', 'LiveSmart Video Chat', 'LiveSmart Video Chat', 'LiveSmart Video Chat', 'LiveSmart Video Chat', 'LiveSmart Video Chat' 
FROM langs WHERE NOT EXISTS (SELECT * FROM `langs` WHERE `lang_key`='livesmart' LIMIT 1) LIMIT 1;
