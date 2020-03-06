BOX = window.BOX || {};


BOX.tvsHide = function(tvs) {
	var ids = tvs.join(",").replace(/(\d+)/g, "tv$1");
	MODx.hideTVs(ids.split(","));
};


Ext.onReady(function() {
	
	const blocks = {
		header: {
			show: [73, 87],
			hide: [83, 68],
		},
		slider: {
			show: [74, 75, 76],
		},
		footer: {
			show: [87],
			hide: [24, 83, 80, 68],
		}
	}
	
	var BoxScriptReady = false;
	MODx.on("ready", function() {
		
		/*
		MODx.addTab("modx-resource-tabs",{
            title: 'Tab Title',
            id: 'some-custom-tab-id',
            width: '95%',
            items: [{
                html: '<h2>Stuff!</h2><p>This is some awesome text in HTML.</p>'
            }]
        });*/
		
		if (BoxScriptReady) return;
		BoxScriptReady = true;
		
		var mode = BOX.mode;
		var resource = BOX.resource;
		resource.properties=resource.properties || {};
		var tabs = Ext.getCmp("modx-resource-tabs");
		
		if (BOX.resource && BOX.resource.template == 3) {
			
			var title = resource.menutitle;
			var menutitle = Ext.getCmp("modx-resource-menutitle");
			menutitle.disable();
			
			let hide = [];
			let show = [];
			
			for (let b in blocks) {
				if (!title.includes(b)) {
					blocks[b].show && hide.push(...blocks[b].show);
				} else {
					show = blocks[b].show || [];
					blocks[b].hide && hide.push(...blocks[b].hide);
				}
			}
			
			hide = hide.filter((value) => !show.includes(value));
			BOX.tvsHide([...new Set([...hide])]);

		}
		
	});

						
});