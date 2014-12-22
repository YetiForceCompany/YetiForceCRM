/**
 * Author: Prasad.A
 * Copyright: You are free to use or modify this extension in commercial and non-commercial application
 * However, make sure to keep the information about this copyright and author information. Provide the
 * credits wherever possible.
 */
;(function($){
	
	$.fn.pushVirtualPage = function() {
		// Make this element as root node first
		var holder = this;		
		if (!holder.hasClass('virtualpages')) {
			holder.addClass('virtualpages');
		}
		
		var pages = $('.virtualpage', holder);
		
		// If its the first page to be added but there are contents
		// inside this element, wrap it into a page.
		if (pages.length == 0 && holder.children().length) {
			var wrapper = $('<div class="virtualpage"></div>');			
			var olderChildrens = holder.children();
			wrapper.append(olderChildrens.clone(true, true));
			olderChildrens.remove();
			holder.empty().append(wrapper);			
			// rebuild the reference
			pages = $('.virtualpage', holder);
		}
		
		// Save scroll position
		var activePage = this.activeVirtualPage();
		activePage.data('scrolltop', $(window).scrollTop());
		
		holder.data('virtualpage-activeindex', pages.length);
		pages.hide();
		
		var page = $('<div class="virtualpage"></div>');
		page.data('isvirtualpage', true);
		holder.append(page);
		page.show();
		return page;
	}
	
	$.fn.popVirtualPage = function(limit) {
		// Page generally will be popped off w.r.t root node
		var holder = this;		
		if (!this.hasClass('virtualpages')) {
			holder = this.closest('.virtualpages');
			if (holder.length) return false;
		}

		var pages = holder.children('.virtualpage');
		if (typeof(limit) == 'undefined') limit = pages.length-1;

		if (pages.length > limit) {
			pages.hide();			
			var page = pages[pages.length-1];			
			$(page).remove();
			delete page;
						
			var activePage = $(pages[pages.length-2]);
			activePage.show();			
			// Restore scroll position
			if (activePage.data('scrolltop')) {
				$(window).scrollTop(activePage.data('scrolltop'))
			}

			holder.data('virtualpage-activeindex', pages.length-2);
			return true;
		} 
		return false;
	}
	
	$.fn.popVirtualPageTo = function(limit) {
		while (this.popVirtualPage(limit)) continue;
	}
	
	$.fn.getVirtualPage = function() {
		if (this.hasClass('virtualpage')) return this;
		return this.closest('.virtualpage');
	}
	
	$.fn.activeVirtualPage = function() {
		var holder = this; 
		var activeIndex = parseInt(holder.data('virtualpage-activeindex'));
		var pages = $('.virtualpage', holder);
		return $(pages[activeIndex]);
	}
})(jQuery);