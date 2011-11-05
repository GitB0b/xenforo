<?php
/**
 * 
 * Listener
 * @author Owner
 * @since 1.0.0
 *
 */
class BookmarkPosts_Listener
{
	
	/**	  
	 * Preload a custom template	 
	 * @since 1.0.0
	 */
	public static function template_create($templateName, array &$params, XenForo_Template_Abstract $template)
    {
    	switch ($templateName) {
    		case 'thread_view':
    			$template->preloadTemplate('bookmarks_posts_link');
    			break;
    		case 'member_view':
    			$template->preloadTemplate('bookmarks_posts_profile_tab');
    			$template->preloadTemplate('bookmarks_posts_profile_tab_content');
    			break;
    		case 'account_wrapper':
    			$template->preloadTemplate('account_bookmarks');
    			$template->preloadTemplate('account_bookmarks_link');
    		
    	}
    }
	
	
	/**      
     * Use the hooks to insert template in contents     
     * @since 1.0.0
     */
	public static function template_hook ($hookName, &$contents, array $hookParams, XenForo_Template_Abstract $template)
	{
		//http://xenforo.com/community/threads/looking-for-the-user-id-for-custom-template.19960/#post-255620
		$viewParams = array_merge($template->getParams(), $hookParams); // then you have access
		$bookmarkModel = XenForo_Model::create('BookmarkPosts_Model_BookmarkPosts');
		$options = XenForo_Application::get('options');
		
		switch ($hookName) {						
			case 'post_public_controls':
				if ($bookmarkModel->canBookmarkPost()) //User can bookmark post?
				{
					if ($viewParams['post']['alreadyBookmarkedPosts']) //Post already bookmarked?
					{	
						$ourTemplate = $template->create('bookmarks_posts_link_unbookmark_text', $viewParams);						
					}
					else
					{
						$ourTemplate = $template->create('bookmarks_posts_link_bookmark_text', $viewParams);	
					}				
					$rendered = $ourTemplate->render();
					$contents .= $rendered;
				}
				break;
			case 'member_view_tabs_heading':
				$ourTemplate = $template->create('bookmarks_posts_profile_tab', $viewParams);
				$rendered = $ourTemplate->render();
				$contents .= $rendered;
				break;
			case 'member_view_tabs_content':
				$ourTemplate = $template->create('bookmarks_posts_profile_tab_content', $viewParams);
				$rendered = $ourTemplate->render();
				$contents .= $rendered;
				break;
			case 'account_wrapper_sidebar_your_account':				
				$ourTemplate = $template->create('account_bookmarks_link', $viewParams);
				$rendered = $ourTemplate->render();
				$contents .= $rendered;
				break;
			case 'navigation_visitor_tab_links2':
                $ourTemplate = $template->create('bookmarks_posts_navigation_visitor_tab', $viewParams);
                $rendered = $ourTemplate->render();
                //$contents = str_replace('</ul>', $rendered . '</ul>', $contents);
                $contents .= $rendered;
                break;
		}
	}
	
	/**
     * Extend some class       
     *@since 1.0.0 
     */
    public static function extend($class, array &$extend)
    {    	
    	switch ($class) {
    		case 'XenForo_ControllerPublic_Member':
    			$extend[] = 'BookmarkPosts_Extend_ControllerPublic_Member';
    			break;    		
    		case 'XenForo_ControllerPublic_Post':
    			$extend[] = 'BookmarkPosts_Extend_ControllerPublic_Post';
    			break;
    		case 'XenForo_ControllerPublic_Thread':
    			$extend[] = 'BookmarkPosts_Extend_ControllerPublic_Thread';
    			break;	
    		case 'XenForo_ControllerPublic_Account':
    			$extend[] = 'BookmarkPosts_Extend_ControllerPublic_Account';
    			break;
    		case 'XenForo_Model_Post':
    			$extend[] = 'BookmarkPosts_Extend_Model_Post';
    			break;
    		case 'XenForo_DataWriter_User':
    			$extend[] = 'BookmarkPosts_Extend_DataWriter_User';
    			break;
    	}
    }
    
    
    public static function init (XenForo_FrontController $fc, XenForo_ControllerResponse_Abstract &$controllerResponse, XenForo_ViewRenderer_Abstract &$viewRenderer, array &$containerParams)
    {
    	$params = array(
					'canBookmarkPost' => XenForo_Model::create('BookmarkPosts_Model_BookmarkPosts')->canBookmarkPost()
			);
			
    	if ($controllerResponse instanceof XenForo_ControllerResponse_View)
		{
			$controllerResponse->params = array_merge($controllerResponse->params, $params);
			$containerParams = array_merge($containerParams, $params);						        
		}
		
		
    }
    
	
    	
}