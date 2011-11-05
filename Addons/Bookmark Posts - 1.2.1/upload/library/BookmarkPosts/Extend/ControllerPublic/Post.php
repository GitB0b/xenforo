<?php 

class BookmarkPosts_Extend_ControllerPublic_Post extends XFCP_BookmarkPosts_Extend_ControllerPublic_Post
{
	
	/**
	 * Bookmark a post.
	 * @since 1.2.0
	 */
	public function actionBookmark()
	{
		$this->_assertRegistrationRequired();
		
		if (!$this->_getBookmarksModel()->canBookmarkPost())
		{
			return $this->responseNoPermission();
		}		
		
		$visitorId = XenForo_Visitor::getUserId();
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($post) = $ftpHelper->assertPostValidAndViewable($postId);
		
		
		$bookmarkModel = $this->_getBookmarksModel();
		$dw = $this->_getBookmarkDataWriter();
		
		$existingBookmark = $bookmarkModel->getBookmarkByPostIdAndUser($postId, $visitorId);
		
		if ($this->_request->isPost())
		{
			if ($existingBookmark){
				$dw->unBookmarkPost($existingBookmark);
			}
			else
			{
				$dw->bookmarkPost($post, $visitorId);
			}
		}
		
		$post['alreadyBookmarkedPosts'] = ($existingBookmark ? false : true);

		$viewParams = array(					
				'post' => $post 				
		);
	
			
		return $this->responseView('BookmarkPosts_ViewPublic_Post_BookmarkConfirmed', '', $viewParams);
			
	}
	
	/**
	 * Unbookmark a post
	 * @since 1.2.0
	 */
	public function actionUnBookmark()
	{
		$this->_assertRegistrationRequired();
		
		if (!$this->_getBookmarksModel()->canBookmarkPost())
		{
			return $this->responseNoPermission();
		}
		$visitorId = XenForo_Visitor::getUserId();
		$postId = $this->_input->filterSingle('post_id', XenForo_Input::UINT);
		
		$ftpHelper = $this->getHelper('ForumThreadPost');
		list($post) = $ftpHelper->assertPostValidAndViewable($postId);
		
		$bookmarkModel = $this->_getBookmarksModel();
		$dw = $this->_getBookmarkDataWriter();
		
		$existingBookmark = $bookmarkModel->getBookmarkByPostIdAndUser($postId, $visitorId);
		
		if ($existingBookmark){			
				$dw->unBookmarkPost($existingBookmark);
		}
		
		$post['alreadyBookmarkedPosts'] = ($existingBookmark ? false : true);

		$viewParams = array(					
				'post' => $post 				
		);
	
			
		return $this->responseView('BookmarkPosts_ViewPublic_Post_BookmarkConfirmed', '', $viewParams);
		
	}
	
	
	/**
	 * @return BookmarkPosts_Model_BookmarkPosts
	 * @since 1.2.0
	 */
	protected function _getBookmarksModel()
	{
		return $this->getModelFromCache('BookmarkPosts_Model_BookmarkPosts');
	}
	
	protected function _getBookmarkDataWriter()
	{
		return XenForo_DataWriter::create('BookmarkPosts_DataWriter_Bookmark');
	}
	
	
} 
