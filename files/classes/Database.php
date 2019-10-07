<?php
class Database {
	private static $INSTANCE = null;

	public static function GetInstance() {
		if(self::$INSTANCE == null) {
			try {
				self::$INSTANCE = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASSWORD, MYSQL_DATABASE);
        self::$INSTANCE->query('SET NAMES utf8');
        self::$INSTANCE->query('SET CHARACTER SET utf8');
			} catch(PDOException $e) {
				return NULL;
			}
		}
		return self::$INSTANCE;
	}
}
?>
