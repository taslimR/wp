// JavaScript Document
jQuery(document).ready(function($) {

    // Start Single Shortcode Start
    (function() {
        tinymce.create('tinymce.plugins.wmtmyteamtable', {
            init : function(ed, url) {
                ed.addButton('wmtmyteamtable', {
                    title : 'Add My Team Shortcode',  
                    image : url+'/images/favicon.ico',
                    onclick : function() {
                    	
                        if( $('select#wmt-ids option').length ){
        			        var valarr = [];
                            $('select#wmt-ids option').each(function(){
                                valarr.push({ text: "My Team " +$(this).val(), value: $(this).val() });
                            });
                            
                            ed.windowManager.open({
                                title: 'Add My Team Shortcode',
                                body: [
                                        {  
                                            type: 'listbox',
                                            name: 'dx_iframe_form',
                                            label: 'Select Form',
                                             'values': valarr
                                        }
                                    ],
                                    onsubmit: function( e ) {
                                            var shortcodestr = "";
                                            shortcodestr = '[wmt_myteam id="'+e.data.dx_iframe_form+'"]';
                                            ed.execCommand('mceInsertContent', false, shortcodestr);
                                    }
                            });
                        } else {
                        	alert( "This site doesn't created My Team yet." );
                        }
     				}
                });
            },
            createControl : function(n, cm) {
                return null;
            },
        });
     	
        tinymce.PluginManager.add('wmtmyteamtable', tinymce.plugins.wmtmyteamtable);
    })();
});