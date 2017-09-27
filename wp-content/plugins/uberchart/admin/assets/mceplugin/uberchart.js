/*
 * Create the 'UberChart' button on the tinymce editor
 */
(function() {
   tinymce.create('tinymce.plugins.uberchart', {
    init : function(ed, url) {

          // Register commands
          ed.addCommand('openuberchart', function() {
                  ed.windowManager.open({
                          file : url + '/uberchart_modal_window.php',
                          width : 220 + parseInt(ed.getLang('button.delta_width', 0)),
                          height : 180 + parseInt(ed.getLang('button.delta_height', 0)),
                          inline : 1
                  }, {
                          plugin_url : url
                  });
          });

          //register the 'selectuberchart' button
          ed.addButton('selectuberchart', {title : 'UberChart', cmd : 'openuberchart', icon: 'icon dashicons-chart-line', });
         
      },
      createControl : function(n, cm) {
         return null;
      },
      getInfo : function() {
         return {
            longname : "UberChart",
            author : 'DAEXT',
            authorurl : 'http://daext.com',
            infourl : 'http://daext.com',
            version : "1.00"
         };
      }
   });
   tinymce.PluginManager.add('uberchart', tinymce.plugins.uberchart);
})();