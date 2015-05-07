/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
jQuery.Class("Potentials_PotentialsList_Dashboard_Js",{},{
	registerEvents : function(){
		$('.dashboardContainer .potentialsListHeader .potentialsListSwitch').on('switchChange.bootstrapSwitch', function(e, state) {
			var currentElement = jQuery(e.currentTarget);
			var dashboardWidgetHeader = currentElement.closest('.dashboardWidgetHeader');
			var drefresh = dashboardWidgetHeader.find('a[name="drefresh"]');
			var url = drefresh.data('url');
			
			url = url.replace('&showtype=owner', '');
			url = url.replace('&showtype=common', '');
			url += '&showtype=';
			if(state)
				url += 'owner';
			else
				url += 'common';
			drefresh.data('url',url);
			drefresh.click();
		});
	}
});
jQuery(document).ready(function() {
	var potentialsList = new Potentials_PotentialsList_Dashboard_Js();
	potentialsList.registerEvents();
});
