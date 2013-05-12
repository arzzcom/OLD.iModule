<?php $id = 'ProDealer'; $title = '지역전문가 등록관리'; ?>
<script type="text/javascript">
Ext.define('MyDesktop.<?php echo $id; ?>',{
	extend:"Ext.ux.desktop.Module",
	id:"<?php echo $id; ?>",
	requires:[
		'Ext.*'
	],
	init:function(){
		this.launcher = {
			text:"<?php echo $title; ?>",
			icon:"./images/<?php echo $id; ?>16.png"
		};
	},
	createWindow:function() {
		var store1 = new Ext.data.JsonStore({
			proxy:{
				type:"ajax",
				simpleSortMode:true,
				url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
				reader:{type:"json",root:"lists",totalProperty:"totalCount"},
				extraParams:{action:"prodealer",get:"auction"}
			},
			remoteSort:true,
			pageSize:50,
			fields:["idx","last_bidding","region","user_id","point","status","bidding"]
		});
		
		var store2 = new Ext.data.JsonStore({
			proxy:{
				type:"ajax",
				simpleSortMode:true,
				url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
				reader:{type:"json",root:"lists",totalProperty:"totalCount"},
				extraParams:{action:"prodealer",get:"myauction"}
			},
			remoteSort:true,
			pageSize:50,
			fields:["idx","title","last_bidding","user_id","point","status","bidding"]
		});

		var desktop = this.app.getDesktop();
		var win = desktop.getWindow("<?php echo $id; ?>");
		if (!win) {
			win = desktop.createWindow({
				id:"<?php echo $id; ?>",
				title:"<?php echo $title; ?>",
				width:800,
				height:500,
				icon:"./images/<?php echo $id; ?>16.png",
				shim:false,
				animCollapse:false,
				constrainHeader:true,
				layout:"fit",
				resizable:false,
				maximizable:true,
				items:[
					new Ext.TabPanel({
						id:"<?php echo $id; ?>ListTab",
						border:false,
						tabPosition:"bottom",
						items:[
							new Ext.grid.GridPanel({
								title:"<?php echo date('Y년 m월 경매참여',mktime(0,0,0,date('m')+1,1,date('Y'))); ?>",
								id:"<?php echo $id; ?>ListPanel1",
								layout:"fit",
								border:false,
								autoScroll:true,
								tbar:[
									new Ext.Button({
										text:"지역전문가 입찰하기",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_auction.png",
										handler:function() {
											new Ext.Window({
												id:"<?php echo $id; ?>AuctionWindow",
												title:"입찰하기",
												width:500,
												layout:"fit",
												timer:null,
												timerFunction:function() {
													var object = Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm();
													var nowTime = parseInt(object.findField("limit_time").getValue());
													if (nowTime == 2678400000) {
														object.findField("timer").setValue('<div style="color:red; text-align:center;">해당 경매는 아직 시작되지 않았습니다. (매월 <?php echo $mOneroom->GetConfig('auction_start'); ?>일 0시 시작)</div>');
													} else if (nowTime > 0) {
														var nowTime = nowTime-1000;
														object.findField("limit_time").setValue(nowTime.toString());
														object.findField("timer").setValue('<div style="color:blue; text-align:center;">'+Ext.Date.format(new Date(nowTime),"j일 G시간 i분 s초 남음")+'</div>');
													} else if (nowTime <= 0) {
														object.findField("timer").setValue('<div style="color:red; text-align:center;">해당 경매는 마감되었습니다. (매월 <?php echo $mOneroom->GetConfig('auction_end'); ?>일 0시 마감)</div>');
													}
													
													Ext.getCmp("<?php echo $id; ?>AuctionWindow").timer = Ext.Function.defer(Ext.getCmp("<?php echo $id; ?>AuctionWindow").timerFunction,1000);
												},
												items:[
													new Ext.form.FormPanel({
														id:"<?php echo $id; ?>AuctionForm",
														border:false,
														bodyPadding:"10 10 5 10",
														fieldDefaults:{labelWidth:70,labelAlign:"right",anchor:"100%",allowBlank:false},
														items:[
															new Ext.form.FieldSet({
																title:"지역선택",
																items:[
																	new Ext.form.FieldContainer({
																		fieldLabel:"지역선택",
																		layout:"hbox",
																		items:[
																			new Ext.form.ComboBox({
																				name:"region1",
																				typeAhead:true,
																				triggerAction:"all",
																				lazyRender:true,
																				store:new Ext.data.JsonStore({
																					proxy:{
																						type:"ajax",
																						simpleSortMode:true,
																						url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
																						reader:{type:"json",root:"lists",totalProperty:"totalCount"},
																						extraParams:{"action":"region"}
																					},
																					sorters:[{property:"sort",direction:"ASC"}],
																					autoLoad:true,
																					fields:["idx","title","sort"],
																					liteners:{load:{fn:function(store) {
																						if (store.find("idx",Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region1").getValue(),0,false,false,true) == -1) {
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region1").setValue("");
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region1").clearInvalid();
																						} else {
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region1").setValue(Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region1").getValue());
																						}
																					}}}
																				}),
																				width:100,
																				editable:false,
																				mode:"local",
																				displayField:"title",
																				valueField:"idx",
																				emptyText:"1차지역",
																				style:{marginRight:"5px"},
																				listeners:{
																					select:{fn:function(form,selected) {
																						if (form.getValue() == "0") {
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region2").disable();
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region3").disable();
																						} else {
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region2").enable();
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region2").getStore().getProxy().setExtraParam("parent",form.getValue());
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region2").getStore().load();
																						}
																					}}
																				}
																			}),
																			new Ext.form.ComboBox({
																				name:"region2",
																				typeAhead:true,
																				triggerAction:"all",
																				lazyRender:true,
																				disabled:true,
																				allowBlank:true,
																				store:new Ext.data.JsonStore({
																					proxy:{
																						type:"ajax",
																						simpleSortMode:true,
																						url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
																						reader:{type:"json",root:"lists",totalProperty:"totalCount"},
																						extraParams:{"action":"region","prent":"-1","is_none":"true"}
																					},
																					sorters:[{property:"sort",direction:"ASC"}],
																					autoLoad:true,
																					fields:["idx","title","sort"],
																					liteners:{load:{fn:function(store) {
																						if (store.find("idx",Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region2").getValue(),0,false,false,true) == -1) {
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region2").setValue("");
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region2").clearInvalid();
																						} else {
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region2").setValue(Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region2").getValue());
																						}
																					}}}
																				}),
																				width:100,
																				editable:false,
																				mode:"local",
																				displayField:"title",
																				valueField:"idx",
																				emptyText:"2차지역",
																				style:{marginRight:"5px"},
																				listeners:{
																					select:{fn:function(form,selected) {
																						if (form.getValue() == "0") {
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region3").disable();
																						} else {
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region3").enable();
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region3").getStore().getProxy().setExtraParam("parent",form.getValue());
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region3").getStore().load();
																						}
																					}}
																				}
																			}),
																			new Ext.form.ComboBox({
																				name:"region3",
																				typeAhead:true,
																				triggerAction:"all",
																				lazyRender:true,
																				disabled:true,
																				allowBlank:true,
																				store:new Ext.data.JsonStore({
																					proxy:{
																						type:"ajax",
																						simpleSortMode:true,
																						url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
																						reader:{type:"json",root:"lists",totalProperty:"totalCount"},
																						extraParams:{"action":"region","prent":"-1","is_none":"true"}
																					},
																					sorters:[{property:"sort",direction:"ASC"}],
																					autoLoad:true,
																					fields:["idx","title","sort"],
																					liteners:{load:{fn:function(store) {
																						if (store.find("idx",Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region3").getValue(),0,false,false,true) == -1) {
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region3").setValue("");
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region3").clearInvalid();
																						} else {
																							Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region3").setValue(Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().findField("region3").getValue());
																						}
																					}}}
																				}),
																				width:100,
																				editable:false,
																				mode:"local",
																				displayField:"title",
																				valueField:"idx",
																				emptyText:"3차지역"
																			})
																		]
																	}),
																	new Ext.Panel({
																		padding:"0 5 5 75",
																		border:false,
																		html:'입찰후 낙찰되었을 때 선택하신 지역의 전문가로 노출되게 됩니다.<br />최초 1회만 선택하면 다음입찰때부터는 자동으로 선택되어 집니다.'
																	})
																]
															}),
															new Ext.form.FieldSet({
																title:"입찰하기",
																items:[
																	new Ext.form.FieldContainer({
																		fieldLabel:"나의 포인트",
																		layout:"hbox",
																		items:[
																			new Ext.form.NumberField({
																				name:"mypoint",
																				readOnly:true,
																				width:100
																			}),
																			new Ext.form.DisplayField({
																				value:"&nbsp;포인트"
																			})
																		]
																	}),
																	new Ext.form.FieldContainer({
																		fieldLabel:"나의 입찰액",
																		layout:"hbox",
																		items:[
																			new Ext.form.NumberField({
																				name:"mybidding",
																				readOnly:true,
																				width:100
																			}),
																			new Ext.form.DisplayField({
																				value:"&nbsp;포인트"
																			})
																		]
																	}),
																	new Ext.form.FieldContainer({
																		fieldLabel:"새 입찰금액",
																		layout:"hbox",
																		items:[
																			new Ext.form.NumberField({
																				name:"point",
																				width:100,
																				minValue:1000,
																				step:1000,
																				listeners:{blur:{fn:function(form) {
																					form.setValue(Math.ceil(form.getValue()/1000)*1000);
																				}}}
																			}),
																			new Ext.form.DisplayField({
																				value:"&nbsp;포인트 (1,000포인트 단위만 가능)"
																			})
																		]
																	})
																]
															}),
															new Ext.form.FieldSet({
																title:"경매마감시각",
																items:[
																	new Ext.form.Hidden({
																		name:"limit_time"
																	}),
																	new Ext.form.DisplayField({
																		name:"timer",
																		value:"로딩중..."
																	})
																]
															})
														]
													})
												],
												buttons:[
													new Ext.Toolbar.TextItem({
														id:"<?php echo $id; ?>LimitCount",
														text:"남은 입찰가능횟수 : 계산중..."
													}),
													'->',
													new Ext.Button({
														id:"<?php echo $id; ?>BiddingButton",
														text:"입찰하기",
														handler:function() {
															Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().submit({
																url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.do.php?action=prodealer&do=auction",
																submitEmptyText:false,
																waitTitle:"잠시만 기다려주십시오.",
																waitMsg:"지역전문가 경매에 입찰하고 있습니다.",
																success:function(form,action) {
																	Ext.Msg.show({title:"안내",msg:"성공적으로 입찰하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
																		if (button == "ok") {
																			Ext.getCmp("<?php echo $id; ?>ListPanel1").getStore().reload();
																			Ext.getCmp("<?php echo $id; ?>ListPanel2").getStore().reload();
																			Ext.getCmp("<?php echo $id; ?>AuctionWindow").close();
																		}
																	}});
																},
																failure:function(form,action) {
																	if (action.result) {
																		if (action.result.message) {
																			Ext.Msg.show({title:"에러",msg:action.result.message,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
																			return;
																		}
																	}
																	Ext.Msg.show({title:"에러",msg:"입력내용에 오류가 있습니다.<br />입력내용을 다시 한번 확인하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
																}
															});
														}
													}),
													new Ext.Button({
														text:"취소",
														handler:function() {
															Ext.getCmp("<?php echo $id; ?>AuctionWindow").close();
														}
													})
												],
												listeners:{
													show:{fn:function() {
														Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().load({
															url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php?action=prodealer&get=bidding",
															submitEmptyText:false,
															waitTitle:"잠시만 기다려주십시오.",
															waitMsg:"데이터를 로딩중입니다.",
															success:function(form,action) {
																if (action.result.data.region1) form.findField("region1").fireEvent("select",form.findField("region1"));
																if (action.result.data.region2) form.findField("region2").fireEvent("select",form.findField("region2"));
																if (action.result.data.region3) form.findField("region3").fireEvent("select",form.findField("region3"));
																if (action.result.data.limit_count == "-1") {
																	Ext.getCmp("<?php echo $id; ?>LimitCount").setText("남은 입찰가능횟수 : 무제한");
																} else {
																	Ext.getCmp("<?php echo $id; ?>LimitCount").setText("남은 입찰가능횟수 : "+GetNumberFormat(action.result.data.limit_count)+"회");
																}
																if (parseInt(action.result.data.bidding_point) > 0) {
																	Ext.getCmp("<?php echo $id; ?>BiddingButton").setText("입찰하기 (참가비 : "+GetNumberFormat(action.result.data.bidding_point)+"포인트)");
																}
																
																Ext.getCmp("<?php echo $id; ?>AuctionWindow").timerFunction();
															},
															failure:function(form,action) {
																Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
															}
														});
													}},
													close:{fn:function() {
														window.clearTimeout(Ext.getCmp("<?php echo $id; ?>AuctionWindow").timer);
													}}
												}
											}).show();
										}
									}),
									'->',
									{xtype:"tbtext",text:"총 입찰제한수 : <?php echo $mOneroom->GetConfig('auction_limit') == '0' ? '무제한' : $mOneroom->GetConfig('auction_limit').'회'; ?>"}
								],
								columns:[
									new Ext.grid.RowNumberer({
										header:"경매번호",
										dataIndex:"idx",
										align:"left",
										width:60,
										renderer:function(value,p,record) {
											p.tdCls = Ext.baseCSSPrefix + 'grid-cell-special';
											return GridNumberFormat(value);
										}
									}),{
										header:"입찰자",
										dataIndex:"user_id",
										width:100
									},{
										header:"지역",
										dataIndex:"region",
										minWidth:100,
										flex:1
									},{
										header:"입찰가",
										dataIndex:"point",
										width:100,
										renderer:function(value) {
											return '<div style="text-align:right;">'+GetNumberFormat(value)+' 포인트</div>';
										}
									},{
										header:"입찰수",
										dataIndex:"bidding",
										width:60,
										renderer:GridNumberFormat
									},{
										header:"마지막 입찰시각",
										dataIndex:"last_bidding",
										width:120,
										renderer:function(value) {
											return '<div style="font-family:tahoma;">'+value+'</div>';
										}
									},{
										header:"낙찰여부",
										dataIndex:"status",
										width:80,
										renderer:function(value) {
											if (value == "SUCCESS") return '<span style="color:blue;">낙찰</span>';
											else if (value == "INFLUENTIAL") return '<span style="color:blue;">낙찰유력</span>';
											else return '<span style="color:red;">낙찰실패</span>'
										}
									}
								],
								sortableColumns:false,
								columnLines:true,
								store:store1,
								bbar:new Ext.PagingToolbar({
									store:store1,
									displayInfo:true
								})
							}),
							new Ext.grid.GridPanel({
								title:"나의경매내역",
								id:"<?php echo $id; ?>ListPanel2",
								layout:"fit",
								border:false,
								autoScroll:true,
								tbar:[
									new Ext.Button({
										text:"낙찰실패건에 대한 입찰금반환신청",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_coins_add.png",
										handler:function() {
											Ext.Msg.wait("낙찰실패건에 대한 입찰금을 반환중입니다.","잠시만 기다려주십시오.");
											Ext.Ajax.request({
												url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.do.php",
												success:function(response) {
													var data = Ext.JSON.decode(response.responseText);
													if (data.success == true) {
														Ext.Msg.show({title:"안내",msg:data.message,buttons:Ext.Msg.OK,icon:Ext.Msg.INFO});
														Ext.getCmp("<?php echo $id; ?>ListPanel2").getStore().reload();
													} else {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
													}
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												},
												params:{"action":"prodealer","do":"return"}
											});
										}
									})
								],
								columns:[
									new Ext.grid.RowNumberer({
										header:"경매번호",
										dataIndex:"idx",
										align:"left",
										width:60,
										renderer:function(value,p,record) {
											p.tdCls = Ext.baseCSSPrefix + 'grid-cell-special';
											return GridNumberFormat(value);
										}
									}),{
										header:"경매명",
										dataIndex:"title",
										minWidth:100,
										flex:1
									},{
										header:"최종입찰일시",
										dataIndex:"last_bidding",
										width:120,
										renderer:function(value) {
											return '<div style="font-family:tahoma;">'+value+'</div>'
										}
									},{
										header:"최종입찰가",
										dataIndex:"point",
										width:120,
										renderer:function(value) {
											return '<div style="text-align:right;">'+GetNumberFormat(value)+' 포인트</div>';
										}
									},{
										header:"입찰수",
										dataIndex:"bidding",
										width:50,
										renderer:GridNumberFormat
									},{
										header:"낙찰여부",
										dataIndex:"status",
										width:90,
										renderer:function(value) {
											if (value == "SUCCESS") return '<span style="color:blue;">낙찰</span>';
											else if (value == "INFLUENTIAL") return '<span style="color:blue;">낙찰유력</span>';
											else if (value == "FAIL") return '<span style="color:green;">낙찰실패</span>';
											else return '반환완료';
										}
									}
								],
								columnLines:true,
								sortableColumns:false,
								store:store2,
								bbar:new Ext.PagingToolbar({
									store:store2,
									displayInfo:true
								})
							})
						]
					})
				],
				listeners:{show:{fn:function() {
					store1.load();
					store2.load();
				}}}
			}).show();
		}
	}
});

ManagerModules.push(new MyDesktop.<?php echo $id; ?>());
ManagerShortcuts.push({name:"<?php echo $title; ?>",icon:"./images/<?php echo $id; ?>48.png",module:"<?php echo $id; ?>"});
</script>