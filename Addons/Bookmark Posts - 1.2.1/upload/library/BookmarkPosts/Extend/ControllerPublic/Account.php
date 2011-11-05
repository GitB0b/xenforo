<?php
/**
 * 
 * @see XenForo_ControllerPublic_Account
 * @author Fuhrmann
 * @since 1.0.0
 *
 */
class BookmarkPosts_Extend_ControllerPublic_Account extends XFCP_BookmarkPosts_Extend_ControllerPublic_Account
{
	/**
	 * Mostra todos os favoritos da conta do usuário. Privados ou compartilhados.
	 * Show all bookmarks from the user account. Private and public.
	 * @since 1.0.0
	 */
	public function actionBookmarks()
    {   
    	if (!$this->_getBookmarksModel()->canBookmarkPost())
		{
			return $this->responseNoPermission();
		}
				
		$bookmarkModel = $this->_getBookmarksModel();
						
		$userId = XenForo_Visitor::getInstance()->toArray();
		
		$page = $this->_input->filterSingle('page', XenForo_Input::UINT);
		$perPage = XenForo_Application::get('options')->bookmark_posts_option_perPage;
		
		//Get all posts bookmarked
		$bookmarkPosts = $bookmarkModel->getBookmarksFromUser($userId['user_id'], array(
			'page' => $page,
			'perPage' => $perPage
		));
		
    	foreach ($bookmarkPosts as &$bookmark)
		{
			$bookmark['content'] = BookmarkPosts_Helper_Bookmarks::getContentData(array($bookmark['post_id']), $userId);			
			$bookmark['user'] = $this->_getUserModel()->getUserById($bookmark['bookmark_user_id']);
		}
		
		
		//Get all tags of the user's bookmarks
		$bookmarkTags =  $bookmarkModel->getAllTagsFromUser($userId['user_id']);
		
		$viewParams = array(
			'bookmarks' => $bookmarkPosts,			
			'bookmarksTags' => $bookmarkTags,
		
			'heading' => new XenForo_Phrase('bookmark_posts_all_bookmarks'),		 
			
			'page' => $page,
			'totalBookmarks' => $bookmarkModel->countBookmarksForContentUser($userId['user_id']),
			'bookmarksPerPage' => $perPage,
			'userId' => $userId['user_id']
		);		
		
		
		return $this->_getWrapper(
			'alerts', 'bookmarks',
			$this->responseView(
				'BookmarkPosts_ViewPublic_Account_Bookmarks',
				'account_bookmarks',
				$viewParams
			)
		);		
    }
	
    
      
    
	/**
	 * @return BookmarkPosts_Model_BookmarkPosts
	 * @since 1.0.0
	 */
	protected function _getBookmarksModel()
	{
		return $this->getModelFromCache('BookmarkPosts_Model_BookmarkPosts');
	}  
} 