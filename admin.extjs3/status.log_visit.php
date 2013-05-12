<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"visit_time","pageurl","ip","nickname","user_agent","refererurl"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"DESC"},
		baseParams:{action:"status",get:"log_visit",date:"<?php echo date('Y-m-d'); ?>","type":"ALL"}
	});

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"방문자로그",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_control_left.png",
						text:"이전일",
						handler:function() {
							var today = new Date(Ext.getCmp("today").getValue()).add("d",-1).format("Y-m-d");
							Ext.getCmp("today").setValue(today);
							Ext.getCmp("ListPanel").getStore().baseParams.date = today;
							Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:100}});
						}
					}),
					' ',
					new Ext.form.DateField({
						id:"today",
						format:"Y-m-d",
						width:90,
						value:"<?php echo date('Y-m-d'); ?>",
						listeners:{select:{fn:function(form,date) {
							var today = new Date(date).format("Y-m-d");
							Ext.getCmp("ListPanel").getStore().baseParams.date = today;
							Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:100}});
						}}}
					}),
					' ',
					new Ext.Button({
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_control_right.png",
						iconAlign:"right",
						text:"다음일",
						handler:function() {
							var today = new Date(Ext.getCmp("today").getValue()).add("d",1).format("Y-m-d");
							Ext.getCmp("today").setValue(today);
							Ext.getCmp("ListPanel").getStore().baseParams.date = today;
							Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:100}});
						}
					}),
					'-',
					new Ext.Button({
						id:"TypeAll",
						text:"전체기록",
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_checkbox_on.png",
						enableToggle:false,
						pressed:true,
						handler:function(button) {
							if (button.pressed == false) {
								Ext.getCmp("ListPanel").getStore().baseParams.type = "ALL";
								Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:100}});
							}
							Ext.getCmp("TypeAll").toggle(true);
							Ext.getCmp("TypeMember").toggle(false);
						},
						listeners:{toggle:{fn:function(button) {
							if (button.pressed == true) button.setIcon("<?php echo $_ENV['dir']; ?>/images/admin/icon_checkbox_on.png");
							else button.setIcon("<?php echo $_ENV['dir']; ?>/images/admin/icon_checkbox.png");
						}}}
					}),
					' ',
					new Ext.Button({
						id:"TypeMember",
						text:"회원기록",
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_checkbox.png",
						enableToggle:false,
						handler:function(button) {
							if (button.pressed == false) {
								Ext.getCmp("ListPanel").getStore().baseParams.type = "MEMBER";
								Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:100}});
							}
							Ext.getCmp("TypeAll").toggle(false);
							Ext.getCmp("TypeMember").toggle(true);
						},
						listeners:{toggle:{fn:function(button) {
							if (button.pressed == true) button.setIcon("<?php echo $_ENV['dir']; ?>/images/admin/icon_checkbox_on.png");
							else button.setIcon("<?php echo $_ENV['dir']; ?>/images/admin/icon_checkbox.png");
						}}}
					}),
					'-',
					new Ext.Button({
						text:"로그지우기",
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_table_delete.png",
						handler:function() {
							Ext.Msg.show({title:"안내",msg:"방문자로그를 모두 초기화하시겠습니까?<br />방문카운터 등은 초기화되지 않습니다.",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
								if (button == "ok") {
									Ext.Msg.wait("처리중입니다.","Please Wait...");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/exec/Admin.do.php",
										success:function() {
											Ext.Msg.show({title:"안내",msg:"성공적으로 초기화하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											Ext.getCmp("ListPanel").getStore().reload();
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										},
										headers:{},
										params:{"action":"status","do":"log_visit_delete"}
									});
								}
							}});
						}
					})
				],
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.RowNumberer(),
					{
						header:"방문시간",
						dataIndex:"visit_time",
						sortable:true,
						width:120,
						renderer:function(value,p,record) {
							return '<span style="font-family:tahoma;">'+value+' </span>';
						}
					},{
						header:"방문페이지",
						dataIndex:"pageurl",
						width:300,
						renderer:function(value,p,record) {
							return '<span style="font-family:tahoma;">'+value+' </span>';
						}
					},{
						header:"아이피",
						dataIndex:"ip",
						width:100,
						renderer:function(value,p,record) {
							return '<span style="font-family:tahoma;">'+value+' </span>';
						}
					},{
						header:"닉네임",
						dataIndex:"nickname",
						sortable:true,
						width:80,
						renderer:function(value) {
							if (value) return value;
							else return '<span style="color:#999999;">비회원</span>';
						}
					},{
						header:"브라우져",
						dataIndex:"user_agent",
						sortable:true,
						width:200,
						renderer:function(value) {
							return '<div style="font-family:tahoma;">'+value+'</div>';
						}
					},{
						header:"이전페이지",
						dataIndex:"refererurl",
						width:300,
						renderer:function(value,p,record) {
							return '<span style="font-family:tahoma;">'+value+' </span>';
						}
					}
				]),
				sm:new Ext.grid.CheckboxSelectionModel(),
				store:store,
				bbar:new Ext.PagingToolbar({
					pageSize:100,
					store:store,
					displayInfo:true,
					displayMsg:'{0} - {1} of {2}',
					emptyMsg:"데이터없음"
				})
			})
		]
	});

	store.load({params:{start:0,limit:100}});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>