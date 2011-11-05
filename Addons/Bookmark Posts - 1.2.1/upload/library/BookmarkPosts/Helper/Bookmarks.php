<?php

class BookmarkPosts_Helper_Bookmarks
{
	/**
	 * The current browsing user.
	 *
	 * @var XenForo_Visitor
	 */
	protected $_visitor;

	/**
	 * Additional constructor setup behavior.
	 */
	protected function _constructSetup()
	{
		$this->_visitor = XenForo_Visitor::getInstance();
	}
	
	static public function getPostInfo($postId, array $fields = array())
	{
		$postModel = XenForo_Model::create('XenForo_Model_Post');
		$post = $postModel->getPostById($postId);
		if (!$post)
		{
			throw $this->_controller->responseException(
				$this->_controller->responseError(new XenForo_Phrase('requested_post_not_found'), 404)
			);
		}
		return $post;
	}
	
	
	static public function getUserPostInfo($userId, array $fields = array ())
	{
		
		$userModel = XenForo_Model::create('XenForo_Model_User');
		$data = $userModel->getUserById($userId);
		if (!$data)
		{
			throw $this->_controller->responseException(
				$this->_controller->responseError(new XenForo_Phrase('requested_post_not_found'), 404)
			);	
		}		
		return $data;
	}
	
	
	static public function getThreadInfo($threadId, array $fields = array ())
	{	
		$threadModel = XenForo_Model::create('XenForo_Model_Thread');
		$data = $threadModel->getThreadById($threadId);
		if (!$data)
		{
			throw $this->_controller->responseException(
				$this->_controller->responseError(new XenForo_Phrase('requested_post_not_found'), 404)
			);	
		}
		return $data;
	}
	
	static public function getForumInfo($forumId, array $fields = array ())
	{	
		$forumModel = XenForo_Model::create('XenForo_Model_Forum');
		$data = $forumModel->getForumById($forumId);
		if (!$data)
		{
			throw $this->_controller->responseException(
				$this->_controller->responseError(new XenForo_Phrase('requested_post_not_found'), 404)
			);	
		}

		return $data;
	}
	
	

	static public function getUserBookmarkInfo($userId, array $fields = array ())
	{		
		$userModel = XenForo_Model::create('XenForo_Model_User');
		$data = $userModel->getUserById($userId);			
		if (!$data)
		{
			throw $this->_controller->responseException(
				$this->_controller->responseError(new XenForo_Phrase('requested_post_not_found'), 404)
			);	
		}
				
		return $data;
	}
	
	
	
	
	
	static public function getPostsInfo(array $posts, array $fields = array())
	{	
		foreach ($posts as &$post)
		{	
				$postModel = XenForo_Model::create('XenForo_Model_Post');			
				$data = $postModel->getPostById($post['post_id']);						
				if (!$data)
				{
					throw $this->_controller->responseException(
						$this->_controller->responseError(new XenForo_Phrase('requested_post_not_found'), 404)
					);	
				}			
				if (!empty($fields))
				{	
					$post['post'] = XenForo_Application::arrayFilterKeys($data, $fields);
				}
		}
		
		return $posts;
	}
	
	static public function getUsersPostInfo(array $posts, array $fields = array ())
	{
		foreach ($posts as &$post)
		{
			$userModel = XenForo_Model::create('XenForo_Model_User');
			$data = $userModel->getUserById($post['post']['user_id']);
			if (!$data)
			{
				throw $this->_controller->responseException(
					$this->_controller->responseError(new XenForo_Phrase('requested_post_not_found'), 404)
				);	
			}
			if (!empty($fields))
			{
				$post['user'] = XenForo_Application::arrayFilterKeys($data, $fields);
			}
				
		}

		return $posts;
	}
	
	static public function getUsersBookmarkInfo(array $posts, array $fields = array ())
	{
		foreach ($posts as &$post)
		{
			$userModel = XenForo_Model::create('XenForo_Model_User');
			$data = $userModel->getUserById($post['bookmark_user_id']);			
			if (!$data)
			{
				throw $this->_controller->responseException(
					$this->_controller->responseError(new XenForo_Phrase('requested_post_not_found'), 404)
				);	
			}
			if (!empty($fields))
			{
				$post['user'] = XenForo_Application::arrayFilterKeys($data, $fields);
			}
				
		}

		return $posts;
	}
	
	static public function getThreadsInfo(array $threads, array $fields = array ())
	{
		foreach ($threads as &$thread)
		{
			$threadModel = XenForo_Model::create('XenForo_Model_Thread');
			$data = $threadModel->getThreadById($thread['post']['thread_id']);
			if (!$data)
			{
				throw $this->_controller->responseException(
					$this->_controller->responseError(new XenForo_Phrase('requested_post_not_found'), 404)
				);	
			}
			if (!empty($fields))
			{
				$thread['thread'] = XenForo_Application::arrayFilterKeys($data, $fields);
			}	
		}		
		return $threads;
	}
	
	static public function getForumsInfo(array $forums, array $fields = array ())
	{
		foreach ($forums as &$forum)
		{
			$forumModel = XenForo_Model::create('XenForo_Model_Forum');
			$data = $forumModel->getForumById($forum['thread']['node_id']);
			if (!$data)
			{
				throw $this->_controller->responseException(
					$this->_controller->responseError(new XenForo_Phrase('requested_post_not_found'), 404)
				);	
			}
			if (!empty($fields))
			{
				$forum['forum'] = XenForo_Application::arrayFilterKeys($data, $fields);
			}	
		}

		return $forums;
	}
	
	
	
	
	/**
	 * Gets content data (if viewable).
	 * @see Postit_Handler_Abstract::getContentData()
	 */
	static public function getContentData(array $postIds, array $viewingUser)
	{
		$postModel = XenForo_Model::create('XenForo_Model_Post');
		$posts = $postModel->getPostsByIds($postIds, array(
			'join' => XenForo_Model_Post::FETCH_THREAD | XenForo_Model_Post::FETCH_USER, XenForo_Model_Post::FETCH_FORUM,
			'permissionCombinationId' => $viewingUser['permission_combination_id']
		));
		$posts = $postModel->unserializePermissionsInList($posts, 'node_permission_cache');

		$output = array();
		foreach ($posts AS $postId => $post)
		{
			if (!$postModel->canViewPostAndContainer(
				$post, $post, $post, $null, $post['permissions'], $viewingUser
			))
			{
				continue;
			}		
			$post['content_user'] = XenForo_Application::arrayFilterKeys($post, array(
					'user_id',
					'username',
					'gender',
					'gravatar',
					'avatar_date'
					)
			);			
			$output = $post;
		}		
		return $output;
	}
	
	
	
}