<?php
/**
 * 
 * Installer
 * @author Owner
 * @since 1.0.0
 */
class BookmarkPosts_Installer
{
	/**
	 * @version 1.2.1
	 */
	protected static $_tables = array(
		'bookmaker_posts' => array(
			'createQuery' => 'CREATE TABLE IF NOT EXISTS `xf_bookmark_posts` (
  				`bookmark_id` int(11) NOT NULL AUTO_INCREMENT,
  				`bookmark_user_id` int(11) NOT NULL,
  				`post_user_id` int(11) NOT NULL,
			  	`post_id` int(11) NOT NULL,
			  	`thread_id` int(11) NOT NULL,			  	
				`bookmark_date` int(11) NOT NULL,
				`bookmark_tag` varchar(50) NOT NULL,
				`bookmark_note` varchar(255) NOT NULL,
				`bookmark_state` varchar(7) NOT NULL,
			  	PRIMARY KEY (`bookmark_id`),
			  	UNIQUE KEY content (post_id, bookmark_user_id)
			) ENGINE = InnoDB CHARACTER SET utf8 COLLATE utf8_general_ci;',
			'dropQuery' => 'DROP TABLE `xf_bookmark_posts`'
		)
	);
	
	protected static $_patches = array(
		0 => array(
			'table' => 'xf_bookmark_posts',
			'field' => 'post_user_id',
			'showColumnsQuery' => 'SHOW COLUMNS FROM `xf_bookmark_posts` LIKE \'post_user_id\'',
			'alterTableAddColumnQuery' => 'ALTER TABLE `xf_bookmark_posts` ADD COLUMN `post_user_id` INT ( 11) NOT NULL'
		),
		1 => array(
			'table' => 'xf_bookmark_posts',
			'field' => 'thread_id',
			'showColumnsQuery' => 'SHOW COLUMNS FROM `xf_bookmark_posts` LIKE \'thread_id\'',
			'alterTableAddColumnQuery' => 'ALTER TABLE `xf_bookmark_posts` ADD COLUMN `thread_id` INT ( 11) NOT NULL'
		)
	);
	
	
		
	
	
	/**	  
	 * @since 1.0.0
	 * @version	1.2.1
	 */
	public static function install() {
		$db = XenForo_Application::get('db');

		foreach (self::$_tables as $table) {
			$db->query($table['createQuery']);
		}
		
		foreach (self::$_patches as $patch) {
			$existed = $db->fetchOne($patch['showColumnsQuery']);
			if (empty($existed)) {
				$db->query($patch['alterTableAddColumnQuery']);
			}
		}
		
		$existed = $db->fetchAll('SHOW COLUMNS FROM `xf_bookmark_posts`');
				
		if (isset($existed['user_id']))
		{
			$db->query('ALTER TABLE `xf_bookmark_posts` CHANGE `user_id`  `bookmark_user_id` INT( 11 ) NOT NULL');
		}
		
		
		/**
	 	* Postit Importer
	 	* @version 1.1.0
	 	*/
		/*If ($db->fetchRow('SHOW TABLES LIKE \'postit_content\''))		
		{
			$db->query
					('
					INSERT IGNORE INTO xf_bookmark_posts
						(user_id, post_id, bookmark_date, bookmark_tag, bookmark_note, bookmark_state)
						SELECT postit_user_id, content_id, postit_date, \'Imported\', postit_note, \'private\' FROM postit_content
					');	
		}*/
	}
		
	
	
	/**	  
	 * @since 1.0.0	 
	 */
	public static function uninstall() {
		$db = XenForo_Application::get('db');

		foreach (self::$_tables as $table) {
			$db->query($table['dropQuery']);
		}
	}
	
}