jQuery.Class("KnowledgeBase_Tree_Js", {},
{
	treeInstance: false,
	generateTree: function (container, data) {
		var thisInstance = this;
		thisInstance.treeInstance = container.find("#treeContent");
		var values = data.result;
		var plugins = ['search'];
		thisInstance.treeInstance.jstree({
			core: {
				data: values,
				themes: {
					name: 'proton',
					responsive: true
				}
			},
			plugins: plugins
		});
	},	
	loadTree: function(){
		var thisInstance = this;
		var container = $('.treeContainer');
		var params = {
			module  : app.getModuleName(),
			action : 'TreeAJAX',
		};
		var progressIndicatorElement = jQuery.progressIndicator({
			position: 'html',
			blockInfo: {
				enabled: true
			}
		});
		AppConnector.request(params).then(function(data){
			progressIndicatorElement.progressIndicator({mode: 'hide'});
			thisInstance.generateTree(container, data);
			thisInstance.registerTreeEvents(container);
			
		});
	},
	searchingInTree: function (text) {
		this.treeInstance.jstree(true).search(text);
	},
	registerSearchEvent: function () {
		var thisInstance = this;
		var valueSearch = $('#valueSearchTree');
		var btnSearch = $('#btnSearchTree');
		valueSearch.keypress(function (e) {
			if (e.which == 13) {
				thisInstance.searchingInTree(valueSearch.val());
			}
		});
		btnSearch.click(function () {
			thisInstance.searchingInTree(valueSearch.val());
		});
	},
	registerTreeEvents: function(){
		var thisInstance = this;
		thisInstance.registerSearchEvent();
		thisInstance.treeInstance.on('changed.jstree',function (e,data){
			if(data.node.original.type == 'folder'){
				var headerInstance = Vtiger_Header_Js.getInstance();
				var moduleName = app.getModuleName();
				var postQuickCreate = function(data){
					thisInstance.loadTree();
				} 
				var relatedParams = {
					category: data.node.original.record_id
				};
				var quickCreateParams = {
					callbackFunction: postQuickCreate,
					data: relatedParams,
					noCache: true
				};
				headerInstance.quickCreateModule(moduleName, quickCreateParams);
			}
			else{
				
			}
		});
	},
	registerEvents: function () {
		var thisInstance = this;
		thisInstance.loadTree();
	
	}
});


