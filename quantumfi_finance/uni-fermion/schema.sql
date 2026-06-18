CREATE TABLE mg_users
(
	id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	username varchar (100) NOT NULL, 
	password varchar (100) NOT NULL,
	forgotPass varchar (100),
	email varchar (100) NOT NULL,
	fname varchar (100),
	lname varchar (100),
	city varchar (100),
	country varchar (100),
	ranking varchar (100),/*beginner, intermediate, advanced, expert*/
	created TIMESTAMP DEFAULT NOW()
);
alter table mg_users add column forgotPass varchar (100);
alter table mg_user_consent add column resultUsed tinyint(1);
alter table mg_user_consent change username email varchar (100) NOT NULL;

CREATE TABLE mg_user_consent
(
	consentId INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	name varchar (200),
	email varchar (100) NOT NULL,
	over18 tinyint(1),/*1=yes, -1=no*/
	tel varchar (20),
	address varchar (400),
	video tinyint(1),/*1=yes, -1=no*/
	audio tinyint(1),/*1=yes, -1=no*/
	photographs tinyint(1),/*1=yes, -1=no*/
	transcripts tinyint(1),/*1=yes, -1=no*/
	contacted tinyint(1),/*1=yes, -1=no*/
	feedback tinyint(1),/*1=yes, -1=no*/
	partiInfo tinyint(1),/*1=yes*/
	resultUsed tinyint(1),/*1=yes*/
	accept tinyint(1),/*1=yes*/
	pref_address varchar (400),
	pref_email varchar (200),
	pref_tel varchar (20)
);

CREATE TABLE mg_user_consent_del
(
	username varchar (100) NOT NULL, 
	name varchar (200),
	over18 varchar (5),/*1=yes, -1=no*/
	tel varchar (20),
	address varchar (200),
	video varchar (5),/*1=yes, -1=no*/
	audio varchar (5),/*1=yes, -1=no*/
	photographs varchar (5),/*1=yes, -1=no*/
	transcripts varchar (5),/*1=yes, -1=no*/
	contacted varchar (5),/*1=yes, -1=no*/
	feedback varchar (5),/*1=yes, -1=no*/
	partiInfo varchar (5),/*1=yes*/
	accept varchar (5),/*1=yes*/
	pref_address varchar (200),
	pref_email varchar (200),
	pref_tel varchar (200)
);


CREATE TABLE mg_games
(
	id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	/*gameUUID varchar (20) NOT NULL, /*Unique for each row*/
	pricePerShare varchar (20) NOT NULL,
	totalRound INT UNSIGNED NOT NULL,
	numOfPlayers INT UNSIGNED NOT NULL,
	currJoin INT UNSIGNED,
	currRoundNo INT UNSIGNED,
	status varchar (15) DEFAULT 'Waiting',/*Started, Waiting, End*/
	noises varchar (10000),
	startTime varchar (25) NOT NULL DEFAULT 'none',
	createdBy varchar (100) NOT NULL DEFAULT 'none',
	created TIMESTAMP DEFAULT NOW()
);
--alter table mg_games add column noises varchar (10000);
--alter table mg_games add column currRoundNo INT UNSIGNED;
--alter table mg_games add column createdBy varchar (100) NOT NULL DEFAULT 'none';
--alter table mg_games add column startTime varchar (25) NOT NULL DEFAULT '2011-05-12 18:20:20';

CREATE TABLE mg_user_game
(
	id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
	userId INT NOT NULL,
	username varchar (100) NOT NULL,
	roundNo TINYINT UNSIGNED, /*1, 2, ...*/
	gameId INT NOT NULL,
	pricePerShare varchar (20) NOT NULL,
	capital varchar (10),
	shares varchar (10),
	cash varchar (10),
	status varchar (10),/*quit, active*/
	minorityCount TINYINT,
	action TINYINT, /*1=buy, -1=sell*/
	minorityAction TINYINT /*1=buy, -1=sell*/
);
alter table mg_user_game add column status varchar (10);