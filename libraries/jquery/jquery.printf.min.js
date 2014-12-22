/*##############################################################################
#    ____________________________________________________________________
#   /                                                                    \
#  |               ____  __      ___          _____  /     ___    ___     |
#  |     ____       /  \/  \  ' /   \      / /      /__   /   \  /   \    |
#  |    / _  \     /   /   / / /    /  ___/  \__   /     /____/ /    /    |
#  |   / |_  /    /   /   / / /    / /   /      \ /     /      /____/     |
#  |   \____/    /   /    \/_/    /  \__/  _____/ \__/  \___/ /           |
#  |                                                         /            |
#  |                                                                      |
#  |   Copyright (c) 2007                             MindStep SCOP SARL  |
#  |   Herve Masson                                                       |
#  |                                                                      |
#  |      www.mindstep.com                              www.mjslib.com    |
#  |   info-oss@mindstep.com                           mjslib@mjslib.com  |
#   \____________________________________________________________________/
#
#  Version: 1.0.0
#
#  (Svn version: $Id: jquery.printf.js 3434 2007-08-27 09:31:20Z herve $)
#
#----------[This product is distributed under a BSD license]-----------------
##############################################################################*/

(function($){$.sprintf=function(fmt)
{return _sprintf_(fmt,arguments,1);}
$.vsprintf=function(args,offset)
{if(offset===undefined)
{offset=0;}
return _sprintf_(args[offset],args,offset+1);}
$.alertf=function()
{return alert($.vsprintf(arguments));}
$.vlogf=function(args)
{if("console"in window)
{console.info($.vsprintf(args));}}
$.verrorf=function(args)
{if("console"in window)
{console.error($.vsprintf(args));}}
$.errorf=function()
{$.verrorf(arguments);}
$.logf=function()
{$.vlogf(arguments);}
FREGEXP=/^([^%]*)%([-+])?(0)?(\d+)?(\.(\d+))?([doxXcsf])(.*)$/;HDIGITS=["0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f"];function _empty(str)
{if(str===undefined||str===null)
{return true;}
return(str=="")?true:false;}
function _int_(val)
{return Math.floor(val);}
function _printf_num_(val,base,pad,sign,width)
{val=parseInt(val,10);if(isNaN(val))
{return"NaN";}
aval=(val<0)?-val:val;var ret="";if(aval==0)
{ret="0";}
else
{while(aval>0)
{ret=HDIGITS[aval%base]+ret;aval=_int_(aval/base);}}
if(val<0)
{ret="-"+ret;}
if(sign=="-")
{pad=" ";}
return _printf_str_(ret,pad,sign,width,-1);}
function _printf_float_(val,base,pad,sign,prec)
{if(prec==undefined)
{if(parseInt(val)!=val)
{return""+val;}
prec=5;}
var p10=Math.pow(10,prec);var ival=""+Math.round(val*p10);var ilen=ival.length-prec;if(ilen==0)
{return"0."+ival.substr(ilen,prec);}
return ival.substr(0,ilen)+"."+ival.substr(ilen,prec);}
function _printf_str_(val,pad,sign,width,prec)
{var npad;if(val===undefined)
{return"(undefined)";}
if(val===null)
{return"(null)";}
if((npad=width-val.length)>0)
{if(sign=="-")
{while(npad>0)
{val+=pad;npad--;}}
else
{while(npad>0)
{val=pad+val;npad--;}}}
if(prec>0)
{return val.substr(0,prec);}
return val;}
function _sprintf_(fmt,av,index)
{var output="";var i,m,line,match;line=fmt.split("\n");for(i=0;i<line.length;i++)
{if(i>0)
{output+="\n";}
fmt=line[i];while(match=FREGEXP.exec(fmt))
{var sign="";var pad=" ";if(!_empty(match[1]))
{output+=match[1];}
if(!_empty(match[2]))
{sign=match[2];}
if(!_empty(match[3]))
{pad="0";}
var width=match[4];var prec=match[6];var type=match[7];fmt=match[8];if(index>=av.length)
{output+="[missing parameter for type '"+type+"']";continue;}
var val=av[index++];switch(type)
{case"d":output+=_printf_num_(val,10,pad,sign,width);break;case"o":output+=_printf_num_(val,8,pad,sign,width);break;case"x":output+=_printf_num_(val,16,pad,sign,width);break;case"X":output+=_printf_num_(val,16,pad,sign,width).toUpperCase();break;case"c":output+=String.fromCharCode(parseInt(val,10));break;case"s":output+=_printf_str_(val,pad,sign,width,prec);break;case"f":output+=_printf_float_(val,pad,sign,width,prec);break;default:output+="[unknown format '"+type+"']";break;}}
output+=fmt;}
return output;}})(jQuery);