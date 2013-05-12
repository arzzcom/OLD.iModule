<?php
REQUIRE_ONCE '../../config/default.conf.php';
?>
<html lang="ko" xmlns:ext="http://www.extjs.com/docs">
<head>
<meta http-equiv="Content-Type" content="text/html" charset="UTF-8" />
<title>iERP환경설정</title>
<link rel="shortcut icon" href="<?php echo $_ENV['dir']; ?>/module/erp/favicon.ico" />
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/php2js.php"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/script/extjs.js"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/erp/script/default.js"></script>
<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/css/extjs.css" type="text/css" title="style" />
<link rel="stylesheet" href="<?php echo $_ENV['dir']; ?>/module/erp/css/default.css" type="text/css" title="style" />
</head>
<body>

<script type="text/javascript">
Ext.QuickTips.init();
BasicLayoutClass = function() {
	return {
		init:function() {
			GlobalViewPort = this.viewport = new Ext.Viewport({
				id:"ModuleLayout",
				layout:"border",
				items:[this.CenterPanel = new ContentArea(this)]
			});
			this.viewport.doLayout();
			this.viewport.syncSize();
		}
	}
}();

function SetCameraList(data) {
	var camera = Ext.data.Record.create(["idx","name"]);
	for (var i=0, loop=data.length;i<loop;i++) {
		Ext.getCmp("ConfigForm").getForm().findField("cam").store.add(new camera({"idx":i.toString(),"name":data[i]}));
	}
}

ContentArea = function(viewport) {
	this.viewport = viewport;

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		layout:"fit",
		margins:"5",
		items:[
			new Ext.form.FormPanel({
				id:"ConfigForm",
				border:false,
				labelWidth:80,
				labelAlign:"right",
				items:[
					new Ext.form.FieldSet({
						title:"현장설정",
						style:"margin:10px;",
						autoWidth:true,
						items:[
							new Ext.form.ComboBox({
								fieldLabel:"현장선택",
								width:280,
								hiddenName:"workspace",
								typeAhead:true,
								triggerAction:"all",
								lazyRender:false,
								store:new Ext.data.Store({
									proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/erp/exec/Admin.get.php"}),
									reader:new Ext.data.JsonReader({
										root:"lists",
										totalProperty:"totalCount",
										fields:[{name:"idx",type:"int"},"title"]
									}),
									remoteSort:true,
									sortInfo:{field:"title",direction:"ASC"},
									baseParams:{"action":"workspace","get":"list","category":"working"}
								}),
								editable:false,
								mode:"local",
								displayField:"title",
								valueField:"idx",
								emptyText:"현장을 선택하세요.",
								listeners:{render:{fn:function(form) {
									form.getStore().load();
								}}}
							})
						]
					}),
					new Ext.form.FieldSet({
						title:"프로그램설정",
						style:"margin:10px;",
						autoWidth:true,
						items:[
							new Ext.form.ComboBox({
								name:"cam",
								fieldLabel:"웹카메라선택",
								triggerAction:"all",
								width:280,
								typeAhead:true,
								lazyRender:false,
								store:new Ext.data.SimpleStore({
									fields:["idx","name"],
									datas:[]
								}),
								editable:false,
								mode:"local",
								displayField:"name",
								valueField:"idx",
								emptyText:"웹카메라를 선택하세요."
							})
						]
					})
				]
			})
		],
		buttons:[
			new Ext.Button({
				text:"확인",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_tick.png",
				handler:function() {
					if (!Ext.getCmp("ConfigForm").getForm().findField("workspace").getValue()) {
						alert("현장을 선택하여 주십시오.");
						return false;
					}
					if (!Ext.getCmp("ConfigForm").getForm().findField("cam").getValue()) {
						alert("웹카메라를 선택하여 주십시오.");
						return false;
					}
					ConfigAction("save");
				}
			}),
			new Ext.Button({
				text:"취소",
				icon:"<?php echo $_ENV['dir']; ?>/module/erp/images/common/icon_cross.png",
				handler:function() {
					ConfigAction("close");
				}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});

Ext.EventManager.onDocumentReady(BasicLayoutClass.init, BasicLayoutClass, true);

Ext.form.XmlErrorReader = function() {
	Ext.form.XmlErrorReader.superclass.constructor.call(this,{record:"field",success:"@success"},["id", "msg"]);
};
Ext.extend(Ext.form.XmlErrorReader, Ext.data.XmlReader);

</script>

</body>
</html>