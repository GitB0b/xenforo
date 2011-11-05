<?php
/**
 * 
 * 
 * @author Fuhrmann
 * @since 1.1.0
 *
 */

class BookmarkPosts_Route_Prefix_Bookmarks implements XenForo_Route_Interface
{
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router)
	{	
		$action = $router->resolveActionWithIntegerOrStringParam($routePath, $request, 'bookmark_id', 'bookmark_tag');
		return $router->getRouteMatch('BookmarkPosts_ControllerPublic_Bookmarks', $action, 'bookmarks');
	}

	/**	 
	 * @see XenForo_Route_BuilderInterface
	 */
	public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
	{	
		if ($action == 'tags' || $action == 'mytags')
		{
			return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, 'bookmark_tag');
		
		}
		else
		{
			return XenForo_Link::buildBasicLinkWithIntegerParam($outputPrefix, $action, $extension, $data, 'bookmark_id');	
		}
	}		
	
}

