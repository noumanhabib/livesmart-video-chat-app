INSERT INTO `Wo_Config` (`name`) SELECT 'livesmart_url' FROM Wo_Config WHERE NOT EXISTS (SELECT * FROM `Wo_Config` WHERE `name`='livesmart_url' LIMIT 1) LIMIT 1;


INSERT INTO `Wo_Langs` (`lang_key`, `type`, `english`, `arabic`, `dutch`, `french`, `german`, `italian`, `portuguese`, `russian`, `spanish`, `turkish`) 
  SELECT 'livesmart', 'livesmart', 'LiveSmart Video Chat', 'LiveSmart Video Chat', 'LiveSmart Video Chat', 'LiveSmart Video Chat', 'LiveSmart Video Chat', 'LiveSmart Video Chat', 'LiveSmart Video Chat', 'LiveSmart Video Chat', 'LiveSmart Video Chat', 'LiveSmart Video Chat' 
FROM Wo_Langs WHERE NOT EXISTS (SELECT * FROM `Wo_Langs` WHERE `lang_key`='livesmart' LIMIT 1) LIMIT 1;

INSERT INTO `Wo_Langs` (`lang_key`, `type`, `english`, `arabic`, `dutch`, `french`, `german`, `italian`, `portuguese`, `russian`, `spanish`, `turkish`) 
  SELECT 'livesmart_dashboard', 'livesmart_dashboard', 'LiveSmart Dashboard', 'LiveSmart Dashboard', 'LiveSmart Dashboard', 'LiveSmart Dashboard', 'LiveSmart Dashboard', 'LiveSmart Dashboard', 'LiveSmart Dashboard', 'LiveSmart Dashboard', 'LiveSmart Dashboard', 'LiveSmart Dashboard' 
FROM Wo_Langs WHERE NOT EXISTS (SELECT * FROM `Wo_Langs` WHERE `lang_key`='livesmart_dashboard' LIMIT 1) LIMIT 1;


INSERT INTO `Wo_Langs` (`lang_key`, `type`, `english`, `arabic`, `dutch`, `french`, `german`, `italian`, `portuguese`, `russian`, `spanish`, `turkish`) 
  SELECT 'livesmart_request_video_chat', 'livesmart_request_video_chat', 'Request for a video chat', 'Request for a video chat', 'Request for a video chat', 'Request for a video chat', 'Request for a video chat', 'Request for a video chat', 'Request for a video chat', 'Request for a video chat', 'Request for a video chat', 'Request for a video chat' 
FROM Wo_Langs WHERE NOT EXISTS (SELECT * FROM `Wo_Langs` WHERE `lang_key`='livesmart_dashboard' LIMIT 1) LIMIT 1;