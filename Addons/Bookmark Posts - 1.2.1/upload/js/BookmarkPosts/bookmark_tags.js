/**
 * @author kier
 */

/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
	XenForo.BookmarkTags = function($select)
	{
		var $wrap,
			$container = $($select.data('container')),
			$textbox = $($select.data('textbox')),
			$popupControl = $('<span rel="menu"><span id="tag_name" class="prefixText"></span></span>').addClass('prefix noPrefix').data('css', 'prefix noPrefix');
		
		if ($textbox.length == 0)
		{
			return;
		}

		$container.hide();

		$textbox.bind(
		{
			focus: function(e) { $wrap.addClass('Focus'); },
			blur: function(e) { $wrap.removeClass('Focus'); }
		});

		function setTextboxWidth(e)
		{
			$textbox.css('width', function()
			{
				var w = $wrap.innerWidth() - 10;

				$textbox.siblings().not($textbox).each(function()
				{
					w -= $(this).outerWidth(true);
				});

				return w;
			});
		}

		function setPrefix($link, preventFocus)
		{
			var $option = $link.data('option');
			
			$link.closest('ul.PrefixMenu').find('li.PrefixOption, li.PrefixGroup').removeClass('selected');

			if ($option instanceof jQuery)
			{
				$link.closest('li.PrefixOption').addClass('selected');

				
				
				if ($popupControl.data('css'))
				{
					$popupControl.removeClass($popupControl.data('css'));
				}
				
				$popupControl
					.addClass($option.data('css'))
					.data('css', $option.data('css'))
					.find('span.prefixText').text($option.text());				
				
				$select.val($option.val());
			}

			setTextboxWidth();

			if (!preventFocus)
			{
				$textbox.get(0).select();
				$('#tag').attr('value',$('#tag_name').text());
				
			}
		}

		function appendPrefixOption(option, $menu)
		{
			var $option = $(option),

			$link = $('<a href="javascript:" />').data('option', $option).text($option.text()).addClass($option.data('css')).click(function(e)					
			{				
				setPrefix($link);
			});

			$menu.append($('<li />').addClass('PrefixOption').append($link));

			$option.data('link', $link);

			if (option.selected)
			{
				setPrefix($link, true);				
			}
		}

		function getPrefixMenu()
		{
			var $menu = $('<ul class="Menu PrefixMenu secondaryContent" />');
			
			$select.children('optgroup').each(function(i, optgroup)
			{
				var $optgroup = $(optgroup), $group, $links;

				$group = $('<li />').addClass('PrefixGroup').appendTo($menu);

				$('<h3 />').text($optgroup.attr('label')).appendTo($group);

				$links = $('<ul />').appendTo($group);

				$optgroup.children('option').each(function(i, option)
				{
					appendPrefixOption(option, $links);
				});
			});

			$select.children('option').each(function(i, option)
			{
				appendPrefixOption(option, $menu);
			});

			return $('<div class="Popup PrefixPopup"></div>').append($popupControl).append($menu);
		}

		$wrap = $('<div />').addClass('textCtrlWrap').addClass($textbox.attr('class')).insertBefore($textbox).append($textbox);

		$wrap.prepend(getPrefixMenu());

		$(document).bind('XenForoActivationComplete OverlayOpened TitlePrefixRecalc', setTextboxWidth);
	};

	// *********************************************************************

	XenForo.register('select.BookmarkTags', 'XenForo.BookmarkTags');

}
(jQuery, this, document);