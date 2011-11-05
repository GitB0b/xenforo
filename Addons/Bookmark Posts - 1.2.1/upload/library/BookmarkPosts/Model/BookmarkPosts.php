<?php

/**
 * 
 * Modelo do BookmarkPosts
 * Model BookmarkPosts
 * @author Fuhrmann
 * @since 1.0.0
 *
 */
class BookmarkPosts_Model_BookmarkPosts extends XenForo_Model
{
	

	/**
	 * 
	 * Get bookmark by id.
	 * @param integer $userId
	 * @param string $tag
	 * @since 1.2.0
	 */
	public function getBookmarkById($bookmarkId)
	{
		$db = $this->_getDb();
		
		return $db->fetchRow('
			SELECT bookmarks.* FROM xf_bookmark_posts as bookmarks			
				WHERE bookmark_id = ?
		', $bookmarkId);
	}
	
	
	/**
	 * 
	 * Get a bookmark by id and user id
	 * @param $bookmarkId
	 * @param $userId
	 */
	public function getBookmarkByPostIdAndUser($postId, $userId)
	{
		$db = $this->_getDb();
		
		return $db->fetchRow('
			SELECT * FROM xf_bookmark_posts			
				WHERE post_id = ? AND bookmark_user_id = ?
		', array($postId, $userId));
	}
	
	
	/**
	 * 
	 * Get all the bookmarks by tag and user
	 * @param integer $userId
	 * @param string $tag
	 * @since 1.2.0
	 */
	public function getBookmarkByTagAndUser($userId, $tag)
	{
		$db = $this->_getDb();
		
		$result = $db->fetchAll('
			SELECT * FROM xf_bookmark_posts			
				WHERE bookmark_user_id = ? and bookmark_tag = ? 
					ORDER BY bookmark_date DESC
		', array($userId, $tag));	

		return $result;	
	}
	
	
	/**
	 * 
	 * Get all the bookmarks by tag
	 * @param integer $userId
	 * @param string $tag
	 * @since 1.2.0
	 */
	public function getBookmarkByTag($tag)
	{
		$db = $this->_getDb();
		return $this->_getDb()->fetchAll('SELECT * FROM xf_bookmark_posts WHERE bookmark_tag = ? ORDER BY bookmark_date DESC', $tag);			
	}
	
	/**
	 * Pega todos os favoritos do usuário (privado ou compartilhado)
	 * Get all bookmarks from user (private or public)
	 * @since 1.0.0
	 */
	public function getBookmarksFromUser($userId, array $fetchOptions = array(), $state = null, array $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		
		$limitOptions = $this->prepareLimitFetchOptions($fetchOptions);
		$where = '';		
		
		if ($state !== null)
		{
			$where = ' AND bookmark_state = ?';			
		}
			
		return $this->fetchAllKeyed($this->limitQueryResults(
			'SELECT * FROM xf_bookmark_posts AS bookmarks			
			WHERE bookmarks.bookmark_user_id = ? ' . $where . '
				ORDER BY bookmarks.bookmark_date DESC
			', $limitOptions['limit'], $limitOptions['offset']
		), 'bookmark_id', $state ? array($userId,$state): $userId);
	}
	
	
	/**
	 * 
	 * Retorna apenas uma linha do banco de dados dos posts favoritados pelo usuario.
	 * Return one row from DB of the bookmarked posts from user.
	 * @param array $thread
	 * @param unknown_type $useDefaultIfNotWatching
	 * @param array $viewingUser
	 * @since 1.0.0
	 */
	public function getBookmarkPost($post_id, $user_id)
	{
		return $this->_getDb()->fetchRow('
	    	SELECT * FROM 
	    		xf_bookmark_posts
    		WHERE post_id = ? AND bookmark_user_id = ?
	    	', array($post_id, $user_id));			
	}
	
	
	
	
	/**
	 * 
	 * Get all the bookmarks tags from user
	 * @param integer $userId
	 * @since 1.1.0
	 * 
	 */
	public function getAllTagsFromUser($userId)
	{
		$db = $this->_getDb();
		
		$result = $db->fetchAll('
			SELECT DISTINCT bookmark_tag FROM xf_bookmark_posts
			WHERE bookmark_user_id = ?
		', $userId);		

		return $result;	
	}
	
	
	
	
	
	
	
	
	
	
	/**
	 * 
	 * Get the latests posts bookmarked
	 * @param string $state
	 * @param integer $quant
	 * @since 1.2.0
	 */
	public function getLatestBookmark($state, $quant)
	{
		$db = $this->_getDb();
		
		return $db->fetchAll('SELECT * FROM xf_bookmark_posts WHERE bookmark_state = ? 
				ORDER BY bookmark_date DESC LIMIT ?',
				array($state, $quant));
	}
	
	
	public function getMostPostsBookmark($state, $quant)
	{
		$db = $this->_getDb();
		
		return $db->fetchAll('SELECT bookmarks.*, bookmarks.post_id AS bookmark_post_id, COUNT(bookmark_id) AS num_bookmarks
							FROM xf_bookmark_posts as bookmarks WHERE bookmark_state = ? GROUP BY bookmark_post_id 
							ORDER BY num_bookmarks DESC LIMIT ?', array($state, $quant));
	}
	
	
	/**
	 * 
	 * Get all the notes of a specific public bookmark by post_id. 
	 * @param integer $bookmarkId
	 * @since 1.2.0
	 */
	public function getAllNotesByPostId($postId, $userId)
	{
		
		$result = $this->_getDb()->fetchAll(
			'SELECT bookmarks.*,					
					user.user_id, user.username, user.gender, user.avatar_date, user.gravatar					
				FROM xf_bookmark_posts AS bookmarks			
			LEFT JOIN xf_user AS user
				ON (user.user_id = bookmarks.bookmark_user_id)
			WHERE bookmarks.post_id = ? AND bookmark_user_id <> ? AND bookmark_note <> ?
				ORDER BY bookmarks.bookmark_date DESC			
		', array($postId, $userId, ''));
		
		foreach ($result as &$r)
		{
			$r['user'] = XenForo_Application::arrayFilterKeys($r, array(
					'user_id',
					'username',
					'gender',
					'gravatar',
					'avatar_date'
					)
			);
		}
		return $result;
		
		
	}
	
	
	
	
	
	/**
	 * Conta o número total de favoritos de um usuário (privado ou compartilhado).
	 * Count the number of bookmarks by user (private or public).
	 *
	 * @param integer $userId
	 * @return integer
	 * @since 1.0.0
	 */
	public function countBookmarksForContentUser($userId)
	{
		return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM xf_bookmark_posts
			WHERE bookmark_user_id = ?
		', $userId);
	}
	
	/**
	 * Conta o número total de favoritos compartilhados de um usuário.
	 * Count the number of public bookmarks by user.
	 *
	 * @param integer $userId
	 * @return integer
	 * @since 1.0.0
	 */
	public function countBookmarksPublicdbyUser($userId)
	{
		return $this->_getDb()->fetchOne('
			SELECT COUNT(*)
			FROM xf_bookmark_posts
			WHERE bookmark_user_id = ? AND bookmark_state = ?
		', array($userId, 'public'));
	}
	
	
	public function countBookmarksByPost($postId, $state = null)
	{		
		$where = '';
		if (!empty($state))
		{
			$where = ' AND bookmark_state = ?';			
		}
		$query = $this->_getDb()->query('SELECT bookmark_id FROM xf_bookmark_posts WHERE post_id = ?' . $where, !empty($state) ? array($postId, $state) : $postId);
        return $query->rowCount();		
	}
	
	/**
	 * 
	 * Verifica se o usuário tem permissão para favoritar posts
	 * User has permission to bookmark posts?
	 * @param unknown_type $viewingUser
	 * @since 1.0.0
	 */
	
	public function canBookmarkPost ($viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		
		//verifica se o usuário tem permissão para favoritar
		return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'bookmarkPosts');
	}
	
	/**
	 *
	 * User has permission to view bookmarked posts?
	 * @param $viewingUser
	 * @since 1.2.0
	 */
	
	public function canViewBookmarkedPost ($viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		
		//verifica se o usuário tem permissão para favoritar
		return XenForo_Permission::hasPermission($viewingUser['permissions'], 'general', 'viewBookmarkedPosts');
	}
	
	
	/**
	 * 
	 * Verifica se o post já foi favoritado pelo usuário
	 * User already bookmarked this post?
	 * @param array $post
	 * @param unknown_type $viewingUser
	 * @since 1.0.0
	 */
	public function alreadyBookmarkedPost (array $post, $viewingUser = null)
	{
		$this->standardizeViewingUserReference($viewingUser);
		
		//verifica se o post ja foi favoritado
		if ($this->getBookmarkPost($post['post_id'], $viewingUser['user_id']))
		{
			return true;
		}
		return false;
	}
	
	
	
	
	
	
	/**
	 * 
	 * Handles the search of bookmarks. Can search with or without a tag.
	 * @param unknown_type $userId
	 * @param unknown_type $string
	 * @since 1.1.0
	 */
	public function searchBookmarksContent($userId, $string, $tag)
	{
		$db = $this->_getDb();
		
		$where = '';
		
		$tag == '(Select a tag)' ? $tag = null : '';
		if (!empty($tag))
		{			
			$where = 'AND bookmark_tag = ?';
		}
			
		$result = $db->fetchAll('
			SELECT DISTINCT bookmark.*, 
	    		   post.post_id, post.thread_id, post.message,
	    		   thread.thread_id, thread.title,
	    		   user.user_id, user.username, user.gender, user.avatar_date, user.gravatar
	    		FROM xf_bookmark_posts as bookmark	    	
	    	INNER JOIN xf_post AS post
				ON (post.post_id = bookmark.post_id)			
			INNER JOIN xf_user AS user
				ON (user.user_id = bookmark.bookmark_user_id)
			LEFT JOIN xf_thread AS thread
				ON (thread.thread_id = post.thread_id)
			WHERE bookmark.bookmark_user_id = ? '. $where .' AND thread.title LIKE ' . XenForo_Db::quoteLike($string, 'lr'), !empty($where) ? array($userId, $tag) :  $userId);
					
		return $result;	
	}
	
	
	/**
	 * 
	 * Fill the user and post array with data.
	 * @param array $bookmarks
	 * @since 1.1.0
	 */
	public function prepareBookmark($bookmarks, $userClass = null, array $fields = array())
	{		
		if (empty($fields)){
			$fields = array(
				 'post' 	=> array('thread_id', 'message', 'post_id', 'user_id'),
				 'thread' 	=> array('title', 'node_id', 'thread_id'), 
				 'forum' 	=> array('title', 'node_id'),
				 'user' 	=>  array('user_id', 'username', 'gender', 'avatar_date', 'gravatar')		
			);
		}		
		$bookmarks = BookmarkPosts_Helper_Bookmarks::getPostsInfo($bookmarks, $fields['post']);
		if ($userClass == 'post')
		{
			$bookmarks = BookmarkPosts_Helper_Bookmarks::getUsersPostInfo($bookmarks, $fields['user']);
		}
		else
		{
			$bookmarks = BookmarkPosts_Helper_Bookmarks::getUsersBookmarkInfo($bookmarks, $fields['user']);
		}
		
		$bookmarks = BookmarkPosts_Helper_Bookmarks::getThreadsInfo($bookmarks, $fields['thread']);
		$bookmarks = BookmarkPosts_Helper_Bookmarks::getForumsInfo($bookmarks, $fields['forum']);
		return $bookmarks;
	}
	
	
	public function prepareBookmarkForView ($bookmark)
	{		
		$visitor = XenForo_Visitor::getInstance()->toArray();		
		$bookmark['content'] = BookmarkPosts_Helper_Bookmarks::getContentData(array($bookmark['post_id']), $visitor);		
		$bookmark['content_user'] = XenForo_Application::arrayFilterKeys($bookmark['content'], array(
					'user_id',
					'username',
					'gender',
					'gravatar',
					'avatar_date',
		));
		$bookmark['user'] = $this->_getUserModel()->getUserById($bookmark['bookmark_user_id']);
		return $bookmark;
	}
	
	
	/**
	* 
	* Delete all bookmarks from user
	* @since 1.1.0
	* @param integer $userId
	*/
	public function deleteBookmarksFromUser($userId)
	{	
		$db = $this->_getDb();
		$db->query('
	    	DELETE FROM xf_bookmark_posts
	    	WHERE bookmark_user_id = ?	    	
	    	', $userId);
	}
	
	
	/**
	 * 
	 * Verify if the given bookmark is public.
	 * @param array $bookmark
	 * @since 1.2.0
	 */
	public function isBookmarkPublic(array $bookmark)
	{
		return $bookmark['bookmark_state'] == 'public' ? true : false;
	}

	
	protected function _getOptions()
	{
		return XenForo_Application::get('options');
	}
	
	/**
	 * @return XenForo_Model_User
	 * @since 1.1.0
	 */
	protected function _getUserModel()
	{
		return $this->getModelFromCache('XenForo_Model_User');
	}
}