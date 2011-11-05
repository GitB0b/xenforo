/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
	
	// *********************************************************************
	/**
	 * @param jQuery $('form.BookmarkPosts')
	 */
	XenForo.BookmarkPosts = function($form) { this.__construct($form); };
	XenForo.BookmarkPosts.prototype =
	{
		__construct: function($form)
		{
			this.$form = $form;
			
			//Unbookmark a post
			$('a.UnBookmarkLink').live('click', $.context(this, 'unbookmark')); 
			this.$link = $('a.UnBookmarkLink');
			
			//Filter bookmarks by tag
			$('a.TagLink').live('click', $.context(this, 'showAllBookmarksWithTags')); 			
			
			//Make public/private a bookmark
			$('a.PublicLink').live('click', $.context(this, 'publicPrivateBookmark'));
		},
		
		unbookmark: function(e) //Handles a unbookmark action
		{
			e.preventDefault();
			
			var $url = $('a[id="' + e.target.id + '"]').attr('href');
			XenForo.ajax($url, {}, function(ajaxData, textStatus) 
			{				
				if (XenForo.hasResponseError(ajaxData))
				{					
					return false;
				}
				
				var $total = $('#totalBookmarks').html().replace(/[^0-9 ]/g,'');
				var $unBookmarkLink = $('a.UnBookmarkLink');
				var $target = $($unBookmarkLink.data('target'));
				$total = parseInt($total - 1);				
				
				if ($(document).find('.titleText').size() == 1 && $(document).find('.PageNav').size() == 0)
				{				
					$($target).xfFadeDown(50);
				}
				
				$('span[id="totalBookmarks"]').html('('+ $total +')'); 
				$('li[id~="bookmark-'+ajaxData.deletedId+'"]').xfRemove('xfSlideUp');				
				
			});
		},
		showAllBookmarksWithTags: function(e) //Show all bookmarks with the same tag
		{
			e.preventDefault();
			
			XenForo.ajax(e.target, {}, function(ajaxData, textStatus) 
			{				
				if (XenForo.hasResponseError(ajaxData))
				{					
					return false;
				}
				new XenForo.ExtLoader(ajaxData, function()
				{	
					var $tagLink = $('a.TagLink');
					var $container = $($tagLink.data('container'));				
					$container.empty().append(ajaxData.templateHtml);
				});
			});
		},
		
		publicPrivateBookmark: function(e)
		{
			e.preventDefault();
			
			var $url = $('a[id="' + e.target.id + '"]').attr('href');
			
			XenForo.ajax($url, {}, function(ajaxData, textStatus) 
			{				
				if (XenForo.hasResponseError(ajaxData))
				{					
					return false;
				}
				
				if (ajaxData.term) // term = Public / Private
				{						
					$('a[id~="Status-'+ajaxData.id+'"] span.PublicLabel').html(ajaxData.term);				
					$('a[id~="Status-'+ajaxData.id+'"]').removeClass(ajaxData.removeClass).addClass(ajaxData.addClass);
				}				
			});
		}
	};

	// *********************************************************************
	XenForo.register('form.BookmarkPosts', 'XenForo.BookmarkPosts');	
	
	
	/**
	 * 
	 *
	 * @param jQuery a.RemoveFilter
	 */	
	XenForo.RemoveFilter = function($link)
	{
		$link.click(function(e)
		{
			e.preventDefault();
			var $link = $(this);
			
			XenForo.ajax(this.href, {}, function(ajaxData, textStatus) 
			{
				if (XenForo.hasResponseError(ajaxData))
				{					
					return false;
				}
				
				new XenForo.ExtLoader(ajaxData, function()
				{						
					var $container = $($link.data('container'));
					$container.empty().append(ajaxData.templateHtml);					
				});				
			});
		});			
	};
	// *********************************************************************
	XenForo.register('a.RemoveFilter', 'XenForo.RemoveFilter');

	
	/**
	 * Handles a bookmark / unbookmar link being clicked in the thread view.
	 *
	 * @param jQuery a.BookmarkLink
	 */
	XenForo.BookmarkLink = function($link)
	{
		$link.click(function(e)  
		{
			e.preventDefault();

			var $link = $(this);

			XenForo.ajax(this.href, {}, function(ajaxData, textStatus)
			{
				if (XenForo.hasResponseError(ajaxData))
				{
					return false;
				}

				$link.stop(true, true);
				
				if (ajaxData.term) // term = Bookmark / Unbookmark
				{	
					$('a[id~="bookmark-'+ajaxData.id+'"] span.BookmarkLabel').html(ajaxData.term);					
					$('a[id~="bookmark-'+ajaxData.id+'"]').removeClass(ajaxData.removeClass).addClass(ajaxData.addClass);
					//$('a[id~="bookmark-'+ajaxData.id+'"]').attr('title',XenForo.phrases.bookmark_posts_bookmark_title);
				}
				
				$link.attr("href", ajaxData.link);
				
				if ($($link.data('container')))
				{						
					$($link.data('container')).xfRemove('xfSlideUp');					
				}
				
				if ($(document).find('.titleText').size() == 1 && $(document).find('.PageNav').size() == 0)
				{
					$($link.data('target')).xfFadeDown(50);
				}
			});
		});
	};
	
	// *********************************************************************
	XenForo.register('a.BookmarkLink', 'XenForo.BookmarkLink');
	
	/**
	 * Handles the show Older in the profile tab 
	 *
	 * @param jQuery a.ShowOlderBookmarks
	 */
	XenForo.ShowOlderBookmarks = function($link)
	{
		$link.click(function(e)
		{
			e.preventDefault();

			var $link = $(this);

			XenForo.ajax(this.href, {}, function(ajaxData, textStatus)
			{
				if (XenForo.hasResponseError(ajaxData))
				{
					return false;
				}

				$link.stop(true, true);
				
				$($link.data('container')).xfFadeUp(50);
				
				$(ajaxData.templateHtml).xfInsert('insertBefore', $link.closest('.BookmarkEnd'), 'xfSlideDown', XenForo.speed.slow);
			});
		});
	};
	
	// *********************************************************************
	XenForo.register('a.ShowOlderBookmarks', 'XenForo.ShowOlderBookmarks');
	
	
	
	XenForo.EditBookmark  = function($form)
	{
		$form.bind('AutoValidationComplete', function(e)
		{
			
			$serialized = $form.serializeArray();
			
			var $note = $serialized[0].value;
			var $tag = $serialized[1].value;
			var $state = $serialized[2].value;
			var $id = $serialized[3].value; 
			
			if ($state == 'public')
			{
				$state = 'Make Private';
				$addClass = 'public';
				$removeClass = 'private';
			}
			else
			{
				$state = 'Make Public';
				$addClass = 'private';
				$removeClass = 'public';
			}
			
			
			var $tagElement = $('a[id~="Tag-'+ $id +'"]');			
			var $noteElement = $('span[id~="note-' + $id + '"]');
			var $statusElement = $('a[id~="Status-' + $id + '"]');
			
			if ($tagElement.size() > 0)
			{
			
				$noteElement.html($note);
				
				//Change the URL to the new TAG
				$oldTag = $tagElement.html();
				$newTagUrl = $tagElement.attr('href').replace($oldTag, $tag);			
				$tagElement.html($tag);
				$tagElement.attr('href', $newTagUrl);
				$tagElement.attr('title', $tagElement.attr('title').replace($oldTag, $tag));
				
				$('a[id~="Status-' + $id + '"] span.PublicLabel').html($state);
				$statusElement.removeClass($removeClass).addClass($addClass);				
			}
			else
			{
				window.location = XenForo.canonicalizeUrl('index.php?account/bookmarks');
				return false;
			}
		});
	};
	
	
	XenForo.register('form.EditBookmark', 'XenForo.EditBookmark');
	
	

	
	
	
	
	
	
	
	
	
	

	
	
}
(jQuery, this, document);