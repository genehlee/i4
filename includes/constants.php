<?php
/*******************************
constants.php

By: Willy Xiao
willy@chenxiao.us

Developed for SCAS i4
masmallclaims@gmail.com

To use code, please contact SCAS or
Willy at the above emails. 

August 2013
***********************************/
 /* some functions differ based on LOCAL_HOST or server. */
define("SERVER_NAME", "hcs.harvard.edu"); 
// define("LOCAL_HOST_NAME", "WILLYXIAO-THINK"); 
define("LOCAL_HOST",  strstr(php_uname("n"), SERVER_NAME) === false);

// ROOT_ can be used to create public links or to run scripts
define("ROOT", dirname(dirname((__FILE__)))); 	
define("ROOT_FROM_DOMAIN", (LOCAL_HOST ? "/" : "/~scas/")); 
define("ROOT_PUBLIC", $_SERVER["HTTP_HOST"]. ROOT_FROM_DOMAIN . basename(ROOT)); 

define("LEADERBOARD_STATS_FILE", ROOT . "/data/" . "data_leaderboard.php"); 
define("AJAX_HASH", "THISISSPARTA");

define("SCAS_EMAIL", "masmallclaims@gmail.com"); 
define("ADMIN_EMAIL", "wxiao@college.harvard.edu"); 
define("LEGAL_RESEARCH_EMAIL", "SCASlegalresearch@gmail.com"); 

//** MYSQL DATABASE ITEMS **//
if(LOCAL_HOST) {
	define("DATABASE", "scas"); 
	define("SERVER", "localhost");
	define("USERNAME", "root"); 
	define("PASSWORD", ""); 
} else {
	define("DATABASE", "scas");
	define("PASSWORD", "pantsonfire");
	define("SERVER", "mysql.hcs.harvard.edu");
	define("USERNAME", "scas");	
}
?>
