<?php
/**
 * 
 * @see XenForo_Model_Post
 * @author Fuhrmann
 * @since 1.0.0
 *
 */
class BookmarkPosts_Extend_Model_Post extends XFCP_BookmarkPosts_Extend_Model_Post
{
	
	/**
	 * 
	 * Faz com que seja pesquisado na tabela dos bookmarks.
	 * Join the bookmark table with posts. One query for all posts.
	 * @param array $fetchOptions
	 * @since 1.0.0
	 */
	public function preparePostJoinOptions(array $fetchOptions)
    {
        $array = parent::preparePostJoinOptions($fetchOptions);
        
		$visitor = XenForo_Visitor::getUserId();
		
        if(!empty($fetchOptions['join']))
        {
            if($fetchOptions['join'] & self::FETCH_USER)
            {
                $array['selectFields'] .= ',
					bookmark_posts.bookmark_user_id AS bookmark_posts_user_id,
					bookmark_posts.post_id AS bookmark_posts_post_id';
                $array['joinTables'] .= '
					LEFT JOIN xf_bookmark_posts AS bookmark_posts ON
						(bookmark_posts.post_id = post.post_id AND bookmark_posts.bookmark_user_id = ' . $visitor . ')';
            }
        }

        return $array;
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