/**
 * Allows text inputs to display a placeholder message until it gets focus, at which point the input
 * is set to empty.
 *
 * This simulated the placeholder attribute in html5.
 * http://dev.w3.org/html5/spec/Overview.html#the-placeholder-attribute
 *
 * @copyright Clock Limited 2010
 * @license http://opensource.org/licenses/bsd-license.php New BSD License
 * @author Paul Serby <paul.serby@clock.co.uk>
 */
(function ($) {
	$.fn.placeholder = function (text) {

	return this.each(function () {

			var
				context = $(this),
				placeholderText,
				nativePlaceholderSupport = ('placeholder' in document.createElement('input'));

			function onBlur(event) {
				checkIfEmpty($(event.target));
			}

			function checkIfEmpty() {
				if (context.val() === '') {
					if (context.attr('type') === 'password') {
						try {
							context.attr('type', 'text');
						} catch(e) {
							return false;
						}
					}
					context.val(placeholderText);
					context.addClass('ui-placeholder');
				}
			}

			function onFocus(event) {
				context.removeClass('ui-placeholder');
				if (context.val() === placeholderText) {
					context.val('');
				}
			}

			if (text === undefined) {
				placeholderText = $(this).attr('placeholder');
			} else {
				placeholderText = text;
			}

			if (!nativePlaceholderSupport || (jQuery.browser.msie && jQuery.browser.version == 10)) {
				checkIfEmpty(context.blur(onBlur).focus(onFocus));
				context.parents('form').submit(function(event) {
					if (context.val() === placeholderText) {
						context.val('');
					}
				});
			} else {
				context.attr('placeholder', placeholderText);
			}
		});
	};
})(jQuery);
