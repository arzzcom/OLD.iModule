<?php $id = 'RegionItem'; $title = '지역추천 매물관리'; $actionTarget = 'regionitem'; $actionTitle = '지역추천'; ?>
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
				extraParams:{action:"<?php echo $actionTarget; ?>",get:"auction"}
			},
			pageSize:50,
			fields:["idx","last_bidding","user_id","point","status","bidding","last_bidding"]
		});
		
		var store2 = new Ext.data.JsonStore({
			proxy:{
				type:"ajax",
				simpleSortMode:true,
				url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
				reader:{type:"json",root:"lists",totalProperty:"totalCount"},
				extraParams:{action:"<?php echo $actionTarget; ?>",get:"myauction_item"}
			},
			pageSize:50,
			fields:["idx","month","ino","title","hit","end_date"]
		});
		
		var store3 = new Ext.data.JsonStore({
			proxy:{
				type:"ajax",
				simpleSortMode:true,
				url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
				reader:{type:"json",root:"lists",totalProperty:"totalCount"},
				extraParams:{action:"<?php echo $actionTarget; ?>",get:"myauction"}
			},
			remoteSort:true,
			sorters:[{property:"month",direction:"DESC"}],
			pageSize:50,
			fields:["idx","title","last_bidding","user_id","point","status","bidding"]
		});
		
		var <?php echo $id; ?>InsertSlot = function() {
			var checked = Ext.getCmp("<?php echo $id; ?>ListPanel2").getSelectionModel().getSelection();
			if (checked.length != 1) {
				Ext.Msg.show({title:"에러",msg:"매물을 할당할 슬롯을 1개만 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
				return;
			}
			
			var ItemStore = new Ext.data.JsonStore({
				proxy:{
					type:"ajax",
					simpleSortMode:true,
					url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
					reader:{type:"json",root:"lists",totalProperty:"totalCount"},
					extraParams:{action:"item",get:"list",agent:"0",dealer:"0",region1:"0",region2:"0",region3:"0",category1:"0",category2:"0",category3:"0",keyword:"",is_open:"TRUE"}
				},
				remoteSort:true,
				sorters:[{property:"idx",direction:"DESC"}],
				autoLoad:true,
				pageSize:50,
				fields:["idx","category","region","title","price","hit","is_open","is_premium","is_regionitem","end_date"]
			});
			
			new Ext.Window({
				id:"<?php echo $id; ?>SlotLinkWindow",
				title:"슬롯할당",
				width:700,
				height:400,
				modal:true,
				layout:"fit",
				tbar:[
					new Ext.form.NumberField({
						width:80,
						emptyText:"매물번호",
						hideTrigger:true,
						mouseWheelEnabled:false,
						checkChangeBuffer:500,
						listeners:{change:{fn:function(form) {
							ItemStore.getProxy().setExtraParam("idx",form.getValue());
							ItemStore.reload();
						}}}
					}),
					'-',
					new Ext.form.ComboBox({
						typeAhead:true,
						triggerAction:"all",
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{"action":"category","is_all":"true"}
							},
							sorters:[{property:"sort",direction:"ASC"}],
							autoLoad:true,
							fields:["idx","title",{name:"sort",type:"int"}]
						}),
						width:90,
						editable:false,
						displayField:"title",
						valueField:"idx",
						emptyText:"1차카테고리",
						listeners:{
							select:{fn:function(form,selected) {
								Ext.getCmp("<?php echo $id; ?>SlotLinkPanel").getStore().getProxy().setExtraParam("category1",form.getValue());
								Ext.getCmp("<?php echo $id; ?>SlotLinkPanel").getStore().reload();
							}}
						}
					}),
					new Ext.form.ComboBox({
						typeAhead:true,
						triggerAction:"all",
						store:new Ext.data.JsonStore({
							proxy:{
								type:"ajax",
								simpleSortMode:true,
								url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
								reader:{type:"json",root:"lists",totalProperty:"totalCount"},
								extraParams:{"action":"region","is_all":"true"}
							},
							sorters:[{property:"sort",direction:"ASC"}],
							autoLoad:true,
							fields:["idx","title",{name:"sort",type:"int"}]
						}),
						width:90,
						editable:false,
						displayField:"title",
						valueField:"idx",
						emptyText:"1차지역",
						listeners:{
							select:{fn:function(form,selected) {
								Ext.getCmp("<?php echo $id; ?>SlotLinkPanel").getStore().getProxy().setExtraParam("region1",form.getValue());
								Ext.getCmp("<?php echo $id; ?>SlotLinkPanel").getStore().reload();
							}}
						}
					}),
					new Ext.form.TextField({
						id:"<?php echo $id; ?>Keyword",
						width:150,
						emptyText:"검색어 입력"
					}),
					new Ext.Button({
						text:"검색",
						icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_magnifier.png",
						handler:function() {
							Ext.getCmp("<?php echo $id; ?>SlotLinkPanel").getStore().getProxy().setExtraParam("keyword",Ext.getCmp("<?php echo $id; ?>Keyword").getValue());
							Ext.getCmp("<?php echo $id; ?>SlotLinkPanel").getStore().reload();
						}
					})
				],
				items:[
					new Ext.grid.GridPanel({
						id:"<?php echo $id; ?>SlotLinkPanel",
						border:false,
						autoScroll:true,
						columns:[
							new Ext.grid.RowNumberer({
								header:"번호",
								dataIndex:"idx",
								sortable:true,
								width:60,
								renderer:function(value,p,record) {
									p.tdCls = Ext.baseCSSPrefix + 'grid-cell-special';
									return GridNumberFormat(value);
								}
							}),{
								header:"상태",
								width:80,
								renderer:function(value,p,record) {
									var sHTML = '';
									if (record.data.is_open == "TRUE") {
										if (record.data.is_premium == "TRUE") sHTML+= '<span style="color:red;">프리미엄</span>';
										if (record.data.is_premium == "TRUE" && record.data.is_regionitem == "TRUE") sHTML+= '/<span style="color:blue;">지역</span>';
										if (record.data.is_premium == "FALSE" && record.data.is_regionitem == "TRUE") sHTML+= '<span style="color:blue;">지역추천</span>';
										if (!sHTML) sHTML+= '일반(공개중)';
									} else {
										sHTML+= '대기(비공개)';
									}
									return sHTML;
								}
							},{
								header:"매물명",
								dataIndex:"title",
								sortable:true,
								minWidth:250,
								flex:1,
								renderer:function(value,p,record) {
									return '<span class="blue">['+record.data.category+']</span> '+value;
								}
							},{
								header:"지역",
								dataIndex:"region",
								sortable:false,
								width:100
							},{
								header:"조회",
								dataIndex:"hit",
								width:40,
								renderer:GridNumberFormat
							},{
								header:"만료일",
								dataIndex:"end_date",
								width:70,
								renderer:function(value,p,record) {
									if (record.data.is_open == "TRUE" && value == 0) {
										return '<div style="text-align:center; color:blue;">무제한</div>';
									} else if (value == 0) {
										return '<div style="text-align:center; color:red;">공개중아님</div>';
									} else {
										return '<div style="font-family:tahoma;">'+value+'</div>';
									}
								}
							}
						],
						columnLines:true,
						store:ItemStore,
						selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last",mode:"SINGLE"}),
						bbar:new Ext.PagingToolbar({
							store:ItemStore,
							displayInfo:true
						})
					})
				],
				buttons:[
					new Ext.Button({
						text:"낙찰건할당",
						handler:function() {
							var checked = Ext.getCmp("<?php echo $id; ?>SlotLinkPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"에러",msg:"할당할 매물을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
							} else {
								var slot = Ext.getCmp("<?php echo $id; ?>ListPanel2").getSelectionModel().getSelection()[0];
								Ext.Msg.show({title:"확인",msg:checked[0].get("title")+"매물을 선택 낙찰건에 할당하시겠습니까?<br />낙찰건에 할당하는 즉시 적용됩니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
									if (button == "yes") {
										Ext.Msg.wait("선택한 매물을 낙찰건에 할당중입니다.","잠시만 기다려주십시오.");
										Ext.Ajax.request({
											url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.do.php",
											success:function(response) {
												var data = Ext.JSON.decode(response.responseText);
												if (data.success == true) {
													Ext.Msg.show({title:"안내",msg:"성공적으로 할당하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
														Ext.getCmp("<?php echo $id; ?>ListPanel2").getStore().reload();
														Ext.getCmp("<?php echo $id; ?>SlotLinkWindow").close();
													}});
												} else {
													Ext.Msg.show({title:"안내",msg:data.message,buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												}
											},
											failure:function() {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											},
											params:{"action":"<?php echo $actionTarget; ?>","do":"auction_link","idx":checked[0].get("idx"),"auction":slot.get("idx")}
										});
									}
								}});
							}
						}
					}),
					new Ext.Button({
						text:"취소",
						handler:function() {
							Ext.getCmp("<?php echo $id; ?>SlotLinkWindow").close();
						}
					})
				]
			}).show();
		}
		
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
						tabPosition:"bottom",
						activeTab:0,
						border:false,
						items:[
							new Ext.grid.GridPanel({
								title:"<?php echo date('Y년 m월 경매참여',mktime(0,0,0,date('m')+1,1,date('Y'))); ?>",
								id:"<?php echo $id; ?>ListPanel1",
								layout:"fit",
								border:false,
								autoScroll:true,
								tbar:[
									new Ext.Button({
										text:"<?php echo $actionTitle; ?>매물 입찰하기",
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
																title:"입찰종류선택 (신규입찰 / 기존입찰건 상회입찰)",
																items:[
																	new Ext.form.ComboBox({
																		name:"type",
																		typeAhead:true,
																		triggerAction:"all",
																		lazyRender:true,
																		store:new Ext.data.JsonStore({
																			proxy:{
																				type:"ajax",
																				simpleSortMode:true,
																				url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php",
																				reader:{type:"json",root:"lists",totalProperty:"totalCount"},
																				extraParams:{"action":"<?php echo $actionTarget; ?>","get":"auction_type"}
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
																		editable:false,
																		mode:"local",
																		displayField:"title",
																		valueField:"idx",
																		value:"0",
																		listeners:{
																			select:{fn:function(form,selected) {
																				Ext.getCmp("<?php echo $id; ?>AuctionForm").getForm().load({
																					url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php?action=<?php echo $actionTarget; ?>&get=bidding&idx="+form.getValue(),
																					submitEmptyText:false,
																					waitTitle:"잠시만 기다려주십시오.",
																					waitMsg:"데이터를 로딩중입니다.",
																					success:function(form,action) {
																						if (action.result.data.limit_count == "-1") {
																							Ext.getCmp("<?php echo $id; ?>LimitCount").setText("남은 입찰가능횟수 : 무제한");
																						} else {
																							Ext.getCmp("<?php echo $id; ?>LimitCount").setText("남은 입찰가능횟수 : "+GetNumberFormat(action.result.data.limit_count)+"회");
																						}
																						if (parseInt(action.result.data.bidding_point) > 0) {
																							Ext.getCmp("<?php echo $id; ?>BiddingButton").setText("입찰하기 (참가비 : "+GetNumberFormat(action.result.data.bidding_point)+"포인트)");
																						}
																					},
																					failure:function(form,action) {
																						Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
																					}
																				});
																			}}
																		}
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
															}),
															new Ext.form.FieldSet({
																title:"<?php echo $actionTitle; ?>매물설정안내",
																items:[
																	new Ext.form.DisplayField({
																		value:"매월 <?php echo $mOneroom->GetConfig('auction_end');?>일 경매가 종료되면, 낙찰경매건관리에서 낙찰된 <?php echo $actionTitle; ?>공간갯수만큼 <?php echo $actionTitle; ?>매물을 설정할 수 있습니다."
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
																url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.do.php?action=<?php echo $actionTarget; ?>&do=auction",
																submitEmptyText:false,
																waitTitle:"잠시만 기다려주십시오.",
																waitMsg:"<?php echo $actionTitle; ?>매물 경매에 입찰하고 있습니다.",
																success:function(form,action) {
																	Ext.Msg.show({title:"안내",msg:"성공적으로 입찰하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
																		if (button == "ok") {
																			Ext.getCmp("<?php echo $id; ?>ListPanel1").getStore().reload();
																			Ext.getCmp("<?php echo $id; ?>ListPanel3").getStore().reload();
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
															url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.get.php?action=<?php echo $actionTarget; ?>&get=bidding",
															submitEmptyText:false,
															waitTitle:"잠시만 기다려주십시오.",
															waitMsg:"데이터를 로딩중입니다.",
															success:function(form,action) {
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
									{xtype:"tbtext",text:"신규입찰제한수 : <?php echo $mOneroom->GetConfig('auction_limit') == '0' ? '무제한' : $mOneroom->GetConfig('auction_limit').'회'; ?> / 기존입찰상회입찰제한수 : <?php echo $mOneroom->GetConfig('auction_limit') == '0' ? '무제한' : $mOneroom->GetConfig('auction_limit').'회'; ?>"}
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
										header:"입찰일시",
										dataIndex:"last_bidding",
										width:130
									},{
										header:"입찰자",
										dataIndex:"user_id",
										minWidth:100,
										flex:1
									},{
										header:"입찰가",
										dataIndex:"point",
										width:130,
										renderer:function(value) {
											return '<div style="text-align:right;">'+GetNumberFormat(value)+' 포인트</div>';
										}
									},{
										header:"입찰수",
										dataIndex:"bidding",
										width:60,
										renderer:GridNumberFormat
									},{
										header:"마지막 입찰",
										dataIndex:"last_bidding",
										width:120,
										renderer:function(value) {
											return '<div style="font-family:tahoma;">'+value+'</div>'
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
								title:"낙찰경매건관리",
								id:"<?php echo $id; ?>ListPanel2",
								layout:"fit",
								border:false,
								autoScroll:true,
								tbar:[
									new Ext.Button({
										text:"매물할당",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_link.png",
										handler:function() {
											<?php echo $id; ?>InsertSlot();
										}
									}),
									new Ext.Button({
										text:"매물할당해제",
										icon:"<?php echo $_ENV['dir']; ?>/module/oneroom/images/admin/icon_link_break.png",
										handler:function() {
											var checked = Ext.getCmp("<?php echo $id; ?>ListPanel2").getSelectionModel().getSelection();
											if (checked.length == 0) {
												Ext.Msg.show({title:"에러",msg:"매물을 할당해제할 낙찰건을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
												return;
											}
											
											var idxs = new Array();
											for (var i=0, loop=checked.length;i<loop;i++) {
												idxs.push(checked[i].get("idx"));
											}
											
											Ext.Msg.show({title:"확인",msg:"선택낙찰건에 할당된 매물을 해제하겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
												if (button == "yes") {
													Ext.Msg.wait("선택한 낙찰건의 매물을 비우고 있습니다.","잠시만 기다려주십시오.");
													Ext.Ajax.request({
														url:"<?php echo $_ENV['dir']; ?>/module/oneroom/exec/Manager.do.php",
														success:function(response) {
															var data = Ext.JSON.decode(response.responseText);
															if (data.success == true) {
																Ext.Msg.show({title:"안내",msg:"성공적으로 해제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
																	Ext.getCmp("<?php echo $id; ?>ListPanel2").getStore().reload();
																}});
															} else {
																Ext.Msg.show({title:"안내",msg:data.message,buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
															}
														},
														failure:function() {
															Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
														},
														params:{"action":"<?php echo $actionTarget; ?>","do":"auction_unlink","idx":idxs.join(",")}
													});
												}
											}});
										}
									}),
									'->',
									{xtype:"tbtext",text:"목록마우스우클릭 : 상세메뉴 / 목록더블클릭 : 할당매물보기"}
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
										header:"노출기간",
										dataIndex:"month",
										minWidth:120
									},{
										header:"할당된 매물명",
										dataIndex:"title",
										minWidth:120,
										flex:1,
										renderer:function(value,p,record) {
											if (record.data.ino == "0") return '<span style="color:#666666;">할당된 매물정보 없음</span>';
											else return '<span style="color:#EF5600;">[#'+record.data.ino+']</span> '+value;
										}
									},{
										header:"조회",
										dataIndex:"hit",
										width:40,
										renderer:GridNumberFormat
									},{
										header:"매물만료일",
										dataIndex:"end_date",
										width:70,
										renderer:function(value,p,record) {
											if (record.data.ino != "0" && value == 0) {
												return '<div style="text-align:center; color:blue;">무제한</div>';
											} else if (record.data.ino == "0") {
												return '<div style="text-align:center; color:red;">할당중아님</div>';
											} else {
												return '<div style="font-family:tahoma;">'+value+'</div>';
											}
										}
									}
								],
								sortableColumns:false,
								columnLines:true,
								store:store2,
								selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
								bbar:new Ext.PagingToolbar({
									store:store2,
									displayInfo:true
								}),
								listeners:{
									itemcontextmenu:{fn:function(grid,record,row,index,e) {
										grid.getSelectionModel().select(index);
										var menu = new Ext.menu.Menu();
										
										menu.add('<b class="menu-title">슬롯번호 #'+record.data.idx+'</b>');
										
										menu.add({
											text:"매물할당",
											handler:function() {
												<?php echo $id; ?>InsertSlot();
											}
										});
										
										if (record.data.ino != "0") {
											menu.add({
												text:"할당해제",
												handler:function() {
													<?php echo $id; ?>RemoveSlot();
												}
											});
											
											menu.add('-');
											
											menu.add({
												text:"할당된 매물정보수정",
												handler:function() {
													ItemForm(record.data.ino,Ext.getCmp("<?php echo $id; ?>ListPanel"));
												}
											});
										}
										
										e.stopEvent();
										menu.showAt(e.getXY());
									}},
									itemdblclick:{fn:function(grid,record) {
										if (record.data.ino != "0") {
											new Ext.Window({
												title:record.data.title,
												width:800,
												height:500,
												modal:true,
												resizable:false,
												html:'<iframe src="./preview.php?idx='+record.data.ino+'" style="width:100%; height:100%;" frameborder="0"></iframe>'
											}).show();
										} else {
											Ext.Msg.show({title:"에러",msg:"할당할 매물이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
										}
									}}
								}
							}),
							new Ext.grid.GridPanel({
								title:"나의경매내역",
								id:"<?php echo $id; ?>ListPanel3",
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
														Ext.getCmp("<?php echo $id; ?>ListPanel3").getStore().reload();
													} else {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
													}
												},
												failure:function() {
													Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
												},
												params:{"action":"<?php echo $actionTarget; ?>","do":"return"}
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
										sortable:false,
										width:80,
										renderer:function(value) {
											if (value == "SUCCESS") return '<span style="color:blue;">낙찰</span>';
											else if (value == "INFLUENTIAL") return '<span style="color:blue;">낙찰유력</span>';
											else if (value == "FAIL") return '<span style="color:green;">낙찰실패</span>';
											else return '반환완료';
										}
									}
								],
								columnLines:true,
								store:store3,
								bbar:new Ext.PagingToolbar({
									store:store3,
									displayInfo:true
								})
							})
						]
					})
					
/*
					new Ext.grid.GridPanel({
						id:"<?php echo $id; ?>ListPanel",
						layout:"fit",
						border:false,
						autoScroll:true,
						columns:[
							new Ext.grid.RowNumberer(),
							{
								header:"상태",
								dataIndex:"status",
								sortable:false,
								width:70,
								renderer:function(value,p,record) {
									if (value == "ACTIVE") {
										if (record.data.ino == "0") return '<span style="color:red;">빈슬롯</span>';
										else return '<span style="color:blue;">활성화</span>';
									} else {
										return '<span style="color:#666666;">기간종료</span>';
									}
								}
							},{
								header:"시작일",
								dataIndex:"start_time",
								width:130
							},{
								header:"종료일",
								dataIndex:"end_time",
								minWidth:130
							},{
								header:"할당된 매물명",
								dataIndex:"title",
								sortable:false,
								minWidth:140,
								flex:1,
								renderer:function(value,p,record) {
									return '<span style="color:#EF5600;">[#'+record.data.ino+']</span> '+value;
								}
							},{
								header:"조회",
								dataIndex:"hit",
								width:40,
								renderer:GridNumberFormat
							}
						],
						columnLines:true,
						store:store,
						selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
						bbar:new Ext.PagingToolbar({
							store:store,
							displayInfo:true
						})
					})
*/
				],
				listeners:{show:{fn:function() {
					store1.load();
					store2.load();
					store3.load();
				}}}
			}).show();
		}
	}
});

ManagerModules.push(new MyDesktop.<?php echo $id; ?>());
ManagerShortcuts.push({name:"<?php echo $title; ?>",icon:"./images/<?php echo $id; ?>48.png",module:"<?php echo $id; ?>"});
</script>