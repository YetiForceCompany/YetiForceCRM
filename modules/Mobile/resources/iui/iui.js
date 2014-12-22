/*
   Copyright (c) 2007, iUI Project Members
   See LICENSE.txt for licensing terms
 */


(function() {

var slideSpeed = 20;
var slideInterval = 0;

var currentPage = null;
var currentDialog = null;
var currentWidth = 0;
var currentHash = location.hash;
var hashPrefix = "#_";
var pageHistory = [];
var newPageCount = 0;
var checkTimer;
var hasOrientationEvent = false;
var portraitVal = "portrait";
var landscapeVal = "landscape";

// CWZ
var loadedPages = [];
var actionButtons = [];

// *************************************************************************************************

window.iui =
{
    showPage: function(page, backwards)
    {
        if (page)
        {
            if (currentDialog)
            {
                currentDialog.removeAttribute("selected");
                currentDialog = null;
            }

            if (hasClass(page, "dialog"))
                showDialog(page);
            else
            {
                var fromPage = currentPage;
                currentPage = page;

                if (fromPage)
                {
	                if (fromPage.onblur)
						fromPage.onblur();

	                if (fromPage.getAttribute("onunload") && backwards)
						eval(fromPage.getAttribute("onunload"));
								
                    setTimeout(slidePages, 0, fromPage, page, backwards);
                }
                else
                    updatePage(page, fromPage);
                    
			    var lNewPage = (loadedPages.indexOf(page.id) == -1);
			    
			    if (lNewPage)
			    {
			    	loadedPages.push(page.id);
   
			    	if (page.getAttribute("stylesheet"))
		    			loadStylesheet(page.getAttribute("stylesheet"));
	    			
			    	if (page.getAttribute("script"))
		    			loadScript(page.getAttribute("script"), function() { continueLoadingPage(page, lNewPage); });
		    		else
		    			continueLoadingPage(page, lNewPage);
				}
				else
					continueLoadingPage(page, lNewPage);
            }
        }
    },

    showPageById: function(pageId)
    {
        var page = $(pageId);
        if (page)
        {
            var index = pageHistory.indexOf(pageId);
            var backwards = index != -1;
            if (backwards)
                pageHistory.splice(index, pageHistory.length);

            iui.showPage(page, backwards);
        }
    },

    showPageByHref: function(href, args, method, replace, cb)
    {
        var req = new XMLHttpRequest();
        req.onerror = function()
        {
            if (cb)
                cb(false);
        };
        
        req.onreadystatechange = function()
        {
            if (req.readyState == 4)
            {
                if (replace)
                    replaceElementWithSource(replace, req.responseText);
                else 
                {
                    var frag = document.createElement("div");
                    frag.innerHTML = req.responseText;
                    iui.insertPages(frag.childNodes);
                }
                if (cb)
                    setTimeout(cb, 1000, true);
            }
        };

        if (args)
        {
            req.open("POST", href, true); // CWZ 02/11/2009
            //req.open(method || "GET", href, true);
            req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            req.setRequestHeader("Content-Length", args.length);
            req.send(args.join("&"));
        }
        else
        {
            req.open(method || "GET", href, true);
            req.send(null);
        }
    },
    
    insertPages: function(nodes)
    {
        var targetPage;
        for (var i = 0; i < nodes.length; ++i)
        {
            var child = nodes[i];
            if (child.nodeType == 1)
            {
                if (!child.id)
                    child.id = "__" + (++newPageCount) + "__";

                var clone = $(child.id);
                if (clone)
                    clone.parentNode.replaceChild(child, clone);
                else
                    document.body.appendChild(child);

                if (child.getAttribute("selected") == "true" || !targetPage)
                    targetPage = child;
                
                --i;
            }
        }

        if (targetPage)
            iui.showPage(targetPage);    
    },

    getSelectedPage: function()
    {
        for (var child = document.body.firstChild; child; child = child.nextSibling)
        {
            if (child.nodeType == 1 && child.getAttribute("selected") == "true")
                return child;
        }    
    },
    isNativeUrl: function(href)
    {
        for(var i = 0; i < iui.nativeUrlPatterns.length; i++)
        {
            if(href.match(iui.nativeUrlPatterns[i])) return true;
        }
        return false;
    },
    nativeUrlPatterns: [
        new RegExp("^http:\/\/maps.google.com\/maps\?"),
        new RegExp("^mailto:"),
        new RegExp("^tel:"),
        new RegExp("^http:\/\/www.youtube.com\/watch\\?v="),
        new RegExp("^http:\/\/www.youtube.com\/v\/")
    ]
};

// *************************************************************************************************

addEventListener("submit", function(event) 
{
    if (hasClass(findParent(event.target, "form"), "dialog") || hasClass(findParent(event.target, "form"), "panel")) 
    {
        submitForm(findParent(event.target, "form"));
        event.preventDefault();
    }
}, true);

addEventListener("load", function(event)
{
    var page = iui.getSelectedPage();
    if (page)
        iui.showPage(page);

    setTimeout(preloadImages, 0);
    setTimeout(checkOrientAndLocation, 0);
    checkTimer = setInterval(checkOrientAndLocation, 300);
}, false);
    
addEventListener("unload", function(event)
{
	return;
}, false);
    
addEventListener("click", function(event)
{
    var link = findParent(event.target, "a");
    if (link)
    {
    	/* Prasad: Perform load and cache the DOM for re-use unless asked no-to-cache */
    	function custom_load() {
    		if(!hasClass(link, "nocache")) {
    			link.setAttribute("xhref", link.href);
    			link.setAttribute("href", "#__" + (newPageCount) + "__");
    		}
    		unselect();
    	}
    	/* END */
    	
        function unselect() { link.removeAttribute("selected"); }
        
        if (link.href && link.hash && link.hash != "#")
        {
            link.setAttribute("selected", "true");
            iui.showPage($(link.hash.substr(1)));
            setTimeout(unselect, 500);
        }
        else if (link == $("backButton"))
            history.back();
        else if (link.getAttribute("type") == "submit")
           	submitForm(findParent(link, "form"));
        else if (link.getAttribute("type") == "cancel")
            cancelDialog(findParent(link, "form"));
        else if (link.target == "_replace")
        {
            link.setAttribute("selected", "progress");
            iui.showPageByHref(link.href, null, null, link, custom_load); // Prasad: unselect);
        }
        else if (iui.isNativeUrl(link.href))
        {
            return;
        }
        else if (!link.target)
        {
            link.setAttribute("selected", "progress");            
            iui.showPageByHref(link.href, null, null, null, custom_load); // Prasad: unselect);
        }
        else
            return;
        
        event.preventDefault();        
    }
}, true);

addEventListener("click", function(event)
{
    var div = findParent(event.target, "div");
    if (div && hasClass(div, "toggle"))
    {
        div.setAttribute("toggled", div.getAttribute("toggled") != "true");
        event.preventDefault();        
    }
}, true);

function orientChangeHandler()
{
  var orientation=window.orientation;
  switch(orientation)
  {
    case 0:
        setOrientation(portraitVal);
        break;  
        
    case 90:
    case -90: 
        setOrientation(landscapeVal);
        break;
  }
}

if (typeof window.onorientationchange == "object")
{
    window.onorientationchange=orientChangeHandler;
    hasOrientationEvent = true;
    setTimeout(orientChangeHandler, 0);
}

function checkOrientAndLocation()
{
    if (!hasOrientationEvent)
    {
      if (window.innerWidth != currentWidth)
      {   
          currentWidth = window.innerWidth;
          var orient = currentWidth == 320 ? portraitVal : landscapeVal;
          setOrientation(orient);
      }
    }

    if (location.hash != currentHash)
    {
        var pageId = location.hash.substr(hashPrefix.length);
        iui.showPageById(pageId);
    }
}

function setOrientation(orient)
{
    document.body.setAttribute("orient", orient);
    setTimeout(scrollTo, 100, 0, 1);
}

function showDialog(page)
{
    currentDialog = page;
    page.setAttribute("selected", "true");
    
    if (hasClass(page, "dialog") && !page.target)
        showForm(page);
}

function showForm(form)
{
    form.onsubmit = function(event)
    {
        event.preventDefault();
        submitForm(form);
    };
    
    form.onclick = function(event)
    {
        if (event.target == form && hasClass(form, "dialog"))
            cancelDialog(form);
    };
}

function cancelDialog(form)
{
    form.removeAttribute("selected");
}

function updatePage(page, fromPage)
{
    if (!page.id)
        page.id = "__" + (++newPageCount) + "__";

    location.href = currentHash = hashPrefix + page.id;
    pageHistory.push(page.id);
    
    var pageTitle = $("pageTitle");
    if (page.title)
        pageTitle.innerHTML = page.title;

    if (page.localName.toLowerCase() == "form" && !page.target)
        showForm(page);
        
    var backButton = $("backButton");    
    if (backButton)
    {
        var prevPage = $(pageHistory[pageHistory.length-2]);
        if (prevPage && !page.getAttribute("hideBackButton"))
        {
            backButton.style.display = "inline";
            backButton.innerHTML = prevPage.title ? prevPage.title : "Back";
        }
        else
            backButton.style.display = "none";
    }    
}

function slidePagesNew(fromPage, toPage, backwards)
{        
	try
	{
		var page = (backwards ? fromPage : toPage);
	    var axis = page.getAttribute("axis");
	    
	    if (axis == "y")
	        page.style.top = "100%";
	    else
	    {
		    if (backwards)
		    {
	        	fromPage.style.left = "0%";
	        	toPage.style.left = "-100%";
        	}
	        else
	        {
	        	fromPage.style.left = "0%";
	        	toPage.style.left = "100%";
        	}
        }

		var transitionProperty = (axis == "y") ? 'top' : 'left';
		var transitionDuration = 300;
	      	      
		toPage.style.webkitTransitionDuration = transitionDuration + "ms";
		fromPage.style.webkitTransitionDuration = transitionDuration + "ms";
	      
	    toPage.setAttribute("selected", "true");
	    scrollTo(0, 1);
	    
	    clearInterval(checkTimer);
	
  		fromPage.style.webkitTransitionProperty = transitionProperty;
		fromPage.style.transitionTimingFunction = "ease-in";

  		toPage.style.webkitTransitionProperty = transitionProperty;
		toPage.style.transitionTimingFunction = "ease-in";
			
	    if (backwards)
	    {
			fromPage.style.left="100%";
			toPage.style.left="0%";
		}
	    else
	    {
			fromPage.style.left="-100%";
			toPage.style.left="0%";
		}
		
		// Call completion function sometime after we think we'll be done
	    setTimeout(slideComplete, transitionDuration);
			
		function slideComplete() 
		{
	        if (!hasClass(toPage, "dialog"))
	            fromPage.removeAttribute("selected");
	     
			toPage.style.webkitTransitionDuration = "0s";
			fromPage.style.webkitTransitionDuration = "0s";
	              
	        checkTimer = setInterval(checkOrientAndLocation,300);
	        setTimeout(updatePage, 0, toPage, fromPage);
	    }
	}
	catch (ex) { alert(ex); }
}

function slidePages(fromPage, toPage, backwards)
{        
    var axis = (backwards ? fromPage : toPage).getAttribute("axis");
    if (axis == "y")
        (backwards ? fromPage : toPage).style.top = "100%";
    else
        toPage.style.left = "100%";

    toPage.setAttribute("selected", "true");
    scrollTo(0, 1);
    clearInterval(checkTimer);
    
    var percent = 100;
    slide();
    var timer = setInterval(slide, slideInterval);

    function slide()
    {
        percent -= slideSpeed;
        if (percent <= 0)
        {
            percent = 0;
            if (!hasClass(toPage, "dialog"))
                fromPage.removeAttribute("selected");
            clearInterval(timer);
            checkTimer = setInterval(checkOrientAndLocation, 300);
            setTimeout(updatePage, 0, toPage, fromPage);
        }
    
        if (axis == "y")
        {
            backwards
                ? fromPage.style.top = (100-percent) + "%"
                : toPage.style.top = percent + "%";
        }
        else
        {
            fromPage.style.left = (backwards ? (100-percent) : (percent-100)) + "%"; 
            toPage.style.left = (backwards ? -percent : percent) + "%"; 
        }
    }
}

function preloadImages()
{
    var preloader = document.createElement("div");
    preloader.id = "preloader";
    document.body.appendChild(preloader);
}

function submitForm(form)
{
	// CWZ
	if (form.getAttribute("target") == "_self")
		form.submit();
	else
    	iui.showPageByHref(form.action, encodeForm(form), form.method || "POST");
}

function encodeForm(form)
{
    function encode(inputs)
    {
        for (var i = 0; i < inputs.length; ++i)
        {
            if (inputs[i].name)
            {
				if ((inputs[i].getAttribute("type") == "checkbox" && !inputs[i].checked) 
					|| (inputs[i].getAttribute("type") == "radio" && !inputs[i].checked) 
					|| (inputs[i].getAttribute("type") == "submit") 
					|| (inputs[i].name && inputs[i].disabled)) 
                {
                    continue;
                } 
                else
                	args.push(inputs[i].name + "=" + escape(inputs[i].value));
            }
        }
    }

    var args = [];
    encode(form.getElementsByTagName("input"));
    encode(form.getElementsByTagName("textarea")); // added in 0.20
    encode(form.getElementsByTagName("select"));
    return args;    
}

function findParent(node, localName)
{
    while (node && (node.nodeType != 1 || node.localName.toLowerCase() != localName))
        node = node.parentNode;
    return node;
}

function hasClass(self, name)
{
    var re = new RegExp("(^|\\s)"+name+"($|\\s)");
    return re.exec(self.getAttribute("class")) != null;
}

function replaceElementWithSource(replace, source)
{
    var page = replace.parentNode;
    var parent = replace;
    while (page.parentNode != document.body)
    {
        page = page.parentNode;
        parent = parent.parentNode;
    }

    var frag = document.createElement(parent.localName);
    frag.innerHTML = source;

    page.removeChild(parent);

    while (frag.firstChild)
        page.appendChild(frag.firstChild);
}

function $(id) { return document.getElementById(id); }
function ddd() { console.log.apply(console, arguments); }

// CWZ 04/24/2009
function loadActionButton(page)
{
	try
	{
		var btn = $("actionButton");
		var lSet = false;

		if (!actionButtons[page.id])
		{
			actionButtons[page.id] = { "title" : "", "href" : null, "target" : null, "visible" : true };
			
			if (page.getAttribute("actionbutton"))
			{
				var oButton = eval("("+page.getAttribute("actionbutton")+")");
				
				if (oButton)
				{
					if (oButton.title	) actionButtons[page.id].title 	= oButton.title;
					if (oButton.href	) actionButtons[page.id].href 		= oButton.href;
					if (oButton.target	) actionButtons[page.id].target	= oButton.target;
				}
				
				if (!btn)
				{
					var oToolbar = document.getElementsByClassName("toolbar")[0];
					btn = document.createElement("A");
					btn.id="actionButton";
					btn.className="button";
					
					oToolbar.appendChild(btn);
				}
			} 
			else if (!btn) // stores the fact that the page has no button
			{
				lSet = true;
				actionButtons[page.id].visible = false;
			}
			else
			{
				lSet = true;
				actionButtons[page.id].title  = btn.innerHTML;
				actionButtons[page.id].href   = btn.href;
				actionButtons[page.id].target = btn.target;
			}
		}
		
		if (!lSet)
		{
			btn.style.visibility = (actionButtons[page.id].visible ? "visible" : "hidden");
			
			if (actionButtons[page.id].title 	!= null) btn.innerHTML 	= actionButtons[page.id].title;
			if (actionButtons[page.id].href 	!= null) btn.href 		= actionButtons[page.id].href;
			if (actionButtons[page.id].target 	!= null) btn.target 	= actionButtons[page.id].target;
		}
	}
	catch (ex) { console.log(ex); }
}

// CWZ 04/27/2009
function continueLoadingPage(page, lNewPage)
{
	if (lNewPage && page.getAttribute("onload"))
		eval(page.getAttribute("onload"));
		
	loadActionButton(page);
	
    if (page.onfocus)
    	page.onfocus();
}

// CWZ adapted from http://www.javascriptkit.com/javatutors/loadjavascriptcss.shtml
// and http://stackoverflow.com/questions/756382/bookmarklet-wait-until-javascript-is-loaded/756436
function loadScript(cFilename, callback)
{
	var script = document.createElement("script");
	
	script.setAttribute("type","text/javascript");
	script.setAttribute("src", cFilename);
	
	var done = false;
	script.onload = script.onreadystatechange = function()
	{
		if( !done && ( !this.readyState 
		                        || this.readyState == "loaded" 
		                        || this.readyState == "complete") )
		{
			done = true;
	
			callback();
		}
	};
	
	document.getElementsByTagName("head")[0].appendChild(script);
	
	return script;
}

function loadStylesheet(cFilename, callback)
{
	var stylesheet = document.createElement("link");
	
	stylesheet.setAttribute("rel", "stylesheet");
	stylesheet.setAttribute("type", "text/css");
	stylesheet.setAttribute("href", cFilename);

	if (callback)
	{
		var done = false;
		stylesheet.onload = stylesheet.onreadystatechange = function()
		{
			if( !done && ( !this.readyState 
			                        || this.readyState == "loaded" 
			                        || this.readyState == "complete") )
			{
				done = true;
				
				callback();
			}
		};
	}
	
	document.getElementsByTagName("head")[0].appendChild(stylesheet);
}


})();

