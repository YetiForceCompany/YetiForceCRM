/**
 * jQuery additions
 */
;(function($){  

  /** 
   * Position the first element in the jQuery list near another element  
   * using absolute positioning. The element should already have the  
   * proper z-Index set. 
   *  
   * @param string align 'bottom' for bottom left, or 'right' for top right, 
   *    'left' for top left, 'top' for above left. 
   */  
  $.fn.makePositioned = function(align, element) {  
    var first = this.eq(0);  
    var pos, height, width, left, top, thisHeight, thisWidth;  
    pos = element.offset();  
    height = element.outerHeight(), width = element.outerWidth();  
    left = pos.left, top = pos.top;  
    thisHeight = first.outerHeight(), thisWidth = first.outerWidth();  

    switch (align) {   
      case 'bottom':  
        top += height;  
      break;  
      case 'right':  
        left += width;  
      break;  
      case 'left':  
        left = left - thisWidth;  
      break;  
      case 'top':  
        top = top - thisHeight;  
      break;  
    }  

    first.css({   
      top: parseInt(top)+'px',   
      left: parseInt(left)+'px',  
      position: 'absolute'  
    });  

    return this;  
  }  

})(jQuery);