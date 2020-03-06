Sbox.tabs.TabImages = {
	title: 'Картинки',
	items: [{
	    xtype: 'panel',
		cls: 'container',
	    items: [
			{/** */
				xtype: 'button',
				text: 'Очистка картинок',
				cls: 'primary-button',
				id: 'button',
				handler() {
					Ext.getCmp('button').setText('Загрузка...');
					MODx.Ajax.request({
						url: Sbox.config.connector_url,
						params: {
							action: 'images/clean',
							confirm: false
						},
						listeners: {
							success: {
								fn(r) {
									console.log(r);

									let list = ''
									let text = 'Нет файлов для удаления';
									if (r.message.removed) {
										for (let f in r.message.files) {
											list += r.message.files[f] + '<br>'
										}
										text = `${list} <hr> Будет удалено ${r.message.removed} файлов из ${r.message.total}`
									}

									MODx.msg.confirm({
										title: 'Удалить следующие файлы?',
										text,
										url: Sbox.config.connector_url,
										params: {
											action: 'images/clean',
											confirm: true
										},
										listeners: {
									        success: { fn(r) {
												if (r.message.confirm) {
													MODx.msg.alert('', `Удалено ${r.message.removed} файлов`);
												}
									            Ext.getCmp('button').setText('Очистка картинок');
									        }, scope: true},
									        cancel: { fn(r) {
									            Ext.getCmp('button').setText('Очистка картинок');
									        }, scope: true}
										}
									});
								}, scope: this
							}
						}
					});
				}	
			},/** */
			{
				html: `
					<br>
					<p>Удаляются картинки, которых нет в поле content и в TV параметрах, <br>
					у которых дата изменения больше недели (меняется в emanager/processors/images/clean.class.php), <br>
					которых нет в sbox_cfg</p>
				`
			}
		]

	}]
};
