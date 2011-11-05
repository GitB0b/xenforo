<?php
/**
 * 
 * @see XenForo_ControllerPublic_Thread 
 * @author Fuhrmann
 *
 */
class BookmarkPosts_Extend_ControllerPublic_Thread extends XFCP_BookmarkPosts_Extend_ControllerPublic_Thread
{
	/**
	 * Override the actionIndex() in XenForo_ControllerPublic_Thread
	 * @since 1.0.0
	 */
	public function actionIndex()
	{		
		$response = parent::actionIndex();
		if (isset($response->params))
		{			
			$viewParams = $response->params;
			
				foreach ($viewParams['posts'] AS &$post)
				{			
					if ($post['bookmark_posts_user_id'])
					{
						$post['alreadyBookmarkedPosts'] = true;
					}
					else
					{
						$post['alreadyBookmarkedPosts'] = false;	
					}
				}
			
			
			$response->params = $viewParams;
		}
		return $response;
		
	}
}