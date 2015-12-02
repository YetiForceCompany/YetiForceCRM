function PotentialsRelatedList() {

    this.registerEvent = function() {
        var thisInstance = this;

        jQuery('#generateFromTpl').on('click', function() {
            thisInstance.process()
        })
    },
            
    this.process = function() {
        app.showModalWindow(null, 'index.php?module=OSSProjectTemplates&view=GenerateTpl&rel_id=' + this.getVarFromUrl()['record']);
    },

    this.getVarFromUrl = function() {
        var vars = {};
        var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m, key, value) {
            vars[key] = value;
        });
        return vars;
    }
}


jQuery(document).ready(function() {
    var gt = new PotentialsRelatedList();
    gt.registerEvent();
})