<?php
class BookmarkPosts_Extend_DataWriter_User extends XFCP_BookmarkPosts_Extend_DataWriter_User
{
	/**
	 * Delete all bookmarks from user deleted.
	 * @since 1.1.0
	 * @version 1.1.0
	 */
	protected function _postDelete()
	{
		parent::_postDelete();

		$userId = $this->get('user_id');
		$bookmarkModel = $this->getModelFromCache('BookmarkPosts_Model_BookmarkPosts');
		$bookmarkModel->deleteBookmarksFromUser($userId);
	}
}