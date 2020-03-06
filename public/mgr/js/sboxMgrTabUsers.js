Sbox.tabs.TabUsers = {
	title: 'Пользователи',
	items: [{
	    xtype: 'panel',
	    cls: 'container',
	    items: [{
		    xtype: 'sboxGridUsers'
		}]
	}]
};


//Окно добавления и редактирования юзера
Sbox.window.Users = function(config) {
    config = config || {};
    Ext.applyIf(config, {
    	url: Sbox.config.connector_url,
        autoHeight: true,
        fields: Sbox.window.UsersFieldsUpdate
    });
    Sbox.window.Users.superclass.constructor.call(this, config);
};
Ext.extend(Sbox.window.Users, MODx.Window);
Ext.reg('sboxWindowUsers', Sbox.window.Users); 


//Таблица с юзерами
Sbox.grid.Users = function (config) {
    config = config || {};
    Ext.apply(config, {
    	tbar:[{
		    xtype: 'button',
		    text: 'Создать пользователя',
		    cls: 'primary-button',
		    handler() {
		        MODx.load({
			        xtype: 'sboxWindowUsers',
			        title: 'Создать пользователя',
			        fields: Sbox.window.UsersFieldsCreate,
			        action: 'users/create',
			        listeners: {
						success: {fn: function(response) {
							this.refresh();
							Ext.MessageBox.alert('Пользователь создан',response.a.result.message);
						}, scope: this}
					}
			    }).show();
		    },
		}],
        columns: [
            {dataIndex: 'primary_group',sortable: true, width: 200, header: 'Доступ',
            	renderer(value, metaData, record, row, col, store, gridView) {
            		let name = '?'
            		Sbox.usergroups.forEach((v) => {
            			if (+v.id === value) name = v.name;
            		});
            		return name;
				}
            },
            {dataIndex: 'username',sortable: true, width: 200, header: 'Логин'},
            {dataIndex: 'fullname',sortable: true, width: 200, header: 'Имя'},
            {dataIndex: 'city',sortable: true, width: 200, header: 'Город'}
        ],
        autoHeight: true,
        viewConfig: {
            forceFit: true,
            scrollOffset: 0
        },
        url: Sbox.config.connector_url,
		action: 'users/get',
		fields: ['primary_group','username','fullname','city','id'],
		getMenu() {
		    return [{
		        text: ('Редактировать'),
		        handler: function() {
			        MODx.load({
				        xtype: 'sboxWindowUsers',
				        title: 'Редактировать: '+this.menu.record.username,
				        action: 'users/update',
				        record: this.menu.record,
				        listeners: {
							success: {fn: function(response) {
								this.refresh();
								Ext.MessageBox.alert('Пользователь обновлён',response.a.result.message);
							}, scope: this}
						}
				    }).show();
			    },
		    },'-',{
		        text: ('Удалить'),
		        handler() {
			        MODx.msg.confirm({
			        	url: Sbox.config.connector_url,
				        title: 'Удалить пользователя',
						text: 'Удалить пользователя '+this.menu.record.username+' ?',
						params: {
							action: 'users/remove',
							id: this.menu.record.id
						},
						listeners: {
							success: {fn: function(response) {
								this.refresh();
								Ext.MessageBox.alert('',response.message);
							}, scope: this}
						}
						
				    });
			    },
		    }];
		}
    });
    Sbox.grid.Users.superclass.constructor.call(this, config);
};
Ext.extend(Sbox.grid.Users, MODx.grid.Grid);
Ext.reg('sboxGridUsers', Sbox.grid.Users);


//Поля форм
Sbox.window.UsersFieldsUpdate = [{
    xtype: 'textfield',
    name: 'password',
    fieldLabel: 'Пароль',
    anchor: '100%',
},{
xtype: 'label', text: 'Если пусто, будет сгенерирован автоматически', style: {color: 'grey',fontStyle: 'italic'} 
},{
    xtype: 'textfield',
    name: 'fullname',
    fieldLabel: 'Имя',
    anchor: '100%'
},{
    xtype: 'textfield',
    name: 'city',
    fieldLabel: 'Город',
    anchor: '100%'
},{
    xtype: 'hidden', name: 'id',
},{
    xtype: 'hidden', name: 'username',
}];
Sbox.window.UsersFieldsShare = [];



const usergroups = [];
Ext.onReady(() => {
	if (Sbox.usergroups) {
		Sbox.usergroups.forEach((u, i) => {
			const radio = {
				xtype: 'radio',
		        boxLabel: u.name,
		        name: 'primary_group',
		        value: +u.id, inputValue: +u.id,
			};
			if (i + 1 == Sbox.usergroups.length) radio.checked = true;
			usergroups.push(radio);
		});
	}
});


Sbox.window.UsersFieldsCreate = [{
	xtype: 'textfield',
    name: 'username',
    vtype: 'email',
    allowBlank: false,
    fieldLabel: 'Логин *',
    anchor: '100%',
},{
    xtype: 'textfield',
    name: 'password',
    fieldLabel: 'Пароль',
    anchor: '100%',
},{
	xtype: 'label', text: 'Если пусто, будет сгенерирован автоматически', style: {color: 'grey',fontStyle: 'italic'} 
	},{
	    xtype: 'textfield',
	    name: 'fullname',
	    fieldLabel: 'Имя',
	    anchor: '100%'
	},{
	    xtype: 'textfield',
	    name: 'city',
	    fieldLabel: 'Город',
	    anchor: '100%'
	},{
    fieldLabel: 'Доступ:',
    xtype: 'radiogroup',
    items: usergroups
},];