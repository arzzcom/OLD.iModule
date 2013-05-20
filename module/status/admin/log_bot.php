<?php
$mStatus = new ModuleStatus();
$bot = $mStatus->GetAllBotCode();
?>
<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/status/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"log_bot",get:"list",date:"<?php echo date('Y-m-d'); ?>"}
		},
		remoteSort:true,
		sorters:[{property:"visit",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["botname","botcode","botsite",{name:"visit",type:"int"},{name:"avgrevisit",type:"float"},"first_time","last_time","last_url"]
	});
	
	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.botname+'</b>');
		
		menu.add({
			text:"현재날짜 기록 초기화",
			handler:function() {
				Ext.Msg.show({title:"안내",msg:record.data.botname+"의 "+Ext.Date.format(Ext.getCmp("today").getValue(),"Y년 m월 d일")+" 기록을 초기화하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("선택한 작업을 서버에서 처리중입니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/status/exec/Admin.do.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("ListPanel").getStore().reload();
									}});
								} else {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								}
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							},
							params:{action:"log_bot_delete",bot:record.data.botcode,date:Ext.Date.format(Ext.getCmp("today").getValue(),"Y-m-d")}
						});
					}
				}});
			}
		});

		e.stopEvent();
		menu.showAt(e.getXY());
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"검색엔진봇로그",
		layout:"fit",
		margin:"0 5 0 0",
		items:[
			new Ext.Panel({
				layout:"border",
				border:false,
				items:[
					new Ext.grid.GridPanel({
						id:"ListPanel",
						title:"일별로그",
						layout:"fit",
						region:"center",
						border:true,
						split:true,
						margin:"5 5 0 5",
						tbar:[
							new Ext.Button({
								icon:"<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_arrow_left.png",
								text:"이전일",
								handler:function() {
									var today = new Date(Ext.getCmp("today").getValue());
									var move = Ext.Date.add(today,Ext.Date.DAY,-1);
									Ext.getCmp("today").setValue(Ext.Date.format(move,"Y-m-d"));
									Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("ip","");
									Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("mno","");
									Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("date",Ext.getCmp("today").getValue());
									Ext.getCmp("ListPanel").getStore().loadPage(1);
								}
							}),
							new Ext.form.DateField({
								id:"today",
								format:"Y-m-d",
								width:90,
								value:"<?php echo date('Y-m-d'); ?>",
								listeners:{select:{fn:function(form,date) {
									Ext.getCmp("today").setValue(Ext.Date.format(date,"Y-m-d"));
									Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("ip","");
									Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("mno","");
									Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("date",Ext.getCmp("today").getValue());
									Ext.getCmp("ListPanel").getStore().loadPage(1);
								}}}
							}),
							new Ext.Button({
								icon:"<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_arrow_right.png",
								iconAlign:"right",
								text:"다음일",
								handler:function() {
									var today = new Date(Ext.getCmp("today").getValue());
									var move = Ext.Date.add(today,Ext.Date.DAY,1);
									Ext.getCmp("today").setValue(Ext.Date.format(move,"Y-m-d"));
									Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("ip","");
									Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("mno","");
									Ext.getCmp("ListPanel").getStore().getProxy().setExtraParam("date",Ext.getCmp("today").getValue());
									Ext.getCmp("ListPanel").getStore().loadPage(1);
								}
							}),
							'-',
							new Ext.Button({
								text:"로그지우기",
								icon:"<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_table_delete.png",
								handler:function() {
									Ext.Msg.show({title:"안내",msg:"검색엔진봇로그를 모두 초기화하시겠습니까?<br />방문카운터 등은 초기화되지 않습니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
										if (button == "yes") {
											Ext.Msg.wait("선택한 작업을 서버에서 처리중입니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/status/exec/Admin.do.php",
												success:function(response) {
													var data = Ext.JSON.decode(response.responseText);
													if (data.success == true) {
														Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
															Ext.getCmp("ListPanel").getStore().reload();
														}});
													} else {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
													}
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												},
												params:{action:"log_bot_delete"}
											});
										}
									}});
								}
							}),
							'->',
							{xtype:"tbtext",text:"마우스 우클릭 : 상세메뉴"}
						],
						columns:[
							new Ext.grid.RowNumberer(),
							{
								header:"검색엔진 봇 이름",
								dataIndex:"botname",
								sortable:true,
								width:180
							},{
								header:"방문횟수",
								dataIndex:"visit",
								width:80,
								sortable:true,
								renderer:GridNumberFormat
							},{
								header:"평균재방문(초)",
								dataIndex:"avgrevisit",
								width:100,
								sortable:true,
								renderer:GridNumberFormat
							},{
								header:"최초방문",
								dataIndex:"first_time",
								sortable:true,
								width:120,
								renderer:function(value) {
									return '<div style="font-family:tahoma;">'+value+'</div>';
								}
							},{
								header:"마지막방문시간",
								dataIndex:"last_time",
								sortable:true,
								width:120,
								renderer:function(value) {
									return '<div style="font-family:tahoma;">'+value+'</div>';
								}
							},{
								header:"마지막방문페이지",
								dataIndex:"last_url",
								minWidth:400,
								flex:1,
								renderer:function(value,p,record) {
									return '<span style="font-family:tahoma;">'+value+' </span>';
								}
							}
						],
						columnLines:true,
						store:store,
						listeners:{
							itemdblclick:{fn:function(grid,record) {
								Ext.Msg.show({title:"안내",msg:"현재 설정화면은 더블클릭으로 실행되는 동작이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
							}},
							itemcontextmenu:ItemContextMenu
						}
					}),
					new Ext.Panel({
						region:"south",
						title:"기간별로그",
						split:true,
						collapsible:true,
						minHeight:250,
						height:350,
						margin:"0 5 5 5",
						layout:{type:"hbox",align:"stretch"},
						tbar:[
							new Ext.form.DateField({
								id:"start_date",
								format:"Y-m-d",
								width:90,
								value:"<?php echo date('Y-m-d',time()-60*60*24*30); ?>",
								listeners:{select:{fn:function() {
									if (Ext.Date.format(Ext.getCmp("start_date").getValue(),"U") > Ext.Date.format(Ext.getCmp("end_date").getValue(),"U")) {
										Ext.getCmp("end_date").setValue(Ext.Date.format(Ext.Date.add(Ext.getCmp("start_date").getValue(),Ext.Date.DAY,30),"Y-m-d"));
									} else if (Ext.Date.format(Ext.getCmp("start_date").getValue(),"U") < Ext.Date.format(Ext.getCmp("end_date").getValue(),"U")-60*60*24*30) {
										Ext.getCmp("end_date").setValue(Ext.Date.format(Ext.Date.add(Ext.getCmp("start_date").getValue(),Ext.Date.DAY,30),"Y-m-d"));
									}
									Ext.getCmp("Pie").getStore().getProxy().setExtraParam("start_date",Ext.Date.format(Ext.getCmp("start_date").getValue(),"Y-m-d"));
									Ext.getCmp("Chart").getStore().getProxy().setExtraParam("start_date",Ext.Date.format(Ext.getCmp("start_date").getValue(),"Y-m-d"));
									Ext.getCmp("Pie").getStore().getProxy().setExtraParam("end_date",Ext.Date.format(Ext.getCmp("end_date").getValue(),"Y-m-d"));
									Ext.getCmp("Chart").getStore().getProxy().setExtraParam("end_date",Ext.Date.format(Ext.getCmp("end_date").getValue(),"Y-m-d"));
									
									Ext.getCmp("Pie").getStore().reload();
									Ext.getCmp("Chart").getStore().reload();
								}}}
							}),
							{xtype:"tbtext",text:"부터"},
							new Ext.form.DateField({
								id:"end_date",
								format:"Y-m-d",
								width:90,
								value:"<?php echo date('Y-m-d'); ?>",
								listeners:{select:{fn:function() {
									if (Ext.Date.format(Ext.getCmp("start_date").getValue(),"U") > Ext.Date.format(Ext.getCmp("end_date").getValue(),"U")) {
										Ext.getCmp("start_date").setValue(Ext.Date.format(Ext.Date.add(Ext.getCmp("end_date").getValue(),Ext.Date.DAY,-30),"Y-m-d"));
									} else if (Ext.Date.format(Ext.getCmp("start_date").getValue(),"U") < Ext.Date.format(Ext.getCmp("end_date").getValue(),"U")-60*60*24*30) {
										Ext.getCmp("start_date").setValue(Ext.Date.format(Ext.Date.add(Ext.getCmp("end_date").getValue(),Ext.Date.DAY,-30),"Y-m-d"));
									}
									Ext.getCmp("Pie").getStore().getProxy().setExtraParam("start_date",Ext.Date.format(Ext.getCmp("start_date").getValue(),"Y-m-d"));
									Ext.getCmp("Chart").getStore().getProxy().setExtraParam("start_date",Ext.Date.format(Ext.getCmp("start_date").getValue(),"Y-m-d"));
									Ext.getCmp("Pie").getStore().getProxy().setExtraParam("end_date",Ext.Date.format(Ext.getCmp("end_date").getValue(),"Y-m-d"));
									Ext.getCmp("Chart").getStore().getProxy().setExtraParam("end_date",Ext.Date.format(Ext.getCmp("end_date").getValue(),"Y-m-d"));
									
									Ext.getCmp("Pie").getStore().reload();
									Ext.getCmp("Chart").getStore().reload();
								}}}
							}),
							{xtype:"tbtext",text:"까지"},
							'-',
							new Ext.Button({
								text:"최근 1주일",
								icon:"<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_calendar_week.png",
								handler:function() {
									Ext.getCmp("start_date").setValue(Ext.Date.format(Ext.Date.add(new Date(),Ext.Date.DAY,-7),"Y-m-d"));
									Ext.getCmp("end_date").setValue(Ext.Date.format(new Date(),"Y-m-d"));
									
									Ext.getCmp("Pie").getStore().getProxy().setExtraParam("start_date",Ext.Date.format(Ext.getCmp("start_date").getValue(),"Y-m-d"));
									Ext.getCmp("Chart").getStore().getProxy().setExtraParam("start_date",Ext.Date.format(Ext.getCmp("start_date").getValue(),"Y-m-d"));
									Ext.getCmp("Pie").getStore().getProxy().setExtraParam("end_date",Ext.Date.format(Ext.getCmp("end_date").getValue(),"Y-m-d"));
									Ext.getCmp("Chart").getStore().getProxy().setExtraParam("end_date",Ext.Date.format(Ext.getCmp("end_date").getValue(),"Y-m-d"));
									
									Ext.getCmp("Pie").getStore().reload();
									Ext.getCmp("Chart").getStore().reload();
								}
							}),
							new Ext.Button({
								text:"최근 1개월",
								icon:"<?php echo $_ENV['dir']; ?>/module/status/images/admin/icon_calendar_month.png",
								handler:function() {
									Ext.getCmp("start_date").setValue(Ext.Date.format(Ext.Date.add(new Date(),Ext.Date.DAY,-30),"Y-m-d"));
									Ext.getCmp("end_date").setValue(Ext.Date.format(new Date(),"Y-m-d"));
									
									Ext.getCmp("Pie").getStore().getProxy().setExtraParam("start_date",Ext.Date.format(Ext.getCmp("start_date").getValue(),"Y-m-d"));
									Ext.getCmp("Chart").getStore().getProxy().setExtraParam("start_date",Ext.Date.format(Ext.getCmp("start_date").getValue(),"Y-m-d"));
									Ext.getCmp("Pie").getStore().getProxy().setExtraParam("end_date",Ext.Date.format(Ext.getCmp("end_date").getValue(),"Y-m-d"));
									Ext.getCmp("Chart").getStore().getProxy().setExtraParam("end_date",Ext.Date.format(Ext.getCmp("end_date").getValue(),"Y-m-d"));
									
									Ext.getCmp("Pie").getStore().reload();
									Ext.getCmp("Chart").getStore().reload();
								}
							})
						],
						items:[
							new Ext.Panel({
								width:300,
								layout:"fit",
								border:false,
								items:[
									new Ext.chart.Chart({
										id:"Pie",
										xtype:"chart",
										animate:true,
										insetPadding:20,
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:true,
												url:"<?php echo $_ENV['dir']; ?>/module/status/exec/Admin.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount"},
												extraParams:{action:"log_bot",get:"pie",start_date:"<?php echo date('Y-m-d',time()-60*60*24*30); ?>",end_date:"<?php echo date('Y-m-d'); ?>"}
											},
											remoteSort:false,
											sorters:[{property:"visit",direction:"DESC"}],
											autoLoad:true,
											pageSize:50,
											fields:["botname",{name:"visit",type:"int"}]
										}),
										series:[{
											type:"pie",
											field:"visit",
											donut:false,
											label:{
												field:"botname",
												display:"rotate",
												contrast:true,
												font:"12px NanumGothicWeb"
											},
											tips:{
												trackMouse:true,
												renderer:function(store,item) {
													this.update(store.get("botname")+" : <b>"+GetNumberFormat(store.get("visit"))+"</b>회 방문");
												}
											}
										}]
									})
								]
							}),
							new Ext.Panel({
								flex:1,
								layout:"fit",
								border:false,
								bodyPadding:"10 0 0 0",
								items:[
									new Ext.chart.Chart({
										id:"Chart",
										animate:true,
										store:new Ext.data.JsonStore({
											proxy:{
												type:"ajax",
												simpleSortMode:true,
												url:"<?php echo $_ENV['dir']; ?>/module/status/exec/Admin.get.php",
												reader:{type:"json",root:"lists",totalProperty:"totalCount"},
												extraParams:{action:"log_bot",get:"chart",start_date:"<?php echo date('Y-m-d',time()-60*60*24*30); ?>",end_date:"<?php echo date('Y-m-d'); ?>"}
											},
											remoteSort:true,
											sorters:[{property:"date",direction:"ASC"}],
											autoLoad:true,
											pageSize:50,
											fields:["date"<?php for ($i=0, $loop=sizeof($bot);$i<$loop;$i++) { ?>,{name:"<?php echo $bot[$i]; ?>",type:"int"}<?php } ?>,{name:"others",type:"int"}]
										}),
										legend:{
											position:"bottom"
										},
										axes:[{
											type:"Numeric",
											position:"left",
											grid:true
										},{
											type:"Category",
											position:"bottom",
											fields:"date",
											grid:true
										}],
										series:[{
											type:"bar",
											column:true,
											axis:"left",
											xField:"date",
											yField:[<?php for ($i=0, $loop=sizeof($bot);$i<$loop;$i++) { ?>"<?php echo $bot[$i]; ?>",<?php } ?>"others"],
											title:[<?php for ($i=0, $loop=sizeof($bot);$i<$loop;$i++) { ?>"<?php echo preg_replace('/\([^\)]+\)/','',$mStatus->GetBotName($bot[$i])); ?>",<?php } ?>"기타"],
											stacked:true,
											xPadding:15,
											yPadding:0,
											tips:{
												trackMouse:true,
												autoWidth:true,
												renderer:function(store,item) {
													this.update("<?php for ($i=0, $loop=sizeof($bot);$i<$loop;$i++) { ?><?php echo $mStatus->GetBotName($bot[$i]); ?> : <b>"+GetNumberFormat(store.get("<?php echo $bot[$i]; ?>"))+"</b><br /><?php } ?>기타 : <b>"+GetNumberFormat(store.get("others"))+"</b>");
												}
											}
										}]
									})
								]
							})
						]
					})
				]
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>