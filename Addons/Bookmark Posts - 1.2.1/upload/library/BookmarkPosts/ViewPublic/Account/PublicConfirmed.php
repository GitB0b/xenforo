<?php
/**
 * 
 * @since 1.0.0
 * @author Fuhrmann
 *
 */
class BookmarkPosts_ViewPublic_Account_PublicConfirmed extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		$output = array('templateHtml' => '', 'js' => '', 'css' => '');
        
		$output += self::getBookmarkViewParams($this->_params['alreadyPublicBookmark']);
		
		if (isset($this->_params['id']))
		{
			$output['id'] = $this->_params['id'];	
		}
		
		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
	}
	
	public static function getBookmarkViewParams($alreadyPublicBookmark)
	{
		$output = array();

		$options = XenForo_Application::get('options');
		
		if ($alreadyPublicBookmark)
		{	
			$output['term'] = new XenForo_Phrase('bookmarks_posts_unshare_text');
			$output['addClass'] = 'public';
			$output['removeClass'] = 'private';						
		}
		else
		{	
			$output['term'] = new XenForo_Phrase('bookmarks_posts_share_text');
			$output['addClass'] = 'private';
			$output['removeClass'] = 'public';			
		}
		return $output;
	}
	
	
}