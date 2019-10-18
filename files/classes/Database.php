<?php
class Database {
	private static $INSTANCE = NULL;

	public static function GetInstance() {
		if(self::$INSTANCE == NULL) {
			try {
				self::$INSTANCE = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE, MYSQL_PORT);
				if (!self::$INSTANCE->connect_errno) {
					self::$INSTANCE->query('SET NAMES utf8');
					self::$INSTANCE->query('SET CHARACTER SET utf8');
				}
			} catch(PDOException $e) {
				return NULL;
			}
		}
		return self::$INSTANCE;
	}
}
?>
