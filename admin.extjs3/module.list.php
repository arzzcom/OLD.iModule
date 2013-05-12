<script type="text/javascript">
function PermissionHelp() {
	if (Ext.getCmp("PermissionHelpWindow")) return;

	new Ext.Window({
		id:"PermissionHelpWindow",
		title:"권한설정 도움말",
		width:500,
		height:400,
		layout:"fit",
		resizeable:false,
		items:[
			new Ext.Panel({
				border:false,
				autoScroll:true,
				style:"background:#FFFFFF;font-family:돋움; font-size:11px; line-height:1.6;",
				html:'<div style="padding:5px;"><div class="boxDefault">권한설정은 해당 카테고리에 게시물을 작성할 수 있는 권한을 설정하는 것입니다. 이 권한설정은 산술적 수식으로 표현됩니다. 아래의 변수값들을 이용하여, 연산식으로 입력하시면 됩니다.</div><br /><b>{$member.user_id} :</b> 회원아이디<br /><b>{$member.level} :</b> 회원레벨<br /><b>{$member.type} :</b> 회원종류(ADMINISTRATOR, MODERATOR, MEMBER)<br /><br /><b>입력예</b><br />1. 회원레벨 5 초과인 사람만 허용<br />{$member.level} > 5<br /><br />2. 회원레벨이 5 이상이고, 10 이하인 사람만 허용<br />{$member.level} >= 5 && {$member.level} <= 10<br /><br />3. 회원종류가 MEMBER이고, 회원레벨이 5이상이거나 또는 회원레벨이 10이상인 경우<br />({$member.type} == "MEMBER" && {$member.level} >= 5) || ({$member.level} >= 10)<br /><br /><div class="boxDefault">위의 예제와 같이 괄호와, AND(&&)연산자, OR(||)연산자를 이용하여 정교한 권한을 설정할 수 있습니다.</div>'
			})
		]
	}).show();
}

ContentArea = function(viewport) {
	this.viewport = viewport;

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"모듈목록",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				autoScroll:true,
				layout:"fit",
				cm:new Ext.grid.ColumnModel([
					{
						header:"모듈이름",
						dataIndex:"title",
						sortable:false,
						width:130
					},{
						header:"모듈경로",
						dataIndex:"path",
						sortable:false,
						minWidth:200,
						flex:1,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"모듈버전",
						dataIndex:"version",
						sortable:false,
						width:60,
						renderer:function(value) {
							return '<div style="font-family:tahoma; text-align:center;">'+value+'</div>';
						}
					},{
						header:"세부정보",
						dataIndex:"detail",
						sortable:false,
						width:160,
						renderer:function(value,p,record) {
							var sHTML = "";
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/images/admin/icon_setup_'+record.data.is_setup.toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/images/admin/icon_config_'+record.data.is_config.toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/images/admin/icon_manager_'+record.data.is_manager.toLowerCase()+'.gif" style="margin-right:2px;" />';
							sHTML+= '<img src="<?php echo $_ENV['dir']; ?>/images/admin/icon_direct_'+record.data.is_direct.toLowerCase()+'.gif" style="margin-right:2px;" />';

							return sHTML;
						}
					},
					new Ext.grid.CheckboxSelectionModel()
				]),
				store:new Ext.data.Store({
					proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/exec/Admin.get.php"}),
					reader:new Ext.data.JsonReader({
						root:"lists",
						totalProperty:"totalCount",
						fields:["module","title","version","is_setup","is_config","is_manager","is_direct","path"]
					}),
					remoteSort:true,
					sortInfo:{field:"idx",direction:"DESC"},
					baseParams:{action:"module",get:"list",keyword:""}
				}),
				listeners:{
					rowcontextmenu:{fn:function(grid,idx,e) {
						GridContextmenuSelect(grid,idx);
						var menu = new Ext.menu.Menu();
						var data = grid.getStore().getAt(idx);
						menu.add('<b class="menu-title">'+data.get("title")+'</b>');
						if (data.get("is_setup") == "TRUE" && data.get("is_config") == "TRUE") {
							menu.add({
								text:"모듈설정하기",
								icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_plugin_edit.png",
								handler:function(item) {
									new Ext.Window({
										id:"ConfigWindow",
										title:"모듈설정하기",
										width:700,
										height:450,
										minWidth:600,
										modal:true,
										maximizable:true,
										layout:"fit",
										style:"background:#FFFFFF;",
										html:'<iframe id="ConfigFrame" src="<?php echo $_ENV['dir']; ?>/admin/module.config.php?module='+data.get("module")+'" style="width:100%; height:100%;" frameborder="0"></iframe>',
										buttons:[
											new Ext.Button({
												text:"확인",
												icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_tick.png",
												handler:function() {
													document.getElementById("ConfigFrame").contentWindow.Ext.getCmp("ConfigForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/exec/Admin.do.php?action=module&do=config&module="+data.get("module"),submitEmptyText:false,waitMsg:"데이터를 전송중입니다."})
												}
											}),
											new Ext.Button({
												text:"취소",
												icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_cross.png",
												handler:function() {
													Ext.getCmp("ConfigWindow").close();
												}
											})
										]
									}).show();
								}
							});
						}

						if (data.get("is_setup") == "TRUE" && data.get("is_manager") == "TRUE") {
							menu.add({
								text:"모듈관리하기",
								icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_plugin_go.png",
								handler:function(item) {
									location.href = location.href+"&subpage="+data.get("module");
								}
							});
							menu.add({
								text:(data.get("is_direct") == "TRUE" ? "바로가기삭제" : "바로가기등록"),
								icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_plugin_link.png",
								handler:function(item) {
									Ext.Msg.wait("처리중입니다.","Please Wait...");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/exec/Admin.do.php",
										success:function() {
											location.href = location.href;
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										},
										headers:{},
										params:{"action":"module","do":"direct","module":data.get("module")}
									});
								}
							});
						}
						e.stopEvent();
						menu.showAt(e.getXY());
					}}
				}
			})
		]
	});
	Ext.getCmp("ListPanel").getStore().load();
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>