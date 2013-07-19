Ext.define("Ext.ux.desktop.Desktop",{extend:"Ext.panel.Panel",alias:"widget.desktop",uses:"Ext.util.MixedCollection Ext.menu.Menu Ext.view.View Ext.window.Window Ext.ux.desktop.TaskBar Ext.ux.desktop.Wallpaper".split(" "),activeWindowCls:"ux-desktop-active-win",inactiveWindowCls:"ux-desktop-inactive-win",lastActiveWindow:null,border:!1,html:"&#160;",layout:"fit",xTickSize:1,yTickSize:1,app:null,shortcuts:null,shortcutItemSelector:"div.ux-desktop-shortcut",shortcutTpl:['<tpl for=".">','<div class="ux-desktop-shortcut" id="{name}-shortcut">','<div class="ux-desktop-shortcut-icon {iconCls}" style="background:url({icon});">','<img src="',Ext.BLANK_IMAGE_URL,'" title="{name}">',"</div>",'<span class="ux-desktop-shortcut-text">{name}</span>',"</div>","</tpl>",'<div class="x-clear"></div>'],taskbarConfig:null,windowMenu:null,initComponent:function(){this.windowMenu=new Ext.menu.Menu(this.createWindowMenu());this.bbar=this.taskbar=new Ext.ux.desktop.TaskBar(this.taskbarConfig);this.taskbar.windowMenu=this.windowMenu;this.windows=new Ext.util.MixedCollection;this.contextMenu=new Ext.menu.Menu(this.createDesktopMenu());this.items=[{xtype:"wallpaper",id:this.id+"_wallpaper"},this.createDataView()];this.callParent();this.shortcutsView=this.items.getAt(1);this.shortcutsView.on("itemclick",this.onShortcutItemClick,this);var a=this.wallpaper;this.wallpaper=this.items.getAt(0);a&&this.setWallpaper(a,this.wallpaperStretch)},afterRender:function(){this.callParent()},createDataView:function(){return{id:"test",xtype:"dataview",overItemCls:"x-view-over",trackOver:!0,itemSelector:this.shortcutItemSelector,store:this.shortcuts,layout:"fit",autoScroll:0,style:{position:"absolute"},x:0,y:0,tpl:new Ext.XTemplate(this.shortcutTpl),defaultAlign:"tr-br",listeners:{viewready:{fn:function(a){for(var b=8,c=8,d=0;d<a.getStore().getCount();d++)b+105>a.getHeight()&&(b=8,c=c+105),Ext.get(a.getStore().getAt(d).get("name")+"-shortcut").animate({to:{x:c,y:b}}),b+=105}},resize:{fn:function(a){for(var b=8,c=8,d=0;d<a.getStore().getCount();d++){b+105>a.getHeight()&&(b=8,c=c+105);var e=Ext.get(a.getStore().getAt(d).get("name")+"-shortcut");e&&(e.animate({to:{x:c,y:b}}),b+=105)}}}}}},createDesktopMenu:function(){var a={items:this.contextMenuItems||[]};a.items.length&&a.items.push("-");a.items.push({text:"Tile",handler:this.tileWindows,scope:this,minWindows:1},{text:"Cascade",handler:this.cascadeWindows,scope:this,minWindows:1});return a},createWindowMenu:function(){return{defaultAlign:"br-tr",items:[{text:"Restore",handler:this.onWindowMenuRestore,scope:this},{text:"Minimize",handler:this.onWindowMenuMinimize,scope:this},{text:"Maximize",handler:this.onWindowMenuMaximize,scope:this},"-",{text:"Close",handler:this.onWindowMenuClose,scope:this}],listeners:{beforeshow:this.onWindowMenuBeforeShow,hide:this.onWindowMenuHide,scope:this}}},onDesktopMenu:function(a){var b=this.contextMenu;a.stopEvent();if(!b.rendered)b.on("beforeshow",this.onDesktopMenuBeforeShow,this);b.showAt(a.getXY());b.doConstrain()},onDesktopMenuBeforeShow:function(a){var b=this.windows.getCount();a.items.each(function(a){a.setDisabled(b<(a.minWindows||0))})},onShortcutItemClick:function(a,b){var c=this.app.getModule(b.data.module);(c=c&&c.createWindow())&&this.restoreWindow(c)},onWindowClose:function(a){this.windows.remove(a);this.taskbar.removeTaskButton(a.taskButton);this.updateActiveWindow()},onWindowMenuBeforeShow:function(a){var b=a.items.items;a=a.theWin;b[0].setDisabled(!0!==a.maximized&&!0!==a.hidden);b[1].setDisabled(!0===a.minimized);b[2].setDisabled(!0===a.maximized||!0===a.hidden)},onWindowMenuClose:function(){this.windowMenu.theWin.close()},onWindowMenuHide:function(a){a.theWin=null},onWindowMenuMaximize:function(){var a=this.windowMenu.theWin;a.maximize();a.toFront()},onWindowMenuMinimize:function(){this.windowMenu.theWin.minimize()},onWindowMenuRestore:function(){this.restoreWindow(this.windowMenu.theWin)},getWallpaper:function(){return this.wallpaper.wallpaper},setTickSize:function(a,b){var c=this.xTickSize=a,d=this.yTickSize=1<arguments.length?b:c;this.windows.each(function(a){var b=a.dd;a=a.resizer;b.xTickSize=c;b.yTickSize=d;a.widthIncrement=c;a.heightIncrement=d})},setWallpaper:function(a,b){this.wallpaper.setWallpaper(a,b);return this},cascadeWindows:function(){var a=0,b=0;this.getDesktopZIndexManager().eachBottomUp(function(c){c.isWindow&&(c.isVisible()&&!c.maximized)&&(c.setPosition(a,b),a+=20,b+=20)})},createWindow:function(a,b){var c=this,d,e=Ext.applyIf(a||{},{stateful:!1,isWindow:!0,constrainHeader:!0,minimizable:!0,maximizable:!0});b=b||Ext.window.Window;d=c.add(new b(e));c.windows.add(d);d.taskButton=c.taskbar.addTaskButton(d);d.animateTarget=d.taskButton.el;d.on({activate:c.updateActiveWindow,beforeshow:c.updateActiveWindow,deactivate:c.updateActiveWindow,minimize:c.minimizeWindow,destroy:c.onWindowClose,scope:c});d.on({boxready:function(){d.dd.xTickSize=c.xTickSize;d.dd.yTickSize=c.yTickSize;d.resizer&&(d.resizer.widthIncrement=c.xTickSize,d.resizer.heightIncrement=c.yTickSize)},single:!0});d.doClose=function(){d.doClose=Ext.emptyFn;d.el.disableShadow();d.el.fadeOut({listeners:{afteranimate:function(){d.destroy()}}})};return d},getActiveWindow:function(){var a=null,b=this.getDesktopZIndexManager();b&&b.eachTopDown(function(b){return b.isWindow&&!b.hidden?(a=b,!1):!0});return a},getDesktopZIndexManager:function(){var a=this.windows;return a.getCount()&&a.getAt(0).zIndexManager||null},getWindow:function(a){return this.windows.get(a)},minimizeWindow:function(a){a.minimized=!0;a.hide()},restoreWindow:function(a){a.isVisible()?(a.restore(),a.toFront()):a.show();return a},tileWindows:function(){var a=this,b=a.body.getWidth(!0),c=a.xTickSize,d=a.yTickSize,e=d;a.windows.each(function(f){if(f.isVisible()&&!f.maximized){var g=f.el.getWidth();c>a.xTickSize&&c+g>b&&(c=a.xTickSize,d=e);f.setPosition(c,d);c+=g+a.xTickSize;e=Math.max(e,d+f.el.getHeight()+a.yTickSize)}})},updateActiveWindow:function(){var a=this.getActiveWindow(),b=this.lastActiveWindow;if(a!==b){b&&(b.el.dom&&(b.addCls(this.inactiveWindowCls),b.removeCls(this.activeWindowCls)),b.active=!1);if(this.lastActiveWindow=a)a.addCls(this.activeWindowCls),a.removeCls(this.inactiveWindowCls),a.minimized=!1,a.active=!0;this.taskbar.setActiveButton(a&&a.taskButton)}}});Ext.define("Ext.ux.desktop.Module",{mixins:{observable:"Ext.util.Observable"},constructor:function(a){this.mixins.observable.constructor.call(this,a);this.init()},init:Ext.emptyFn});Ext.define("Ext.ux.desktop.ShortcutModel",{extend:"Ext.data.Model",fields:[{name:"name"},{name:"iconCls"},{name:"icon"},{name:"module"}]});Ext.define("Ext.ux.desktop.StartMenu",{extend:"Ext.panel.Panel",requires:["Ext.menu.Menu","Ext.toolbar.Toolbar"],ariaRole:"menu",cls:"x-menu ux-start-menu",defaultAlign:"bl-tl",iconCls:"user",floating:!0,shadow:!0,width:300,initComponent:function(){var a=this;a.menu=new Ext.menu.Menu({cls:"ux-start-menu-body",border:!1,floating:!1,items:a.menu});a.menu.layout.align="stretch";a.items=[a.menu];a.layout="fit";Ext.menu.Manager.register(a);a.callParent();a.toolbar=new Ext.toolbar.Toolbar(Ext.apply({dock:"right",cls:"ux-start-menu-toolbar",vertical:!0,width:100},a.toolConfig));a.toolbar.layout.align="stretch";a.addDocked(a.toolbar);delete a.toolItems;a.on("deactivate",function(){a.hide()})},addMenuItem:function(){var a=this.menu;a.add.apply(a,arguments)},addToolItem:function(){var a=this.toolbar;a.add.apply(a,arguments)},showBy:function(a,b,c){this.floating&&a&&(this.layout.autoSize=!0,this.show(),a=a.el||a,a=this.el.getAlignToXY(a,b||this.defaultAlign,c),this.floatParent&&(b=this.floatParent.getTargetEl().getViewRegion(),a[0]-=b.x,a[1]-=b.y),this.showAt(a),this.doConstrain());return this}});Ext.define("Ext.ux.desktop.TaskBar",{extend:"Ext.toolbar.Toolbar",requires:["Ext.button.Button","Ext.resizer.Splitter","Ext.menu.Menu","Ext.ux.desktop.StartMenu"],alias:"widget.taskbar",cls:"ux-taskbar",startBtnText:"\uc2dc\uc791",startBtnIcon:"",initComponent:function(){this.startMenu=new Ext.ux.desktop.StartMenu(this.startConfig);this.quickStart=new Ext.toolbar.Toolbar(this.getQuickStart());this.windowBar=new Ext.toolbar.Toolbar(this.getWindowBarConfig());this.tray=new Ext.toolbar.Toolbar(this.getTrayConfig());this.items=[{xtype:"button",cls:"ux-start-button",icon:this.startBtnIcon,menu:this.startMenu,menuAlign:"bl-tl",text:this.startBtnText},this.quickStart,{xtype:"splitter",html:"&#160;",height:14,width:2,cls:"x-toolbar-separator x-toolbar-separator-horizontal"},this.windowBar,"-",this.tray];this.callParent()},afterLayout:function(){this.callParent();this.windowBar.el.on("contextmenu",this.onButtonContextMenu,this)},getQuickStart:function(){var a=this,b={minWidth:20,width:60,items:[],enableOverflow:!0};Ext.each(this.quickStart,function(c){b.items.push({tooltip:{text:c.name,align:"bl-tl"},overflowText:c.name,iconCls:c.iconCls,module:c.module,handler:a.onQuickStartClick,scope:a})});return b},getTrayConfig:function(){var a={width:80,items:this.trayItems};delete this.trayItems;return a},getWindowBarConfig:function(){return{flex:1,cls:"ux-desktop-windowbar",items:["&#160;"],layout:{overflowHandler:"Scroller"}}},getWindowBtnFromEl:function(a){return this.windowBar.getChildByElement(a)||null},onQuickStartClick:function(a){if(a=this.app.getModule(a.module))a=a.createWindow(),a.show()},onButtonContextMenu:function(a){var b=a.getTarget(),c=this.getWindowBtnFromEl(b);c&&(a.stopEvent(),this.windowMenu.theWin=c.win,this.windowMenu.showBy(b))},onWindowBtnClick:function(a){a=a.win;a.minimized||a.hidden?a.show():a.active?a.minimize():a.toFront()},addTaskButton:function(a){a={icon:a.icon,iconCls:a.iconCls,enableToggle:!0,toggleGroup:"all",width:140,margins:"0 2 0 3",text:Ext.util.Format.ellipsis(a.title,20),listeners:{click:this.onWindowBtnClick,scope:this},win:a};a=this.windowBar.add(a);a.toggle(!0);return a},removeTaskButton:function(a){var b;this.windowBar.items.each(function(c){c===a&&(b=c);return!b});b&&this.windowBar.remove(b);return b},setActiveButton:function(a){a?a.toggle(!0):this.windowBar.items.each(function(a){a.isButton&&a.toggle(!1)})}});Ext.define("Ext.ux.desktop.TrayClock",{extend:"Ext.toolbar.TextItem",alias:"widget.trayclock",cls:"ux-desktop-trayclock",html:"&#160;",timeFormat:"g:i A",tpl:"{time}",initComponent:function(){this.callParent();"string"==typeof this.tpl&&(this.tpl=new Ext.XTemplate(this.tpl))},afterRender:function(){Ext.Function.defer(this.updateTime,100,this);this.callParent()},onDestroy:function(){this.timer&&(window.clearTimeout(this.timer),this.timer=null);this.callParent()},updateTime:function(){var a=Ext.Date.format(new Date,this.timeFormat),a=this.tpl.apply({time:a});this.lastText!=a&&(this.setText(a),this.lastText=a);this.timer=Ext.Function.defer(this.updateTime,1E4,this)}});Ext.define("Ext.ux.desktop.App",{mixins:{observable:"Ext.util.Observable"},requires:["Ext.container.Viewport","Ext.ux.desktop.Desktop"],isReady:!1,modules:null,useQuickTips:!0,constructor:function(a){this.addEvents("ready","beforeunload");this.mixins.observable.constructor.call(this,a);if(Ext.isReady)Ext.Function.defer(this.init,10,this);else Ext.onReady(this.init,this)},init:function(){var a;this.useQuickTips&&Ext.QuickTips.init();(this.modules=this.getModules())&&this.initModules(this.modules);a=this.getDesktopConfig();this.desktop=new Ext.ux.desktop.Desktop(a);this.viewport=new Ext.container.Viewport({layout:"fit",items:[this.desktop]});Ext.EventManager.on(window,"beforeunload",this.onUnload,this);this.isReady=!0;this.fireEvent("ready",this)},getDesktopConfig:function(){var a={app:this,taskbarConfig:this.getTaskbarConfig()};Ext.apply(a,this.desktopConfig);return a},getModules:Ext.emptyFn,getStartConfig:function(){var a=this,b={app:a,menu:[]},c;Ext.apply(b,a.startConfig);Ext.each(a.modules,function(d){if(c=d.launcher)c.handler=c.handler||Ext.bind(a.createWindow,a,[d]),b.menu.push(d.launcher)});return b},createWindow:function(a){a.createWindow().show()},getTaskbarConfig:function(){var a={app:this,startConfig:this.getStartConfig()};Ext.apply(a,this.taskbarConfig);return a},initModules:function(a){var b=this;Ext.each(a,function(a){a.app=b})},getModule:function(a){for(var b=this.modules,c=0,d=b.length;c<d;c++){var e=b[c];if(e.id==a||e.appType==a)return e}return null},onReady:function(a,b){if(this.isReady)a.call(b,this);else this.on({ready:a,scope:b,single:!0})},getDesktop:function(){return this.desktop},onUnload:function(a){!1===this.fireEvent("beforeunload",this)&&a.stopEvent()}});Ext.define("Ext.ux.desktop.Wallpaper",{extend:"Ext.Component",alias:"widget.wallpaper",cls:"ux-wallpaper",html:'<img src="'+Ext.BLANK_IMAGE_URL+'">',stretch:!1,wallpaper:null,stateful:!0,stateId:"desk-wallpaper",afterRender:function(){this.callParent();this.setWallpaper(this.wallpaper,this.stretch)},applyState:function(){var a=this.wallpaper;this.callParent(arguments);a!=this.wallpaper&&this.setWallpaper(this.wallpaper)},getState:function(){return this.wallpaper&&{wallpaper:this.wallpaper}},setWallpaper:function(a,d){var b,c;this.stretch=!1!==d;this.wallpaper=a;this.rendered&&(b=this.el.dom.firstChild,!a||a==Ext.BLANK_IMAGE_URL?Ext.fly(b).hide():this.stretch?(b.src=a,this.el.removeCls("ux-wallpaper-tiled"),Ext.fly(b).setStyle({width:"100%",height:"100%"}).show()):(Ext.fly(b).hide(),c="url("+a+")",this.el.addCls("ux-wallpaper-tiled")),this.el.setStyle({backgroundImage:c||""}),this.stateful&&this.saveState());return this}});