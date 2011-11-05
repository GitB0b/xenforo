<?php
/**
 * @author Fuhrmann
 * @since 1.2.0
 *
 */

class BookmarkPosts_ViewPublic_Bookmarks_BookmarkConfirmed extends XenForo_ViewPublic_Base
{
	public function renderJson()
	{
		$output = array('templateHtml' => '', 'js' => '', 'css' => '');
		 
		if (isset($this->_params['deletedId']))
		{
			$output['deletedId'] = $this->_params['deletedId']; //When unbookmarking	
		}
		
		return XenForo_ViewRenderer_Json::jsonEncodeForOutput($output);
	}
}