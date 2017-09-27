(function() {
	tinymce.create('tinymce.plugins.dw_chart', {
		init: function(ed,url) {
			ed.addButton('dw_chart',{
				title: 'DW Chart',
				icon: 'chart',
				onclick: function() {
					tb_show('', 'admin-ajax.php?action=dw_chart_select_chart&width=300&height=125');
					setTimeout(function() {
						jQuery('#TB_window').css({
							width: '300',
							height: '125',
							'margin-left': '-15%',
							top: '30%'
						})
					},100)
				}
			});
		},
		getInfo: function() {
			return {
				longname: 'Import chart short-code',
				author: 'DesignWall',
				authorurl: 'https://www.designwall.com',
				infourl: 'https://www.designwall.com',
				version: '1.0'
			}
		}
	})

	tinymce.PluginManager.add( 'dw_chart', tinymce.plugins.dw_chart );
})();