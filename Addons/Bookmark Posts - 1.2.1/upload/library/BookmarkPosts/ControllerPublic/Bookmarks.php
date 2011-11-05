<?php
/**
 * 
 * Enter description here ...
 * @author Fuhrmann
 * @since 1.1.0
 *
 */
class BookmarkPosts_ControllerPublic_Bookmarks extends XenForo_ControllerPublic_Abstract
{
	protected $Allbookmarks = array ();
	
	/**
	 * Favorita/Desfavorita um post.
	 * Bookmark/UnBookmark a post.
	 * @since 1.1.0
	 * TODO
	 */
	public function actionIndex()
	{
		$this->_assertRegistrationRequired();
		$options = $this->_getOptions();
		if (!$this->_getBookmarksModel()->canViewBookmarkedPost())
		{
			return $this->responseNoPermission();
		}
    	    	
    	$visitor = XenForo_Visitor::getInstance()->toArray();
    	
    	$bookmarkModel = $this->_getBookmarksModel();		
		
    	$latestBookmarks = $bookmarkModel->getLatestBookmark('public', $options->bookmark_posts_option_latest_number_home);
    	$mostBookmarks = $bookmarkModel->getMostPostsBookmark('public', $options->bookmark_posts_option_most_posts);
    	    	
		foreach ($latestBookmarks as &$bookmark)
		{
			$bookmark['content'] = BookmarkPosts_Helper_Bookmarks::getContentData(array($bookmark['post_id']), $visitor);			
			$bookmark['user'] = $this->_getUserModel()->getUserById($bookmark['bookmark_user_id']);
		}
		
		foreach ($mostBookmarks  as &$bookmark)
		{
			$bookmark['content'] = BookmarkPosts_Helper_Bookmarks::getContentData(array($bookmark['post_id']), $visitor);			
			$bookmark['user'] = $this->_getUserModel()->getUserById($bookmark['bookmark_user_id']);
		}
    	
    		
    	
    	$viewParams = array(
				'mostBookmarks' => $mostBookmarks,
    			'latestBookmarks' => $latestBookmarks
    			);
    	
		return $this->responseView('XenForo_ViewPublic_Base', 'bookmark_posts_index', $viewParams);
		
	}
	
	
	/**
     * Edita um post favoritado
     * Edit a bookmarked post.
     * @since 1.1.0
     * @version 1.2.0
     */
	public function actionEdit()
	{	
		$this->_assertRegistrationRequired();
		
		$userId = $visitor = XenForo_Visitor::getUserId();
		
		$input = $this->getInput();
		$bookmarkId = $input->filterSingle('bookmark_id', XenForo_Input::UINT);
		
		$bookmarModel = $this->_getBookmarksModel();
		$bookmark = $bookmarModel->getBookmarkById($bookmarkId);
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($post, $thread) = $ftpHelper->assertPostValidAndViewable($bookmark['post_id']);
		
		if (!$bookmark)
		{
			return $this->responseNoPermission();
		}
		
		return $this->responseView('XenForo_ViewPublic_Base', 'bookmarks_posts_edit_bookmark', array(
		   	'bookmark' => $bookmark,
			'post' => $post,
			'thread' => $thread			
		));
	}
	
	
	
	/**
	 * 
	 * Save a bookmarked post which was edited
	 * @since 1.1.0
	 * @version 1.2.0
	 */
	public function actionSave()
	{		
		$this->_assertPostOnly();	
		$this->_assertRegistrationRequired();
		
		$userId = XenForo_Visitor::getUserId();		
		$input = $this->getInput();
		
		$bookmarkId = $this->_input->filterSingle('bookmark_id', XenForo_Input::UINT);
		
		$data = $this->_input->filter(array(			
			'bookmark_note' => XenForo_Input::STRING,
			'bookmark_state' => XenForo_Input::STRING,
			'bookmark_tag' => XenForo_Input::STRING
		));
		
		$dw = $this->_getBookmarkDataWriter();
		$dw->setExistingData($bookmarkId);
		$dw->bulkSet($data);
		$dw->save();
		
		return $this->responseRedirect(
				XenForo_ControllerResponse_Redirect::SUCCESS,
				XenForo_Link::buildPublicLink('account/bookmarks'),
				new XenForo_Phrase('bookmarks_posts_updated'),				
				array($data, $bookmarkId)
		);
			
	}
	
	
	/**
	 * 
	 * Show specific bookmarks by tag
	 * @since 1.1.0
	 * @version 1.2.0
	 */
	public function actionTags()
	{	
		$this->_assertRegistrationRequired();
		$visitor = XenForo_Visitor::getInstance()->toArray();
		
		$input = $this->getInput();
		$tag = $input->filterSingle('bookmark_tag', XenForo_Input::STRING);
		
		$bookmarkModel = $this->_getBookmarksModel();
					
		$bookmarks = $bookmarkModel->getBookmarkByTag($tag);				
		
		foreach ($bookmarks as &$bookmark)
		{
			$bookmark['content'] = BookmarkPosts_Helper_Bookmarks::getContentData(array($bookmark['post_id']), $visitor);			
			$bookmark['user'] = $this->_getUserModel()->getUserById($bookmark['bookmark_user_id']);
		}
		
		return $this->responseView('XenForo_ViewPublic_Base', 'bookmark_posts_bookmarks_tag', array(			
		   	'tagName' => $tag,
		
			'bookmarks' => $bookmarks,		
			'totalBookmarks' => count($bookmarks)
		));		
	}
	
	public function actionMyTags()
	{
		$this->_assertRegistrationRequired();
		
		$input = $this->getInput();
		$tag = $input->filterSingle('bookmark_tag', XenForo_Input::STRING);
		$userId = XenForo_Visitor::getInstance()->toArray();
		
		$bookmarkModel = $this->_getBookmarksModel();
		$bookmarks = $bookmarkModel->getBookmarkByTagAndUser($userId['user_id'], $tag);
		
		foreach ($bookmarks as &$bookmark)
		{
			$bookmark['content'] = BookmarkPosts_Helper_Bookmarks::getContentData(array($bookmark['post_id']), $userId);			
			$bookmark['user'] = $this->_getUserModel()->getUserById($bookmark['bookmark_user_id']);
		}
			
		//Get all tags of the user's bookmarks
		$bookmarkTags =  $bookmarkModel->getAllTagsFromUser($userId['user_id']);
			
		return $this->responseView('XenForo_ViewPublic_Base', 'bookmark_posts_each', array(			
		   	'tagName' => $tag,
			'bookmarksTags' => $bookmarkTags,
		
			'heading' => $tag,
		
			'bookmarks' => $bookmarks,		
			'totalBookmarks' => count($bookmarks)
		));

	}
	
	
	/**
	 * 
	 * Search throug all user's bookmarks.
	 * @since 1.1.0
	 */
	public function actionSearch()
	{
		// this action must be called via POST
		$this->_assertPostOnly();
		$this->_assertRegistrationRequired();
		
		$input = $this->getInput();		
		$search = $input->filterSingle('search', XenForo_Input::STRING);
		$tag = $input->filterSingle('tag', XenForo_Input::STRING);
		
		$userId = XenForo_Visitor::getUserId();
		if (!$this->_getBookmarksModel()->canBookmarkPost())
		{
			return $this->responseNoPermission();
		}

			
		$bookmarkModel = $this->_getBookmarksModel();
		
		//Get all tags of the user's bookmarks
		$bookmarkTags =  $bookmarkModel->getAllTagsFromUser($userId);
		
		$results = $bookmarkModel->searchBookmarksContent($userId, $search, $tag);
		$results = $bookmarkModel->prepareBookmark($results, 'post');
		
		return $this->responseView('XenForo_ViewPublic_Base', 'bookmark_posts_each', array(
		   	'bookmarks' => $results,
			
			'heading' => new XenForo_Phrase('bookmark_posts_search_result'),
			
			'bookmarksTags' => $bookmarkTags,
			'totalBookmarks' => count($results)
		));
	}
	
	
	
	/**
     * Publica ou deixa como privado um favorito. 
     * Make Public or make private a bookmark.
     * @since 1.1.0
     */
    public function actionPublic()
    {
    	// this action must be called via POST
		$this->_assertPostOnly();
		$this->_assertRegistrationRequired();
		
    	if (!$this->_getBookmarksModel()->canBookmarkPost())
		{
			return $this->responseNoPermission();
		}
			
		$bookmarkModel = $this->_getBookmarksModel();
		$dw = $this->_getBookmarkDataWriter();
		
		$userId = XenForo_Visitor::getUserId();
		$bookmarkId = $this->_input->filterSingle('bookmark_id', XenForo_Input::UINT);
		
		$existingBookmark = $bookmarkModel->getBookmarkById($bookmarkId);
		
		if ($existingBookmark['bookmark_state'] == 'public')
		{	
			$dw->changeState($bookmarkId, 'private');
			$alreadyPublicdBookmark = false;
		}
		else
		{
			$dw->changeState($bookmarkId, 'public');
			$alreadyPublicdBookmark = true;
		}
		
		$viewParams = array(					
					'alreadyPublicBookmark' => $alreadyPublicdBookmark,
					'id' => $bookmarkId			
			);
		
		return $this->responseView('BookmarkPosts_ViewPublic_Account_PublicConfirmed', 'account_bookmarks', $viewParams
		);	
    }
    
    /**
     * 
     * View a bookmark
     * @since 1.2.0
     * @version 1.2.0
     */
    public function actionView()
    {
    	$this->_assertRegistrationRequired();
		
    	if (!$this->_getBookmarksModel()->canViewBookmarkedPost())
		{
			return $this->responseNoPermission();
		}
    	    	
    	$visitor = XenForo_Visitor::getInstance()->toArray();
    	
    	$input = $this->getInput();
    	$bookmarkId = $input->filterSingle('bookmark_id', XenForo_Input::UINT);
    	
    	$bookmarkModel = $this->_getBookmarksModel();		
		$bookmark = $bookmarkModel->getBookmarkById($bookmarkId);
		
    	if (!$bookmark)
		{
			return $this->responseError(
					new XenForo_Phrase('bookmark_posts_does_not_exists')
			);
		}
		
		//$bookmark = $bookmarkModel->prepareBookmarkForView($bookmark); DEIXAR?
		
		$bookmark['content'] = BookmarkPosts_Helper_Bookmarks::getContentData(array($bookmark['post_id']),$visitor);
		
		if (!$bookmark['content'])
		{
			return $this->responseNoPermission();
		}
		
		$bookmark['user'] = $this->_getUserModel()->getUserById($bookmark['bookmark_user_id']);
		
		$post['alreadyBookmarkedPosts'] = $bookmarkModel->alreadyBookmarkedPost($bookmark['content']);
		$post['post_id'] = $bookmark['post_id'];
		
		$latestBookmarks = $bookmarkModel->getLatestBookmark('public', $this->_getOptions()->bookmark_posts_option_latest_number);
    	foreach ($latestBookmarks as &$bookmarks)
		{
			$bookmarks['content'] = BookmarkPosts_Helper_Bookmarks::getContentData(array($bookmark['post_id']), $visitor);			
			$bookmarks['user'] = $this->_getUserModel()->getUserById($bookmark['bookmark_user_id']);
		}
		
		
		//Is this public?
		if ($bookmarkModel->isBookmarkPublic($bookmark) || $visitor['user_id'] == $bookmark['bookmark_user_id'])
		{	
			$bookmarkNotes = $bookmarkModel->getAllNotesByPostId($bookmark['post_id'], $bookmark['bookmark_user_id']);
			
			
			$viewParams = array(
					'bookmark' => $bookmark,
					'latestBookmarks' => $latestBookmarks,
					'bookmarkNotes' => $bookmarkNotes,
						
					'post' => $post	
			);
			
			
			
			return $this->responseView('BookmarkPosts_ViewPublic_Bookmarks_ItemView', 'bookmark_posts_bookmark_view', $viewParams);			
		}
		else
		{
			return $this->responseError(
					new XenForo_Phrase('bookmark_posts_bookmark_not_public')
				);
		}
    }
    
    
	/**
	 * 
	 * Unbookmark a post bookmarked
	 * @since 1.1.0 
	 */
	public function actionUnbookmark()
	{
		// this action must be called via POST
		$this->_assertPostOnly();
		$this->_assertRegistrationRequired();
		
		if (!$this->_getBookmarksModel()->canBookmarkPost())
		{
			return $this->responseNoPermission();
		}
		
		$visitor = XenForo_Visitor::getInstance();		
		$bookmarkId = $this->_input->filterSingle('bookmark_id', XenForo_Input::UINT);
		
		$bookmarkModel = $this->_getBookmarksModel();
		$dw = $this->_getBookmarkDataWriter();
		
		if ($this->_request->isPost())
		{
			$existingBookmark = $bookmarkModel->getBookmarkById($bookmarkId);					
			if ($existingBookmark)
			{	
				$dw->unBookmarkPost($existingBookmark);				
				$totalBookmarks = $bookmarkModel->countBookmarksForContentUser($visitor['user_id']);												
			}
		}
		
		$post['alreadyBookmarkedPosts'] = ($existingBookmark ? false : true);			
		$viewParams = array(					
					'post' => $post, 		
					'deletedId' => $bookmarkId,
					'totalBookmarks' => $totalBookmarks
				);
			
		return $this->responseView('BookmarkPosts_ViewPublic_Bookmarks_BookmarkConfirmed', '', $viewParams);
		
	}
	
	
	
	
	
	/**
	 * @return BookmarkPosts_Model_BookmarkPosts
	 * @since 1.1.0
	 */
	protected function _getBookmarksModel()
	{
		return $this->getModelFromCache('BookmarkPosts_Model_BookmarkPosts');
	}
	
	/**
	 * @return XenForo_Model_Post
	 * @since 1.2.0
	 */
	protected function _getPostModel()
	{
		return $this->getModelFromCache('XenForo_Model_Post');
	}
	
	/**
	 * @return Options
	 * @since 1.2.0 
	 */
	protected function _getOptions()
	{
		return XenForo_Application::get('options');
	}
	
	/**
	 * @return XenForo_Model_User
	 * @since 1.2.0
	 */
	protected function _getUserModel()
	{
		return $this->getModelFromCache('XenForo_Model_User');
	}
	
	protected function _getBookmarkDataWriter()
	{
		return XenForo_DataWriter::create('BookmarkPosts_DataWriter_Bookmark');
	}
}