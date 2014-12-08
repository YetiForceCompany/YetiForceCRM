
DROP TABLE IF EXISTS chat_online;
CREATE TABLE chat_online (
	userID INT(11) NOT NULL,
	userName VARCHAR(64) NOT NULL,
	userRole INT(1) NOT NULL,
	channel INT(11) NOT NULL,
	dateTime DATETIME NOT NULL,
	ip VARBINARY(16) NOT NULL,
	PRIMARY KEY (userID),
	INDEX (userName)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS chat_messages;
CREATE TABLE chat_messages (
	id INT(11) NOT NULL AUTO_INCREMENT,
	userID INT(11) NOT NULL,
	userName VARCHAR(64) NOT NULL,
	userRole INT(1) NOT NULL,
	channel INT(11) NOT NULL,
	dateTime DATETIME NOT NULL,
	ip VARBINARY(16) NOT NULL,
	text TEXT,
	PRIMARY KEY (id),
	INDEX message_condition (id, channel, dateTime),
	INDEX (dateTime)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS chat_bans;
CREATE TABLE chat_bans (
	userID INT(11) NOT NULL,
	userName VARCHAR(64) NOT NULL,
	dateTime DATETIME NOT NULL,
	ip VARBINARY(16) NOT NULL,
	PRIMARY KEY (userID),
	INDEX (userName),
	INDEX (dateTime)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS chat_invitations;
CREATE TABLE chat_invitations (
	userID INT(11) NOT NULL,
	channel INT(11) NOT NULL,
	dateTime DATETIME NOT NULL,
	PRIMARY KEY (userID, channel),
	INDEX (dateTime)
) DEFAULT CHARSET=utf8;
