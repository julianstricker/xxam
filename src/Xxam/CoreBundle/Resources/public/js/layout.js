Ext.Loader.setConfig({enabled: true});
Ext.Loader.setPath('Ext.ux', '/assets/vendor/extjs/examples/ux');
Ext.Loader.setPath('Portal.view', '/js/portal');
Ext.Loader.setPath('widget.xxam', '/bundles');
Ext.require([
    '*',
    'Portal.view.Portlet',
    'Portal.view.PortalDropZone',
    'Portal.view.PortalColumn',
    'Portal.view.PortalPanel',
    'Ext.ux.PreviewPlugin',
    'Ext.ux.RowExpander',
    'Ext.ux.IFrame'

]);

Ext.Component.override({
    setHtml: function(html, loadScripts) {
        this.update(html, loadScripts);
    }
});

Xxam = function() {
    var msgCt;

    function createBox(t, s, i) {
        var html = '<div class="msg">';
        if (i)
            html += '<div class="msgimg"><img src="' + i + '" /></div>';
        html += '<h3>' + t + '</h3><p>' + s + '</p></div>';
        return html;
    }
    return {
        msg: function(title, format, icon) {
            if (!msgCt) {
                msgCt = Ext.DomHelper.insertFirst(document.body, {id: 'msg-div'}, true);
            }
            var s = Ext.String.format.apply(String, Array.prototype.slice.call(arguments, 1));
            var m = Ext.DomHelper.append(msgCt, createBox(title, s, icon), true);
            m.hide();
            m.slideIn('t').ghost("t", {delay: 5000, remove: true});
        },
        /**
         * Returns the Component that contains the passed String (id), dom node, or Ext.Element.
         *
         * @param {String/HTMLElement/Ext.Element} el
         * @return {Ext.Component}
         */
        findComponentByElement: function(el) {
            var topmost = document.body,
                    target = Ext.getDom(el),
                    cmp;

            while (target && target.nodeType === 1 && target !== topmost) {
                cmp = Ext.getCmp(target.id);

                if (cmp) {
                    return cmp;
                }

                target = target.parentNode;
            }

            return null;
        },
        init: function() {
        },
        appendHead: function(o)
        {
            var count = 0;
            var scriptTag, linkTag;
            var scriptFiles = o.js;
            var cssFiles = o.css;
            var head = document.getElementsByTagName('head')[0];

            for (var k in cssFiles) {
                linkTag = document.createElement('link');
                linkTag.type = 'text/css';
                linkTag.rel = 'stylesheet';
                linkTag.href = cssFiles[k];
                head.appendChild(linkTag);
            }
            for (var k in scriptFiles) {
                scriptTag = document.createElement('script');
                scriptTag.type = 'text/javascript';
                if (typeof o.callback == "function") {
                    if (scriptTag.readyState) {  //IE
                        scriptTag.onreadystatechange = function() {
                            if (scriptTag.readyState == "loaded" || scriptTag.readyState == "complete") {
                                count++;
                                if (count == scriptFiles.length)
                                    o.callback.call();
                            }
                        };
                    } else { // other browsers
                        scriptTag.onload = function() {
                            count++;
                            if (count == scriptFiles.length)
                                o.callback.call();
                        }
                    }
                }
                scriptTag.src = scriptFiles[k];
                head.appendChild(scriptTag);
            }
        }
    };
}();

Ext.define('Xxam.util.HttpStateProvider', {
    extend: 'Ext.state.Provider',
    requires: ['Ext.state.Provider', 'Ext.Ajax'],
    alias: 'util.HttpProvider',
    config: {
        //userId: null,
        url: null,
        stateRestoredCallback: null
    },
    constructor: function(config) {

//        if (!config.userId) {
//            throw 'Xxam.util.HttpStateProvider: Missing userId';
//        }
        if (!config.url) {
            throw 'Xxam.util.HttpStateProvider: Missing url';
        }

        this.initConfig(config);
        var me = this;

        me.restoreState();
        me.callParent(arguments);
    },
    set: function(name, value) {

        var me = this;

        if (typeof value == 'undefined' || value === null) {
            me.clear(name);
            return;
        }

        me.saveStateForKey(name, value);
        me.callParent(arguments);
    },
    // private
    restoreState: function() {

        var me = this,
                callback = me.getStateRestoredCallback();

        Ext.Ajax.request({
            url: me.getUrl(),
            method: 'GET',
//            params: {
//                userId: me.getUserId(),
//            },
            success: function(response, options) {
                var result = JSON.parse(response.responseText.trim());
                for (var property in result) {
                    if (result.hasOwnProperty(property)) {
                        me.state[property] = me.decodeValue(result[property]);
                    }
                }

                if (callback) {
                    callback();
                }
            },
            failure: function() {
                console.log('Xxam.util.HttpStateProvider: restoreState failed', arguments);
                if (callback) {
                    callback();
                }
            }
        });
    },
    // private
    clear: function(name) {

        var me = this;

        me.clearStateForKey(name);
        me.callParent(arguments);
    },
    // private
    saveStateForKey: function(key, value) {

        var me = this;

        Ext.Ajax.request({
            url: me.getUrl(),
            method: 'POST',
            params: {
                //userId: me.getUserId(),
                key: key,
                value: me.encodeValue(value)
            },
            failure: function() {
                console.log('Xxam.util.HttpStateProvider: saveStateForKey failed', arguments);
            }
        });
    },
    // private
    clearStateForKey: function(key) {

        var me = this;

        Ext.Ajax.request({
            url: me.getUrl(),
            method: 'DELETE',
            params: {
                //userId: me.getUserId(),
                key: key
            },
            failure: function() {
                console.log('Xxam.util.HttpStateProvider: clearStateForKey failed', arguments);
            }
        });
    }
});

AUTOBAHN_DEBUG = true;

//
// This is the main layout definition.
//
Ext.onReady(function() {

    Ext.tip.QuickTipManager.init();

    var tabReorder = Ext.create('Ext.ux.TabReorderer');

    Ext.state.Manager.setProvider(new Xxam.util.HttpStateProvider({
        url: statefulservicepath, stateRestoredCallback: function() {
            ttttt = this;
            Ext.Array.sort(menu, function(a, b) {

                var astate = typeof (a.stateId) == 'undefined' ? 100 : Ext.state.Manager.get(a.stateId);
                var bstate = typeof (b.stateId) == 'undefined' ? 100 : Ext.state.Manager.get(b.stateId);
                var apos = typeof (astate) == 'undefined' ? 100 : astate.position;
                var bpos = typeof (bstate) == 'undefined' ? 100 : bstate.position;
                if (apos < bpos) {
                    return -1;
                } else if (apos > bpos) {
                    return 1;
                }
                return 0;
            });
            // Finally, build the main layout once all the pieces are ready.  This is also a good
            // example of putting together a full-screen BorderLayout within a Viewport.
            Ext.create('Ext.Viewport', {
                layout: 'border',
                //style: 'background-color: lightgray;',

                items: [{

                         id: 'header',
                         region: 'north',
                         tbar: {
                        weight: -100,
                        defaults: {
                            reorderable: true,
                            getState: function() {

                                var toolbar = this.up('toolbar');
                                var pos = 0;
                                for (var i = 0; i < toolbar.items.items.length; i++) {
                                    if (toolbar.items.items[i].stateId == this.stateId) {
                                        pos = i;
                                        break;
                                    }
                                }
                                return {
                                    position: pos
                                };
                            },
                            stateful: true
                        },
                        id: 'xxam_menu',
                        plugins: Ext.create('Ext.ux.BoxReorderer', {
                            listeners: {
                                drop: function(source, container, dragCmp, startIdx, idx, eOpts) {
                                    Ext.Array.each(container.items.items, function(item) {
                                        item.saveState();
                                    });


                                }
                            }
                        }),
                        items: []

                    },
                         plain: true,

                    },
                    {

                        region: 'center',
                        xtype: 'tabpanel', // TabPanel itself has no title
                        id: 'contenttabpanel',
                        //plain: true,
                        activeTab: 0, // First tab active by default
                        plugins: [tabReorder],
                        items: []


                    },
                    {
                        region: 'east',
                        layout: {
                            type: 'accordion',
                            multi: true
                        },
                        title: 'Comm Panel',
                        id: 'commpanel',
                        collapsible: true,
                        split: true,
                        width: 150,

                        items: []
                    }
                ],
                renderTo: Ext.getBody()
            });
            loadtab();
            Ext.getCmp('xxam_menu').add(menu);
        }}));


    var Event = function (publication, publisher, topic) {

        var self = this;

        self.publication = publication;
        self.publisher = publisher;
        self.topic = topic;
    };
    connection = new autobahn.Connection({
        url: 'wss://xxam.com/websocket',
        realm: realm,
        authmethods: ["chattoken"],
        onchallenge: function () {
            return chattoken;
        }
    });
    connection.onopen = function (session) {
        console.log('onopen');
        chatsession=session;
        Ext.Ajax.request({
            url: setchatidurl,
            method: 'POST',
            params: {
                sessionid: chatsession._id
            },

            success: function() {
               chatsession.subscribe("com.xxam.imap", function (topic, data) {
                   console.log('success',topic, data);
                   /*notify('Chat',data.message);*/
               },{'disclose_publisher': false,'disclose_me':false}).then(function (subscription) {
                    //sub1 = subscription;
               });
                for(var i=0; i<groups.length; i++){
                    subscribeChat(groups[i].toLowerCase());
                }
            }
    });
     };


     connection.open(); 
    
    
    
});

function subscribeChat(chatroom){
    console.log('subscribeChat',chatroom);
    var chatroomuri="com.xxam.chat."+chatroom.toLowerCase();
    var subscriptions=chatsession.subscriptions;
    var found=false;
    for(var i=0; i<subscriptions.length; i++){
        if (subscriptions[i].topic==chatroomuri){
            found=true;
            break;
        }
    }
    if (!found){
        chatsession.subscribe(chatroomuri, function (args, kwargs, details) {
            aargs=args;
            kkwargs=kwargs;
            ddetails=details;
            tttt=this;
            console.log('success',args, kwargs, details);
        },{'disclose_publisher': true,'disclose_me':true}).then(function (subscription) {
            createChatWindows();
        });
    }
}
function createChatWindows(){
    console.log('createChatWindows');
    var subscriptions=chatsession.subscriptions;
    console.log(subscriptions);
    var found=false;
    for(var i=0; i<subscriptions.length; i++){
        if (subscriptions[i][0].topic.substr(0,14)=="com.xxam.chat."){
            var chatroom=subscriptions[i][0].topic.substr(14);
            var commpanel=Ext.getCmp('commpanel');
            if (commpanel.down('#commpanel_chatroom_'+chatroom)==null){
                //create new chatroom-panel:
                var chatroompanel=Ext.create('Ext.panel.Panel', {
                    title: Ext.String.capitalize(chatroom),
                    id: 'commpanel_chatroom_'+chatroom,
                    html: '',
                    chatroom: subscriptions[i][0].topic,
                    bbar: [ {
                        xtype: 'textfield',
                        flex: 1,
                        enableKeyEvents: true,

                        listeners: {
                            keyup: function(field,e){
                                if (e.event.keyCode == 13){
                                    sendChatMessage(field,e);
                                }

                            }
                        } },{ xtype: 'button', text: 'Send', handler: sendChatMessage }]
                });
                commpanel.add(chatroompanel);

            }

        }
    }
}
function sendChatMessage(ele,e){
    console.log('sendChatMessage');
    tttt=ele;
    var message=ele.up().down('textfield').getValue();
    var chatroom=ele.up().up().getInitialConfig().chatroom;
    chatsession.publish(chatroom, [message], {}, {disclose_me: true,disclose_publisher:true} );
    ele.up().down('textfield').setValue('');


}

function loadtab() {
    var token = window.location.hash.substr(1);
    var tabfound = null;
    Ext.Array.each(Ext.getCmp('contenttabpanel').items.items, function(tab, index, tabpanels) {
        if (tab.loader != null && tab.loader.url.substring(xxam_core_homepage.length) == token) {
            tabfound = tab;
            return true;
        }
    });
    if (tabfound != null) {
        Ext.getCmp('contenttabpanel').setActiveTab(tabfound);
        return true;
    }
    if (token == '') return false;

    Ext.getCmp('contenttabpanel').add({
        'title': 'Loading...',
        closable: true,
        layout: 'fit',
        loader: {
            url: xxam_core_homepage + token,
            autoLoad: false,
            loadOnRender: true,
            loadMask: true,
            renderer: function(loader, response, active) {
                var success = true,
                        target = loader.getTarget(),
                        items = [];

                //<debug>
                if (!target.isContainer) {
                    Ext.Error.raise({
                        target: target,
                        msg: 'Components can only be loaded into a container'
                    });
                }
                //</debug>

                try {
                    items = Ext.decode(response.responseText);
                } catch (e) {
                    success = false;
                }

                if (success) {
                    target.suspendLayouts();
                    if (typeof (items.tabtitle) != 'undefined')
                        loader.getTarget().setTitle(items.tabtitle);
                    if (typeof (items.tabicon) != 'undefined')
                        loader.getTarget().setIcon(items.tabicon);
                    if (active.removeAll) {
                        target.removeAll();
                    }
                    if (typeof (items.getitems) != 'undefined')
                        target.add(items.getitems());

                    target.resumeLayouts(true);
                }
                return success;
            },
            failure: function(loader, response) {
                Ext.Msg.alert('Failed', response ? response : 'No response');
                loader.target.close();
            }
        },
        listeners: {
            beforeactivate: function(tab) {
                window.location.hash = '#' + tab.loader.url.substring(xxam_core_homepage.length);
                return true;
            }

        }
    });
    Ext.Array.each(Ext.getCmp('contenttabpanel').items.items, function(tab, index, tabpanels) {
        if (tab.loader != null && tab.loader.url.substring(xxam_core_homepage.length) == token) {
            Ext.getCmp('contenttabpanel').setActiveTab(tab);
            return true;
        }
    });
}


if ('onhashchange' in window) {
    window.onhashchange = function() {
        loadtab();
    }
}
function notify(title, body, icon) {
    var options = {
        dir: "ltr",
        icon: '/favicon.ico'
    };
    if (body != '')
        options.body = body;
    if (icon != '')
        options.icon = icon;


    if (!("Notification" in window)) {
        //alert("This browser does not support desktop notification");
    }
    else if (Notification.permission === "granted") {
        var notification = new Notification(title, options);
    }
    else if (Notification.permission !== 'denied') {
        Notification.requestPermission(function(permission) {
            if (!('permission' in Notification)) {
                Notification.permission = permission;
            }
            if (permission === "granted") {
                var notification = new Notification(title, options);
            }
        });
    }
}
windowhasfocus = true;
window.addEventListener('focus', function() {
    windowhasfocus = true;
});

window.addEventListener('blur', function() {
    windowhasfocus = false;
});

var allowedchars = '0123456789abcdef';
function generateprogressid() {
    var progressid = '';
    for (var i = 0; i < 32; i++) {
        progressid += allowedchars.charAt(Math.round(Math.random() * allowedchars.length))
    }
    return progressid;
}