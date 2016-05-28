<?php
return [
	/**
	 * Directtory to place backup files
	 * Must be without trailing slashes
	 */
	'backupDir'	=> base_path().'/BACKUPS',
	/**
	 * array of database-aliases from config/database.php
	 * (key values of connections array)
	 */
	'databases' => [
		'mysql',
	],
	/**
	 * Exclude to backup
	 * WARNING! DO NOT REMOVE BACKUPS DIRECTORY, TO AVOID EXCESS ARCHIVE SIZE
	 */
	'exclude'   => [
		base_path().'/BACKUPS',	// Archive storage
		base_path().'/.git',	// git version control
		base_path().'/.svn',	// subversion version control
		base_path().'/.hg',		// mercurial version control
	],
];
