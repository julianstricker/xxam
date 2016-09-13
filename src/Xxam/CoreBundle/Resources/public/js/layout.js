Ext.Loader.setConfig({
    enabled: true,
    paths: {
        'Ext.ux.WebSocket': '/bundles/xxamcore/js/WebSocket.js' ,
        'Ext.ux.WebSocketManager': '/bundles/xxamcore/js/WebSocketManager.js'
    }
});
Ext.Loader.setPath('Ext.ux', '/assets/vendor/extjs/packages/ux/classic/src'); //'/assets/vendor/extjs/examples/ux');
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
                        overflowHandler: 'menu',
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
                        id: 'regioneast',
                        headerPosition: 'left',
                        header: false,
                        /*layout: {
                            type: 'accordion',
                            multi: true
                        },
                        title: 'Comm Panel',
                        id: 'commpanel',
                        collapsible: true,
                        //collapsed: true,
                        split: true,*/
                        width: 300,

                        tabPosition: 'left',
                        collapsible: true,
                        collapsed: true,
                        split: true,
                        xtype: 'tabpanel',
                        items: [{
                            xtype: 'panel',
                            layout: {
                                type: 'accordion',
                                multi: false
                            },
                            iconCls: 'x-fa fa-phone',
                            bodyStyle: 'margin:0; padding:0;',

                            title: 'Comm Panel',
                            id: 'commpanel',
                            items:[]
                        },{
                            xtype: 'panel',
                            iconCls: 'x-fa fa-user',
                            //bodyStyle: 'background:#ffc; padding:10px;',
                            hidden: true,
                            title: 'Contact',
                            id: 'contactpanel',
                            items:[]
                        }]
                    }
                ],
                renderTo: Ext.getBody()
            });




            loadtab();
            Ext.getCmp('xxam_menu').add(menu);

        }}));

    loadContactdata=function(email) {
        Ext.Ajax.request({
            url: xxam_core_homepage+'contact/getcontactdataforemail/'+email,

            success: function(response, opts) {
                var obj = Ext.decode(response.responseText);
                console.dir(obj);
                var contactpanel=Ext.getCmp('contactpanel');

                var t = new Ext.XTemplate([
                    '<div style="width: 100%;">',
                    '<tpl for="images">',
                    '  <div style="margin: auto; padding-top:20px; width: 120px"><img src="{origin}" style="width: 120px; height:120px; border: 5px solid white; border-radius: 100px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);" /></div>',
                    '</tpl>',
                    '  <div style="text-align: center;">{firstname} {lastname}</div>',
                    '  <div style="text-align: center;">{email}</div>',
                    '  <div style="text-align: center;">{organizationfunction} at {organizationname}</div>',
                    '  <div style="text-align: center;">{notes}</div>',
                    '<tpl for="communicationdatas">',
                    '  <div style="text-align: center;">{value}</div>',
                    '</tpl>',
                    '</div>'
                ]);
                contactpanel.update(t.apply(obj));
                contactpanel.setHidden(false);
                contactpanel.tab.setHidden(false);
                Ext.getCmp('regioneast').setCollapsed(false);
            },

            failure: function(response, opts) {
                console.log('server-side failure with status code ' + response.status);
                var contactpanel=Ext.getCmp('contactpanel');
                contactpanel.setHidden(true);
                contactpanel.tab.setHidden(true);
                Ext.getCmp('regioneast').setCollapsed(true);

            }
        });
        /*var tip = Ext.create('Ext.tip.ToolTip', {
            target: 'mailclient_maillist',
            delegate: Ext.getCmp('mailclient_maillist').getView().itemSelector,
            // Moving within the row should not hide the tip.
            trackMouse: false,
            bodyStyle: 'background:#FFF; padding:10px;',
            bodyBorder: false,
            // Render immediately so that tip.body can be referenced prior to the first show.
            renderTo: Ext.getBody(),
            listeners: {
                // Change content dynamically depending on which element triggered the show.
                beforeshow: function updateTipBody(tip) {
                    ttt = tip;
                    tip.update('Over company "' + Ext.getCmp('mailclient_maillist').getView().getRecord(tip.triggerElement).get('from') + '"');
                    tip.update('<div><img src="https://www.xing.com/image/e_6_d_a941a55af_6398154_1/julian-stricker-foto.192x192.jpg" style="border: 5px solid white; border-radius: 100px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);" /></div>');
                }
            }
        });*/
    }

    xxamws={
        subscriptions:[],
        online:{},
        incomingvideophonecallwins:{},
        subscriptiontargets:{},
        websocket: Ext.create ('Ext.ux.WebSocket', {
            url: 'wss://xxam.com/websocket' ,
            listeners: {
                open: function (ws) {
                    console.log ('The websocket is ready to use');
                    xxamws.subscriptions=[];
                    //ws.send ('This is a simple text');
                } ,
                close: function (ws) {
                    console.log ('The websocket is closed!');
                } ,
                error: function (ws, error) {
                    Ext.Error.raise (error);
                } ,
                message: function (ws, message) {
                    console.log ('A new message arrived: ' + message);
                    var message=Ext.JSON.decode(message);
                    var messagetype=message[0];
                    var messagedata=message[1];
                    var messagefrom=message[2];
                    switch(messagetype){
                        case 2: //Welcome:
                            xxamws.events.welcome(messagedata,messagefrom,ws);
                            break;
                        case 33: //Subscribed:
                            xxamws.events.subscribed(messagedata,messagefrom,ws);
                            break;
                        case 83: //Getonline Response:
                            xxamws.events.getonlineresponse(messagedata,messagefrom,ws);
                            break;
                        case 16: //Publish
                            xxamws.events.publish(messagedata,messagefrom,ws);
                            break;
                        case 86: //SUBSCRIBEDBROADCAST
                            xxamws.events.subscribedbroadcast(messagedata,messagefrom,ws);
                            break;
                        case 87: //UNSUBSCRIBEDBROADCAST
                            xxamws.events.unsubscribedbroadcast(messagedata,messagefrom,ws);
                            break;
                        case 96: //SIGNAL
                            xxamws.events.signal(messagedata,messagefrom,ws);
                            break
                        case 101: //VIDEOPHONECALL
                            xxamws.events.videophonecall(messagedata,messagefrom,ws);
                            break
                        case 102: //VIDEOPHONECALLACCEPT
                            xxamws.events.videophonecallaccept(messagedata,messagefrom,ws);
                            break
                        case 103: //VIDEOPHONECALLCANCEL
                            xxamws.events.videophonecallcancel(messagedata,messagefrom,ws);
                            break

                    }

                }
            }
        }),
        events:{
            welcome: function(data,from,ws){

                xxamws.sessionid=data.id;
                Ext.Ajax.request({
                    url: setchatidurl,
                    method: 'POST',
                    params: {
                        sessionid: xxamws.sessionid
                    },

                    success: function() {

                        xxamws.subscribe("com.xxam.imap");
                        for(var i=0; i<groups.length; i++){
                            xxamws.subscribe("com.xxam.chat."+groups[i].toLowerCase());
                        }
                    }
                });
            },
            subscribed: function(data,from,ws){
                xxamws.subscriptions.push(data.topic);
                xxamws.getonline(data.topic);
                createChatWindows();
            },
            getonlineresponse: function(data,from,ws){
                console.log('getonlineresponse');
                Ext.Object.each(data,function(key,value){
                    xxamws.online[key]=value;
                    updateOnlineStatus(key);
                });
            },
            subscribedbroadcast: function(data,from,ws){
                Ext.Object.each(data,function(key,value){
                    var chatroom = key.substr(14);

                    if (key.substr(0,14)=="com.xxam.chat.") {
                        var html = '';
                        var commpanel = Ext.getCmp('commpanel');
                        var chatroompanel = commpanel.down('#commpanel_chatroom_' + chatroom);
                    }
                    for (sessid in value) {
                        xxamws.online[key][sessid]=value[sessid];
                        if (key.substr(0,14)=="com.xxam.chat.") {
                            var html = xxamws.chatNoticeRenderer(xxamws.chatUserRenderer(sessid,key) + ' joined chat.');
                            chatroompanel.setHtml(chatroompanel.body.el.dom.lastChild.lastChild.innerHTML + html);
                            Ext.get(chatroompanel.body.el.dom.lastChild.lastChild.lastChild).hide().show(100);
                            chatroompanel.scrollBy(0, 1000, true);

                            Ext.Array.each(Ext.query('.chatuser'),function(userhtml){
                                Ext.get(userhtml).clearListeners();
                            });
                            Ext.Array.each(Ext.query('.isonline'),function(userhtml){
                                Ext.get(userhtml).addListener('click',onChatuserbuttonclicked);
                            });
                        }
                    }
                    if (typeof(xxamws.subscriptiontargets[key])!='undefined'){
                        Ext.Array.each(xxamws.subscriptiontargets[key],function(target){
                            if (typeof(target.onWsSubscribedbroadcast)=='function') target.onWsSubscribedbroadcast(value,ws);
                    });
                    }


                });

            },
            unsubscribedbroadcast: function(data,from,ws){
                Ext.Object.each(data,function(key,value){
                    var chatroom = key.substr(14);
                    if (key.substr(0,14)=="com.xxam.chat.") {
                        var html = '';
                        var commpanel = Ext.getCmp('commpanel');
                        var chatroompanel = commpanel.down('#commpanel_chatroom_' + chatroom);
                    }
                    for (sessid in value) {
                        if (key.substr(0,14)=="com.xxam.chat.") {
                            var html = xxamws.chatNoticeRenderer(value[sessid] + ' left chat.');

                            chatroompanel.setHtml(chatroompanel.body.el.dom.lastChild.lastChild.innerHTML + html);
                            Ext.get(chatroompanel.body.el.dom.lastChild.lastChild.lastChild).hide().show(100);
                            chatroompanel.scrollBy(0, 1000, true);
                            var userhtmls=Ext.query('.user_'+sessid);
                            Ext.Array.each(userhtmls,function(userhtml){
                                Ext.get(userhtml).clearListeners();
                                Ext.get(userhtml).removeCls('isonline');
                            });

                        }
                        delete xxamws.online[key][sessid];
                    }


                });

            },
            publish: function(data,from,ws){
                console.log('published');

                if (data.topic.substr(0,14)=="com.xxam.chat.") {
                    var chatroom = data.topic.substr(14);
                    console.log(chatroom);
                    var commpanel=Ext.getCmp('commpanel');
                    var chatroompanel = commpanel.down('#commpanel_chatroom_' + chatroom);
                    commpanel.expand();
                    chatroompanel.expand();
                    var html= xxamws.chatMessageRenderer(from,data.topic,data.message);
                    chatroompanel.setHtml(chatroompanel.body.el.dom.lastChild.lastChild.innerHTML + html);
                    Ext.get(chatroompanel.body.el.dom.lastChild.lastChild.lastChild).hide().show(100);
                    chatroompanel.scrollBy(0,1000,true);

                    Ext.Array.each(Ext.query('.chatuser'),function(userhtml){
                        Ext.get(userhtml).clearListeners();
                    });
                    Ext.Array.each(Ext.query('.isonline'),function(userhtml){
                        Ext.get(userhtml).addListener('click',onChatuserbuttonclicked);
                    });

                }else if (data.topic=="com.xxam.imap") {
                    console.log(data, from);
                    if (!windowhasfocus) {
                        notify('New mail found' ,'', '/bundles/xxammailclient/icons/32x32/email.png')
                    }
                    if (typeof(data.message)!= 'undefined' && typeof(data.message.recent)!='undefined' && data.message.recent > 0 ) {

                        Xxam.msg('New mail found', '', '/bundles/xxammailclient/icons/32x32/email.png');
                    }
                    if (Ext.getCmp('mailclient_maillist') != undefined) {
                        if(Ext.getCmp('mailclient_maillist').getStore().getProxy().extraParams.path==data.message.mailaccount_id.toString()){
                            Ext.getCmp('mailclient_maillist').getStore().load();
                        }
                    }
                }else{
                    if (typeof(xxamws.subscriptiontargets[data.topic])!='undefined'){
                        Ext.Array.each(xxamws.subscriptiontargets[data.topic],function(target){
                            target.onWsPublish(data,from,ws);
                        });
                    }
                }

            },
            signal: function(data,from,ws) {
                callreceivers=[from];
                if (!pc) startCall(false);

                var signal = JSON.parse(data.data);
                if (signal.sdp) {
                    pc.setRemoteDescription(new RTCSessionDescription(signal.sdp));
                } else {

                    if(signal.candidate!=null) pc.addIceCandidate(new RTCIceCandidate(signal.candidate));
                }
            },
            videophonecall: function(data,from,ws) { //received a videophonecall:
                console.log(from,data);
                var name=xxamws.getUsernameForId(from) || 'Unknown User'
                if (!name) name= 'Unknown User';
                var wintitle= 'Incoming call from ' + name + '...';
                //if (typeof(xxamws.incomingvideophonecallwins[from])=='undefined') {

                    xxamws.incomingvideophonecallwins[from] = Ext.create('Ext.window.Window', {
                        title: wintitle,
                        width: 320,
                        height: 120,
                        maximizable: true,
                        layout: 'fit',
                        closable: false,
                        closeAction: 'destroy',
                        videophonecallfrom: from,
                        listeners: {
                            close: function () {

                            }
                        },
                        bbar: [
                            {
                                xtype: 'button',
                                text: 'Accept',
                                scale: 'large',
                                icon: '/bundles/xxamcore/icons/32x32/call-start.png',
                                flex: 1,
                                disabled: false,
                                handler: function () {
                                    var win=this.up('window');
                                    xxamws.videophonecallaccept([win.videophonecallfrom]);
                                    win.close();
                                }
                            },
                            {
                                xtype: 'button',
                                text: 'Decline',
                                scale: 'large',
                                icon: '/bundles/xxamcore/icons/32x32/call-stop.png',
                                flex: 1,
                                disabled: false,
                                handler: function () {
                                    var win=this.up('window');
                                    console.log('close',win.videophonecallfrom);
                                    xxamws.videophonecallcancel([win.videophonecallfrom]);
                                    delete xxamws.incomingvideophonecallwins[win.videophonecallfrom];
                                    win.close();
                                }
                            }
                        ]


                    });



                xxamws.incomingvideophonecallwins[from].show();
            },
            videophonecallaccept: function(data,from,ws) {
                if (typeof videophonecallingwin != 'undefined' && videophonecallingwin.videophonecallreceivers.indexOf(from)>-1){
                    videophonecallingwin.close();
                    delete videophonecallingwin;
                    var name=xxamws.getUsernameForId(from) || 'Unknown User'
                    callUser(from,name);
                }

            },
            videophonecallcancel: function(data,from,ws) {
                if (typeof videophonecallingwin != 'undefined'){
                    videophonecallingwin.close();
                    delete videophonecallingwin;
                }
                if (typeof(xxamws.incomingvideophonecallwins[from])!='undefined') {
                    xxamws.incomingvideophonecallwins[from].close();
                    delete xxamws.incomingvideophonecallwins[from];
                }
            }

        },
        subscribe:function(topic,target){
            var message=[32,{topic:topic}]
            this.websocket.send(Ext.JSON.encode(message));
            if (target) {
                if (typeof(xxamws.subscriptiontargets[topic]) == 'undefined') {
                    xxamws.subscriptiontargets[topic] = []
                }
                xxamws.subscriptiontargets[topic].push(target);
            }

        },
        getonline:function(topic){
            var message=[82,{topic:topic}]
            this.websocket.send(Ext.JSON.encode(message));

        },
        publish:function(topic,message,receivers){
            var message=[16,{topic:topic,message:message,receivers:receivers}]
            this.websocket.send(Ext.JSON.encode(message));

        },
        signal:function(data,receivers){
            var message=[96,{data:data,receivers:receivers}]
            this.websocket.send(Ext.JSON.encode(message));

        },
        videophonecall:function(receivers){ //init a videophonecall:
            var message=[101,{receivers:receivers}]
            this.websocket.send(Ext.JSON.encode(message));
            var name=xxamws.getUsernameForId(receivers[0]) || 'Unknown User'

            var wintitle= 'Calling ' + name + '...';
            videophonecallingwin = Ext.create('Ext.window.Window', {
                title: wintitle,
                width: 200,
                height: 120,
                maximizable: true,
                layout: 'fit',
                closable: false,
                closeAction: 'destroy',
                videophonecallreceivers: receivers,
                modal: true,
                listeners: {
                    close: function () {
                        delete videophonecallingwin;
                    }
                },
                bbar: [
                    {
                        xtype: 'button',
                        text: 'Hangup',
                        scale: 'large',
                        icon: '/bundles/xxamcore/icons/32x32/call-stop.png',
                        flex: 1,
                        disabled: false,
                        handler: function () {

                            var win=this.up('window');
                            xxamws.videophonecallcancel(win.videophonecallreceivers);
                            videophonecallingwin.close();
                            //delete videophonecallingwin;
                        }
                    }
                ]


            }).show();

        },
        videophonecallaccept:function(receivers){ //accept a videophonecall:
            var message=[102,{receivers:receivers}]
            this.websocket.send(Ext.JSON.encode(message));
        },
        videophonecallcancel:function(receivers){ //cancel a videophonecall:
            var message=[103,{receivers:receivers}]
            this.websocket.send(Ext.JSON.encode(message));
        },
        chatMessageRenderer:function(from,topic,message){
            var leftright=(from==xxamws.sessionid ? 'right' : 'left');
            var html = '<div><div style="text-align: '+leftright+'">' + xxamws.chatUserRenderer(from,topic) + '</div><div class="chatbubble'+leftright+'">' + message  + '</div></div>';
            return html;
        },
        chatNoticeRenderer:function(message){
            var html = '<div class="chatnotice">' + message  + '</div>';
            return html;
        },
        chatUserRenderer:function(userid,topic){
            var status='isonline';
            if (userid==xxamws.sessionid) status='me';
            var html = '<div class="chatuser user_'+userid+' '+status+'">' + xxamws.online[topic][userid]  + '</div>';
            return html;
        },
        isUserOnline: function(userid){
            for(var topic in xxamws.online){
                if (xxamws.online.hasOwnProperty(topic)) {
                    if (typeof(xxamws.online[topic][userid])!='undefined'){
                        return true;
                    }
                }
            }
            return false;
        },
        getUsernameForId:function(userid){
            for(var topic in xxamws.online){
                if (xxamws.online.hasOwnProperty(topic)) {
                    if (typeof(xxamws.online[topic][userid])!='undefined'){
                        return xxamws.online[topic][userid];
                    }
                }
            }
            return false;
        },
        init:function(){

        }
    }


    
});



function createChatWindows(){
    console.log('createChatWindows');
    var subscriptions=xxamws.subscriptions;
    console.log(subscriptions);
    var found=false;
    for(var i=0; i<subscriptions.length; i++){
        if (subscriptions[i].substr(0,14)=="com.xxam.chat."){
            var chatroom=subscriptions[i].substr(14);
            var commpanel=Ext.getCmp('commpanel');

            if (commpanel.down('#commpanel_chatroom_'+chatroom)==null){
                //create new chatroom-panel:
                var chatroompanel=Ext.create('Ext.panel.Panel', {
                    title: Ext.String.capitalize(chatroom),
                    id: 'commpanel_chatroom_'+chatroom,
                    html: ' ',
                    autoScroll: true,
                    chatroom: subscriptions[i],
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

function updateOnlineStatus(topic){
    var commpanel=Ext.getCmp('commpanel');

    if (topic.substr(0,14)=="com.xxam.chat.") {
        var chatroom = topic.substr(14);
        console.log(chatroom);
        var chatroompanel = commpanel.down('#commpanel_chatroom_' + chatroom);
        var html=typeof chatroompanel.body.el.dom.lastChild.lastChild.innerHTML != 'undefined' ? chatroompanel.body.el.dom.lastChild.lastChild.innerHTML : '';
        var usershtml=[];
        Ext.Object.each(xxamws.online[topic],function(key,value){
            usershtml.push(xxamws.chatUserRenderer(key,topic));
        });
        html += xxamws.chatNoticeRenderer('Users: ' + usershtml.join(', '));
        chatroompanel.setHtml(html);

        Ext.Array.each(Ext.query('.chatuser'),function(userhtml){
            Ext.get(userhtml).clearListeners();
        });
        Ext.Array.each(Ext.query('.isonline'),function(userhtml){
            Ext.get(userhtml).addListener('click',onChatuserbuttonclicked);
        });

    }

}

function onChatuserbuttonclicked(){
    var userid=null;
    Ext.Array.each(this.dom.classList,function(classname){
        if (classname.substr(0,5)=='user_'){
            userid=parseInt(classname.substr(5));
        }
    });
    xxamws.videophonecall([userid]);
    console.log(userid);
}

function sendChatMessage(ele,e){
   var message=ele.up().down('textfield').getValue();
    if (message!=''){
        var chatroom=ele.up().up().getInitialConfig().chatroom;
        xxamws.publish(chatroom, message,[]);
        var commpanel=Ext.getCmp('commpanel');
        var chatroompanel = commpanel.down('#commpanel_chatroom_' + chatroom.substr(14));
        commpanel.expand();
        chatroompanel.expand();
        var html= xxamws.chatMessageRenderer(xxamws.sessionid,chatroom,message);
        chatroompanel.setHtml(chatroompanel.body.el.dom.lastChild.lastChild.innerHTML + html);
        Ext.get(chatroompanel.body.el.dom.lastChild.lastChild.lastChild).hide().show(100);

        chatroompanel.scrollBy(0,1000,true);
        ele.up().down('textfield').setValue('');

        Ext.Array.each(Ext.query('.chatuser'),function(userhtml){
            Ext.get(userhtml).clearListeners();
        });
        Ext.Array.each(Ext.query('.isonline'),function(userhtml){
            Ext.get(userhtml).addListener('click',onChatuserbuttonclicked);
        });
    }
}
function createVideophonewin(isCaller){
    if (isCaller){
        var name=callreceivername;
        var wintitle= 'Calling ' + name + '...';
    }else{
        var name=xxamws.getUsernameForId(callreceivers[0]) | '?';
        var wintitle= 'Incoming call from ' + name + '...';

    }
    if (typeof(chatwin)=='undefined') {
        chatwin = Ext.create('Ext.window.Window', {
            title: wintitle,
            height: 380,
            width: 400,
            maximizable: true,
            layout: 'fit',
            html: '<video id="remoteVideo" style="width:400px; height:300px;" autoplay></video><div id="localVideoContainer" class="localvideocontainer" style="width:400px; height:300px; right:0; bottom:0"><video id="localVideo" style="width:400px; height:300px" autoplay muted></video></div>',
            closeAction: 'hide',
            listeners: {
                resize: function (win, width, height, oOpts) {

                    console.log(width, height);
                    if (videochatStartTime){
                        var remoteVideo = Ext.fly('remoteVideo');
                        remoteVideo.setWidth(width);
                        remoteVideo.setHeight(height);
                    }else{
                        var localVideo = Ext.fly('localVideo');
                        localVideo.setWidth(width);
                        localVideo.setHeight(height);
                        var localVideoContainer = Ext.fly('localVideoContainer');
                        localVideoContainer.setWidth(width);
                        localVideoContainer.setHeight(height);
                    }


                },
                close: function () {
                    endCall();
                }

            },
            bbar: [
                {
                    xtype: 'button',
                    id: 'hangupbutton',
                    text: 'Hangup',
                    scale: 'large',
                    icon: '/bundles/xxamcore/icons/32x32/call-stop.png',
                    flex: 1,
                    disabled: true,
                    handler: function () {
                        endCall();
                    }
                }
            ]


        });

    }
    chatwin.setTitle(wintitle);
    chatwin.show();

}
function callUser(sessionid,name){

    callreceivers=[sessionid];
    callreceivername=name;
    startCall(true);
}


navigator.mediaDevices = navigator.mediaDevices || ((navigator.mozGetUserMedia || navigator.webkitGetUserMedia) ? {
        getUserMedia: function(c) {
            return new Promise(function(y, n) {
                (navigator.mozGetUserMedia ||
                navigator.webkitGetUserMedia).call(navigator, c, y, n);
            });
        }
    } : null);

if (!navigator.mediaDevices) {
    console.log("getUserMedia() not supported.");
    //return;
}

var videochatStartTime;
var pc;

// Helper functions
function endCall() {
    var videos = document.getElementsByTagName("video");
    for (var i = 0; i < videos.length; i++) {
        videos[i].pause();
        if (typeof(videos[i].srcObject) != 'undefined' && typeof(videos[i].srcObject.stop) == 'function') videos[i].srcObject.stop();
    }


    if (pc) {
        pc.close();
        pc = null;
    }
    chatwin.hide();
    videochatStartTime = null;
}

function videochaterror(err){
    endCall();
    console.log(err);
    Xxam.msg('Videochat error',err.name);
}
// run start(true) to initiate a call
function startCall(isCaller) {

    createVideophonewin(isCaller);

    console.log('startCall');
    Ext.getCmp('hangupbutton').setDisabled(false);
    var pc_config = {"iceServers": [{"url": "stun:stun.l.google.com:19302"}]};
    pc = new RTCPeerConnection(pc_config);


    // send any ice candidates to the other peer
    pc.onicecandidate = function (evt) {
        //signalingChannel.send(JSON.stringify({ "candidate": evt.candidate }));
        console.log('onicecandidate',evt.candidate);
        if (evt.candidate!=null) xxamws.signal(JSON.stringify({ "candidate": evt.candidate }),callreceivers);
    };

    // once remote stream arrives, show it in the remote video element
    pc.onaddstream = function (evt) {
        console.log('onaddstream',evt);
        //remoteVideo.src = URL.createObjectURL(evt.stream);
        remoteVideo.srcObject = evt.stream;
        var localVideoContainer = Ext.fly('localVideoContainer');
        var localVideo = localVideoContainer.down('video');
        localVideoContainer.animate({duration: 500, to: {width: 100, height: 80}});
        localVideo.animate({duration: 500, to: {width: 100, height: 80}});
        videochatStartTime = window.performance.now();
    };
    pc.onremotehangup = function(evt){
        endCall();
    }
    pc.onerror = videochaterror;

    // get the local stream, show it in the local video element and send it
    navigator.mediaDevices.getUserMedia({ "audio": true, "video": {facingMode: "user"} }).then(function (stream) {
        //localVideo.src = URL.createObjectURL(stream);
        console.log('iscaller: ',isCaller);
        localVideo.srcObject = stream;
        pc.addStream(stream);

        if (isCaller) {
            pc.createOffer().then(gotDescription).catch(videochaterror);
        }else {

            pc.createAnswer().then(gotDescription).catch(videochaterror);
        }
        function gotDescription(desc) {
            pc.setLocalDescription(desc);
            xxamws.signal(JSON.stringify({ "sdp": desc }),callreceivers);
        }
    }).catch(function(e) {
        console.log(e);
        alert('getUserMedia() error: ' + e.name);
    });
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
                    if (typeof (items.iconCls) != 'undefined')
                        loader.getTarget().setIconCls(items.iconCls);
                    if (active.removeAll) {
                        target.removeAll();
                    }
                    iii=items;
                    ttt=target;
                    if (typeof (items.getitems) != 'undefined')
                        target.add(items.getitems());

                    target.resumeLayouts(true);
                }
                return success;
            },
            failure: function(loader, response) {
                Ext.create('Ext.window.MessageBox', {
                    resizable: true,
                    scrollable: true,
                    maxWidth: 1000
                }).show({
                    title:'Failed',
                    message: response ? response : 'No response',
                    buttons: Ext.Msg.OK,
                    icon: Ext.Msg.ALERT
                });

                loader.target.close();
            }
        },
        listeners: {
            beforeactivate: function(tab) {
                window.location.hash = '#' + tab.loader.url.substring(xxam_core_homepage.length);
                return true;
            },
            beforeclose: function(tab) {
                if (tab.up('tabpanel').items.length==1) window.location.hash = '#';
                //window.location.hash = '#' + tab.loader.url.substring(xxam_core_homepage.length);
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

function showlogentries(entityname,id,editroute) {
    if (typeof(LogentrieslistModel) == 'undefined') {
        Ext.define('LogentrieslistModel', {
            extend: 'Ext.data.Model',
            fields: [
                {name: 'logged_at', type: 'date', dateFormat:"Y-m-d H:i:s"},
                {name: 'action', type: 'string'},
                {name: 'object_id', type: 'int'},
                {name: 'username', type: 'string'},
                {name: 'version', type: 'int'}
            ],
            idProperty: 'version'
        });
    }
    var logentrieslist_store = Ext.create('Ext.data.Store', {
        model: 'LogentrieslistModel',
        remoteSort: false,
        pageSize: 100,

        proxy: {
            type: 'ajax',
            url: xxam_core_getlogentries,
            extraParams:{
                entityname: entityname,
                id: id
            },
            reader: {
                type: 'json',
                rootProperty: 'logentries',

            }
        },
        autoLoad: true
    });

    Ext.create('Ext.window.Window', {
        title: logentrieswindowtitle,
        height: 200,
        width: 500,
        layout: 'fit',
        items: {  // Let's put an empty grid in just to illustrate fit layout
            xtype: 'grid',
            border: false,
            columns: [
                {"text":"Id","dataIndex":"id","filter":{"type":"number"}, flex: 1, "hidden":true},
                {'text': 'Version','dataIndex':'version',"filter":{"type":"number"}, flex: 1, "hidden":false},
                {"text":"Time","dataIndex":"logged_at","xtype":"datecolumn", flex: 2, "format":"Y-m-d H:i:s","hidden":false},
                {'text': 'Action','dataIndex':'action',"filter":{"type":"string"}, flex: 2, "hidden":false},
                {'text': 'User','dataIndex':'username',"filter":{"type":"string"}, flex: 2, "hidden":false},
                {
                    menuDisabled: true,
                    sortable: false,
                    xtype: 'actioncolumn',
                    width: 100,
                    flex: 1,
                    items: [{
                        //icon: '/bundles/xxamdynmod/icons/16x16/user_edit.png',
                        iconCls: 'x-fa fa-edit',
                        tooltip: 'get version',
                        handler: function (grid, rowIndex, colIndex) {
                            var rec = grid.getStore().getAt(rowIndex);
                            window.location.href = '#'+editroute+'/' + rec.get('object_id') + '?version=' + rec.get('version');
                        }
                    }]
                }
            ],
            store: logentrieslist_store
        }
    }).show();

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