<?php
$mOneroom = new ModuleOneroom();
?>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/uploader/script/AzUploader.js"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/wysiwyg/script/wysiwyg.js"></script>
<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["idx","agent","name","item","status","email","cellphone"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"DESC"},
		baseParams:{action:"dealer",get:"list",agent:"0"}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"중개담당자관리",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				layout:"fit",
				border:false,
				autoScroll:true,
				tbar:[
					new Ext.form.ComboBox({
						id:"Agent",
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["idx","title","sort"]
							}),
							remoteSort:false,
							sortInfo:{field:"sort",direction:"ASC"},
							baseParams:{"action":"agent","get":"list"}
						}),
						width:90,
						editable:false,
						mode:"local",
						displayField:"title",
						valueField:"idx",
						emptyText:"중개업소",
						listeners:{
							render:{fn:function(form) {
								form.getStore().load();
							}},
							select:{fn:function(form,selected) {
								Ext.getCmp("ListPanel").getStore().baseParams.agent = form.getValue();
								Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:50}});
							}}
						}
					}),
					'-',
					new Ext.form.TextField({
						id:"Keyword",
						width:100,
						emptyText:"검색어 입력"
					}),
					' ',
					new Ext.Button({
						text:"검색",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_magnifier.png",
						handler:function() {
							if (!Ext.getCmp("Keyword").getValue()) {
								Ext.Msg.show({title:"에러",msg:"검색어를 입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
								return false;
							}
							Ext.getCmp("ListPanel").getStore().baseParams.keyword = Ext.getCmp("Keyword").getValue();
							Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:50}});
						}
					}),
					new Ext.Button({
						text:"검색취소",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_magnifier_zoom_out.png",
						handler:function() {
							Ext.getCmp("Agent").setValue("");
							Ext.getCmp("Keyword").setValue("");
							Ext.getCmp("ListPanel").getStore().baseParams.agent = "";
							Ext.getCmp("ListPanel").getStore().baseParams.keyword = "";
							Ext.getCmp("ListTab").getActiveTab().getStore().reload();
						}
					}),
					'-',
					new Ext.Button({
						text:"담당자상태변경",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_group_gear.png",
						menu:new Ext.menu.Menu({
							items:[
								new Ext.menu.Item({
									text:"선택담당자승인",
									handler:function() {
										ChangeDealer("ACTIVE");
									}
								}),
								new Ext.menu.Item({
									text:"선택담당자미승인",
									handler:function() {
										ChangeDealer("WAIT");
									}
									
								})
							]
						})
					}),
					'-',
					new Ext.Button({
						text:"담당자삭제",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_group_delete.png",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").selModel.getSelections();
							if (checked.length == 0) {
								Ext.Msg.show({title:"에러",msg:"삭제할 담당자를 선택해주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
								return;
							}
							
							var idxs = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								idxs.push(checked[i].get("idx"));
							}
							var idx = idxs.join(",");
							
							Ext.Msg.show({title:"안내",msg:"해당 담당자가 등록한 매물도 함께 삭제됩니다.<br />해당 담당자를 정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "ok") {
									Ext.Msg.wait("처리중입니다.","Please Wait...");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Admin.do.php",
										success:function() {
											Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
											Ext.getCmp("ListPanel").getStore().reload();
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
										},
										headers:{},
										params:{"action":"dealer","do":"delete","idx":idx}
									});
								}
							}});
						}
					})
				],
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.CheckboxSelectionModel(),
					{
						header:"중개업소",
						dataIndex:"agent",
						sortable:false,
						width:110
					},{
						header:"담당자명",
						dataIndex:"name",
						sortable:true,
						width:100
					},{
						header:"상태",
						dataIndex:"status",
						sortable:true,
						width:60,
						renderer:function(value) {
							if (value == "ACTIVE") return '<div class="blue center">승인됨</div>';
							else return '<div class="red center">미승인</div>';
						}
					},{
						header:"완료/전체매물",
						dataIndex:"item",
						sortable:false,
						width:80,
						renderer:function(value) {
							var temp = value.split(",");
							return '<div style="text-align:right;"><span class="blue bold">'+GetNumberFormat(temp[0])+'</span> / '+GetNumberFormat(temp[1])+'</div>';
						}
					},{
						header:"이메일",
						dataIndex:"email",
						sortable:false,
						width:150
					},{
						header:"핸드폰번호",
						dataIndex:"cellphone",
						sortable:false,
						width:120
					}
				]),
				store:store,
				sm:new Ext.grid.CheckboxSelectionModel(),
				bbar:new Ext.PagingToolbar({
					pageSize:50,
					store:store,
					displayInfo:true,
					displayMsg:'{0} - {1} of {2}',
					emptyMsg:"데이터없음"
				}),
				listeners:{
					render:{fn:function(grid) {
						grid.getStore().load({params:{start:0,limit:50}});
					}},
					rowdblclick:{fn:function(grid,idx,e) {
						var data = grid.getStore().getAt(idx);
						ItemFunction(data.get("idx"));
					}}
				}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>