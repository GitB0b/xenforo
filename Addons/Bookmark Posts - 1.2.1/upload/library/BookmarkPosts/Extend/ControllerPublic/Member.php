<?php
/**
 * 
 * @see XenForo_ControllerPublic_Member
 * @author Fuhrmann
 * @since 1.0.0
 */ 
class BookmarkPosts_Extend_ControllerPublic_Member extends XFCP_BookmarkPosts_Extend_ControllerPublic_Member
{	
	
	/**
	 * 
	 * Show all the public bookmarks of user
	 * @since 1.0.0
	 * @version 1.1.0
	 */
    public function actionBookmarks()
    {
    	if (!$this->_getBookmarksModel()->canBookmarkPost())
		{
			return $this->responseNoPermission();
		}
		$visitor = XenForo_Visitor::getInstance()->toArray();
		
        $userId = $this->_input->filterSingle('user_id', XenForo_Input::UINT);
		$user = $this->getHelper('UserProfile')->assertUserProfileValidAndViewable($userId);
		
		$page = $this->_input->filterSingle('page', XenForo_Input::UINT);
		$page == 0 ? $page = 1 : '';
		
		$perPage = XenForo_Application::get('options')->bookmark_posts_option_perPage_profile;
		
		
		$bookmarkModel = $this->_getBookmarksModel();
		$publicBookmarks = $bookmarkModel->getBookmarksFromUser($userId, array(
			'page' => $page,
			'perPage' => $perPage
		), 'public'); //only public bookmarks
		
		foreach ($publicBookmarks as &$bookmark)
		{
			$bookmark['content'] = BookmarkPosts_Helper_Bookmarks::getContentData(array($bookmark['post_id']), $visitor);			
			$bookmark['user'] = $this->_getUserModel()->getUserById($bookmark['bookmark_user_id']);			
		}
		
		$viewParams = array(
			'user' => $user,
		
			'page' => $page + 1,
			'bookmarksPerPage' => $perPage,
			'publicBookmarks' => $publicBookmarks,
			'totalBookmarks' => $bookmarkModel->countBookmarksPublicdbyUser($userId)
		);
		
        return $this->responseView(
            'XenForo_ViewPublic_Base', 'bookmark_posts_member_bookmarks',
            $viewParams
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