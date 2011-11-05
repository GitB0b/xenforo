/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
	XenForo.SearchForm = function($form)
	{
		
		var $input = $('#ctrl_search'),
			$target = $($form.data('target')),			
			timeOut = null,
			xhr = null,
			storedValue = '';
		
		$input.attr('autocomplete', 'off').bind(
		{
			keyup: function(e)
			{				
				var currentValue = $input.strval();

				if (currentValue != storedValue && currentValue.length >= 3)
				{
					storedValue = currentValue;
					
					clearTimeout(timeOut);
					timeOut = setTimeout(function()
					{
						console.log('The input now reads "%s"', $input.strval());						
						if (xhr)
						{
							xhr.abort();
						}
						
						xhr = XenForo.ajax
						(								
							$form.attr('action'),
							
							$form.serializeArray(),							
							function(ajaxData, textStatus)
							{									
								if (XenForo.hasResponseError(ajaxData))
								{									
									return false;
								}
								
								if (XenForo.hasTemplateHtml(ajaxData))
								{									
									$target.empty().append(ajaxData.templateHtml);
									$(document).find("#all").hide();
									//$target.empty().append(ajaxData.templateHtml);
								}								
							}							
						);

					}, 250);
				}
				if (currentValue < 3)
				{
					$target.empty();
					$(document).find("#all").show();
				}
			},

			keydown: function(e)
			{
				switch (e.which)
				{
					case 38: // up
					case 13: return false;
					case 40: // down
					{
						var $links = $target.find('li'),
							$selected = $links.filter('.kbSelect'),
							index = 0;

						if ($selected.length)
						{
							index = $links.index($selected.get(0));

							index += (e.which == 40 ? 1 : -1);

							if (index < 0 || index >= $links.length)
							{
								index = 0;
							}
						}

						$links.removeClass('kbSelect').eq(index).addClass('kbSelect');
						return false;
					}
				}
			}
		});

	
	};

	// *********************************************************************

	XenForo.register('#searchForm', 'XenForo.SearchForm');

}
(jQuery, this, document);