<?php
/**
 * @author Fuhrmann
 * @since 1.0.0
 *
 */

class BookmarkPosts_ViewPublic_Post_BookmarkConfirmed extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		$output = array('templateHtml' => '', 'js' => '', 'css' => '');
        
		$output += self::getBookmarkViewParams($this->_params['post']);
		
		if (isset($this->_params['post']['post_id']))
		{
			$output['id'] = $this->_params['post']['post_id'];
		}
		 
		if (isset($this->_params['deletedId']))
		{
			$output['deletedId'] = $this->_params['deletedId']; //When unbookmarking	
		}
		
		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
	}
	
	public static function getBookmarkViewParams($post)
	{
		$output = array();
		
		$options = XenForo_Application::get('options');
		
		
		if ($post['alreadyBookmarkedPosts'])
		{	
			$output['term'] = new XenForo_Phrase('bookmark_posts_action_unbookmark_text');
			$output['addClass'] = 'unbookmark';
			$output['removeClass'] = 'bookmark';
			$output['link'] = 'index.php?posts/' . $post['post_id'] . '/unbookmark/';
						
		}
		else
		{
			$output['term'] = new XenForo_Phrase('bookmark_posts_action_bookmark_text');
			$output['addClass'] = 'bookmark';
			$output['removeClass'] = 'unbookmark';			
			$output['link'] = 'index.php?posts/' . $post['post_id'] . '/bookmark/';
		}
		return $output;
	}
	
	
}