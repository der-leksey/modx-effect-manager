var Sbox = function (config) {
    config = config || {};
    Sbox.superclass.constructor.call(this, config);
};
Ext.extend(Sbox, MODx.Component, {
    panel: {}, window: {}, grid: {}, tabs: {}, config: {}
});
Ext.reg('Sbox', Sbox);
Sbox = new Sbox();


Sbox.panel.Home = function(config) {
    config = config || {};
    Ext.apply(config, {
        cls: 'container',
        items: [{
            html: '<h2>Effect Manager</h2>'
        }, {
            xtype: 'modx-tabs',
            items: [
                Sbox.tabs.TabImages,
                Sbox.tabs.TabUsers,
            ]
        } ]
    });
    Sbox.panel.Home.superclass.constructor.call(this, config);
};


Ext.extend(Sbox.panel.Home, MODx.Panel);
Ext.reg('sboxPanelHome', Sbox.panel.Home);