Sbox.tabs.TabImages = {
	title: 'Картинки',
	items: [{
	    xtype: 'panel',
		cls: 'container',
		
	    items: [
			{/** */
				xtype: 'button',
				text: 'Load',
				cls: 'primary-button',
				handler() {
					MODx.Ajax.request({
						url: Sbox.config.connector_url,
						params: {
							action: 'images/clean',
							parent: 0
						},
						listeners: {
							success: {
								fn(r) {
									console.log(r); // выведем ответ в консоль и покажем окошко

									let list = ''
									const files = r.message.files;
									for (let f in files) {
										list += files[f] + '<br>'
									}

									//files.map((v) => v + '-');

									MODx.msg.confirm({
										title: 'Вы уверены?',
										text: `${list}`,
										params: {
										   deleteWorld: true
										},
									});
								}, scope: this
							}
						}
					});
				}	
			}/** */
		]

	}]
};
