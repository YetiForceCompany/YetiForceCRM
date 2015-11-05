{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
<script>
var height = window.innerHeight;
$(document).ready( function(){
	$('#roundcube_interface').css('height', height-70)
} );
</script>
<iframe id="roundcube_interface" style="width: 100%; height: 590px;" src="{$URL}" frameborder="0"> </iframe>
<input type="hidden" value="" id="temp_field" name="temp_field"/>
<input type="hidden" value="{vglobal('site_URL')}" id="site_URL"/>
<input type="hidden" value="{vtlib_isModuleActive('OSSMailTemplates')}" id="activeMailTemplates"/>
