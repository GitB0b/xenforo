<?php

class BookmarkPosts_DataWriter_Bookmark extends XenForo_DataWriter
{
	
	/**
	 * Bookmark a post.
	 */
	public function bookmarkPost(array $post, $userId)
	{
		$options = $this->_getOptions();
		
		$data = array(
					'bookmark_user_id' 	=> $userId,
					'post_user_id' 		=> $post['user_id'], 
					'post_id' 			=> $post['post_id'],	
					'thread_id'			=> $post['thread_id'], 
					'bookmark_date'		=> XenForo_Application::$time,
		 			'bookmark_tag'		=> $options->bookmark_posts_option_defaultTag,
					'bookmark_note'		=> '',
					'bookmark_state'	=> $options->bookmark_posts_option_defaultState);
		
		$this->bulkSet($data);
		$this->save();		
		
		if ($this->_getOptions()->bookmark_posts_option_showInFeed)
		{
			$this->_publishToNewsFeed('bookmark');
		}	
	}
	
	/**
	 * Unbookmark a post.
	 * @since 1.2.0
	 */
	public function unBookmarkPost(array $bookmark)
	{	
		$this->setExistingData($bookmark['bookmark_id']);				
		$this->delete();
	}
	
	/**
	 * Sets a new state to a bookmark.
	 * @since 1.2.0
	 *  
	 */
	public function changeState($bookmarkId, $newState)
	{
		$this->setExistingData($bookmarkId);
		$this->set('bookmark_state', $newState);
		$this->save();
	}
	
	/**
	 * 
	 * Publish to the news feed.	 
	 * @since 1.2.0
	 */
	protected function _publishToNewsFeed($action)
	{	
		$user = XenForo_Visitor::getInstance();
		$userWhoPost = $this->getModelFromCache('XenForo_Model_User')->getUserById($this->get('post_user_id'));
		
		$this->getModelFromCache('XenForo_Model_NewsFeed')->publish(
			$user['user_id'],
			$user['username'],
			'post',
			$this->get('post_id'),
			$action
		);
	}
			
	
	
	
	/**
	* Gets the fields that are defined for the table. See parent for explanation.
	*
	* @return array
	* @since 1.2.0
	*/
	protected function _getFields() {
	    return array(
	        'xf_bookmark_posts' => array(
	            'bookmark_id' => array('type' => 'uint', 'autoIncrement' => true),
	            'bookmark_user_id' => array('type' => 'uint', 'required' => true),
	    		'post_user_id' => array('type' => 'uint', 'required' => true),
	            'post_id' => array('type' => 'uint', 'required' => true),
	    		'thread_id' => array('type' => 'uint', 'required' => true),	    		
	            'bookmark_date' => array('type' => 'uint', 'default' => XenForo_Application::$time),
	            'bookmark_tag' => array('type' => 'string', 'required' => false),
	    		'bookmark_note' => array('type' => 'string', 'required' => false),
	    		'bookmark_state' => array('type' => 'string', 'required' => true, 'default' => 'private'),
	        )
	    );
	}	
	
	/**
	* Gets the actual existing data out of data that was passed in. See parent for explanation.
	*
	* @param mixed
	*
	* @return array|false
	*/
	protected function _getExistingData($data)
	{
	    if (!$id = $this->_getExistingPrimaryKey($data, 'bookmark_id')) {
	        return false;
	    }
	
	    return array('xf_bookmark_posts' => $this->_getBookmarksModel()->getBookmarkById($id));
	}
	

	
	
	/**
	* Gets SQL condition to update the existing record.
	*
	* @return string
	*/
	protected function _getUpdateCondition($tableName)
	{
	    return 'bookmark_id = ' . $this->_db->quote($this->getExisting('bookmark_id'));
	}
	
	/**
	 * @return BookmarkPosts_Model_BookmarkPosts
	 * @since 1.2.0
	 */
	protected function _getBookmarksModel()
	{
		return $this->getModelFromCache('BookmarkPosts_Model_BookmarkPosts');
	}
	
	/**
	 * @since 1.2.0
	 */
	protected function _getOptions()
	{
		return XenForo_Application::get('options');
	}	
}