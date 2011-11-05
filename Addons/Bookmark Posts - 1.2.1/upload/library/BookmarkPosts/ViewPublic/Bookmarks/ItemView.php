<?php
class BookmarkPosts_ViewPublic_Bookmarks_ItemView extends XenForo_ViewPublic_Base
{
	public function renderHtml()
	{	
		$bbCodeParser = new XenForo_BbCode_Parser(XenForo_BbCode_Formatter_Base::create('Base', array('view' => $this)));
		$content = $this->_params['bookmark']['content'];		
		$this->_params['bookmark']['content']['messageHtml'] = new XenForo_BbCode_TextWrapper($content['message'], $bbCodeParser);		
	}
}