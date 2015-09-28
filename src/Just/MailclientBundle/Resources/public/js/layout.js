Ext.Loader.setConfig({enabled: true});

Ext.Loader.setPath('Ext.ux', '../ux');

Ext.require(['*']);
Ext.Component.override({
    setHtml: function (html, loadScripts) {
        this.update(html, loadScripts);
    }
});

Ext.define('EmaillistModel', {
    extend: 'Ext.data.Model',
    fields: [ 
        {name: 'subject', type: 'string'}, 
        {name: 'from', type: 'string'},
        {name: 'to', type: 'string'},
        {name: 'date', type: 'string'},
        {name: 'message_id', type: 'string'},
        {name: 'size', type: 'int'},
        {name: 'uid', type: 'int', unique: true, convert: null},
        {name: 'msgno', type: 'int'},
        {name: 'recent', type: 'boolean'},
        {name: 'flagged', type: 'boolean'},
        {name: 'answered', type: 'boolean'},
        {name: 'deleted', type: 'boolean'},
        {name: 'seen', type: 'boolean'},
        {name: 'draft', type: 'boolean'},
        {name: 'udate', type: 'int'}
    ],
    idProperty: 'uid'
});
//
// This is the main layout definition.
//
Ext.onReady(function() {

    Ext.tip.QuickTipManager.init();



    // Finally, build the main layout once all the pieces are ready.  This is also a good
    // example of putting together a full-screen BorderLayout within a Viewport.
    Ext.create('Ext.Viewport', {
        layout: 'border',
        style: 'background-color: lightgray;',
        items: [{
                //xtype: 'box',
                id: 'header',
                region: 'north',
                html: '<div class="bnheaderbar"></div>',
                //cls: 'bnheaderbar',
//plain: true,
                height: 50,
                tbar:{
                    weight: -100,
                    items: menu
                }
            },
            {
                region: 'center',
                xtype: 'tabpanel', // TabPanel itself has no title
                id: 'contenttabpanel',
                plain: true,
                activeTab: 0, // First tab active by default
                items: [{
                    title: 'Default Tab',
                    html: 'The first tab\'s content. Others may be added dynamically',
                    bbar:['lala']
                }]
                
                
            }
        ],
        renderTo: Ext.getBody()
    });
    loadtab();
});

function loadtab(){
    var token = window.location.hash.substr(1);
    console.log(token);
    var tabfound=null;
    Ext.Array.each(Ext.getCmp('contenttabpanel').items.items,function(tab,index,tabpanels){
        if (tab.loader!=null && tab.loader.url==token){
            console.log(tab.loader.url,token);
            tabfound=tab;
            return true;
        }
    });
    if (tabfound!=null) {
        Ext.getCmp('contenttabpanel').setActiveTab(tabfound);
        return true;
    }
    if (token=='') return false;
    Ext.getCmp('contenttabpanel').add({
        'title': 'Loading...',
        closable: true,
        layout: 'fit',
        loader: {
            url: token,
            autoLoad: false,
            loadOnRender: true,
            loadMask: true,
            contentType: 'component',
            renderer: 'component',
            listeners: {
                load: function(tab, response, options, eOpts){
                    ttt=tab;
                    var responsejson=Ext.JSON.decode(response.responseText);
                    if (typeof(responsejson.tabtitle) != 'undefined') tab.getTarget().setTitle(responsejson.tabtitle);
                    
                }
            }
            
        },
        listeners: {
           beforeactivate: function(tab){
               console.log('activate');
               window.location.hash='#'+tab.loader.url;
               return true;

           }
        }
    });
    Ext.Array.each(Ext.getCmp('contenttabpanel').items.items,function(tab,index,tabpanels){
        if (tab.loader!=null && tab.loader.url==token){
            Ext.getCmp('contenttabpanel').setActiveTab(tab);
            return true;
        }
    });
}


if ( 'onhashchange' in window ) {
    window.onhashchange = function() {
        loadtab();
    }
}
