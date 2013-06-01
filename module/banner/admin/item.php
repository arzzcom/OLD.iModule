<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/banner/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"item",get:"list"}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","code","mno","master","is_active","type","point","paid_point","start_date","end_date","bannerpath","bannertext","url",{name:"percent",type:"float"},"view","hit"]
	});

	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">배너번호 #'+record.data.idx+'</b>');
		
		menu.add({
			text:"배너수정",
			handler:function() {
				ItemFormFunction(record.data.idx);
			}
		});

		menu.add('-');
		
		menu.add({
			text:"배너삭제",
			handler:function () {
				Ext.Msg.show({title:"확인",msg:"배너를 삭제하면 모든 통계 및 배너이미지가 삭제됩니다.<br />배너를 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("배너를 삭제하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/banner/exec/Admin.do.php",
							success:function(response) {
								var data = Ext.JSON.decode(response.responseText);
								if (data.success == true) {
									Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
										Ext.getCmp("ListPanel").getStore().loadPage(1);
									}});
								} else {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
								}
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
							},
							params:{"action":"item","do":"delete","code":record.data.idx}
						});
					}
				}});
			}
		});

		e.stopEvent();
		menu.showAt(e.getXY());
	}

	function ItemFormFunction(idx) {
		if (idx) {
			var title = "배너추가";
		} else {
			var title = "배너수정";
		}
		
		new Ext.Window({
			id:"ItemWindow",
			title:title,
			width:650,
			height:500,
			modal:true,
			minWidth:650,
			layout:"fit",
			items:[
				new Ext.form.FormPanel({
					id:"ItemForm",
					bodyPadding:"10 10 5 10",
					border:false,
					autoScroll:true,
					fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%",allowBlank:false},
					items:[
						new Ext.form.FieldSet({
							title:"광고주정보",
							items:[
								new Ext.form.FieldContainer({
									fieldLabel:"광고주",
									layout:"hbox",
									items:[
										new Ext.form.Hidden({
											name:"mno",
											allowBlank:true
										}),
										new Ext.form.TextField({
											name:"master",
											readOnly:true,
											allowBlank:true,
											width:100,
											style:{marginRight:"5px"}
										}),
										new Ext.Button({
											text:"광고주검색",
											handler:function() {
												
											}
										})
									]
								}),
								new Ext.Panel({
									border:false,
									bodyPadding:"0 0 10 0",
									html:'<div class="boxDefault">광고주를 선택하면 해당 광고주회원은 광고관리시스템에서 이 광고에 대한 통계를 보거나, 광고수정등이 가능합니다.</div>'
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"광고영역선택",
							items:[
								new Ext.form.ComboBox({
									fieldLabel:"광고영역",
									name:"code",
									store:new Ext.data.JsonStore({
										proxy:{
											type:"ajax",
											simpleSortMode:true,
											url:"<?php echo $_ENV['dir']; ?>/module/banner/exec/Admin.get.php",
											reader:{type:"json",root:"lists",totalProperty:"totalCount"},
											extraParams:{action:"section",get:"list"}
										},
										remoteSort:true,
										sorters:[{property:"code",direction:"ASC"}],
										autoLoad:true,
										pageSize:50,
										fields:["code","title","type","point","filetype","width","height","allow_user","auto_active"]
									}),
									editable:false,
									mode:"local",
									valueField:"code",
									triggerAction:"all",
									tpl:'<tpl for="."><div class="x-boundlist-item">{title} ({code})</div></tpl>',
									displayTpl:'<tpl for=".">{title} ({code})</tpl>',
									emptyText:"광고를 게시할 광고영역을 선택하세요",
									listeners:{select:{fn:function(form,selected) {
										var record = selected.shift();
										Ext.getCmp("ItemForm").getForm().findField("type").setValue(record.data.type);
										Ext.getCmp("ItemForm").getForm().findField("point").setValue(record.data.point);
										var fileType = record.data.filetype.replace("IMG","GIF,PNG,JPG");
										if (fileType.search("GIF") > -1 || fileType.search("SWF") > -1) {
											Ext.getCmp("ItemForm").getForm().findField("bannerfile").emptyText = "가로 "+record.data.width+"픽셀, 세로 "+record.data.height+"픽셀";
											Ext.getCmp("ItemForm").getForm().findField("bannerfile").reset();
											Ext.getCmp("ItemForm").getForm().findField("bannerfile").show();
										} else {
											Ext.getCmp("ItemForm").getForm().findField("bannerfile").hide();
										}
										
										if (fileType.search("TEXT") > -1) {
											Ext.getCmp("ItemForm").getForm().findField("bannertext").show();
										} else {
											Ext.getCmp("ItemForm").getForm().findField("bannertext").hide();
										}
									}}}
								}),
								new Ext.Panel({
									border:false,
									bodyPadding:"0 0 10 0",
									html:'<div class="boxDefault">광고영역에 따라 광고유형 및 광고비 등의 정보가 자동으로 설정되며, 자동설정된 값을 변경시 해당 설정값으로 광고가 등록되게 됩니다.</div>'
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"광고유형 및 광고비설정",
							items:[
								new Ext.form.ComboBox({
									fieldLabel:"광고유형",
									name:"type",
									store:new Ext.data.ArrayStore({
										fields:["value","display"],
										data:[["CPC","CPC방식 (유효클릭당 정해진 배너비만큼 차감되어 배너선입금이 모두 소진되면 노출중단)"],["CPM","CPM방식 (30일동안 정해진 배너비로 고정적으로 노출 (클릭수와는 무관, 정액제)"]]
									}),
									editable:false,
									mode:"local",
									displayField:"display",
									valueField:"value",
									triggerAction:"all",
									emptyText:"광고유형을 선택하세요. (광고영역선택시 자동으로 설정됩니다.)",
									listeners:{change:{fn:function(form) {
										if (form.getValue() == "CPC") {
											Ext.getCmp("BannerTypeCPC1").show();
											Ext.getCmp("BannerTypeCPC2").show();
											Ext.getCmp("BannerTypeCPC1").enable();
											Ext.getCmp("BannerTypeCPC2").enable();
											Ext.getCmp("BannerTypeCPM").hide();
											Ext.getCmp("BannerTypeCPM").disable();
										} else {
											Ext.getCmp("BannerTypeCPC1").hide();
											Ext.getCmp("BannerTypeCPC2").hide();
											Ext.getCmp("BannerTypeCPC1").disable();
											Ext.getCmp("BannerTypeCPC2").disable();
											Ext.getCmp("BannerTypeCPM").show();
											Ext.getCmp("BannerTypeCPM").enable();
										}
									}}}
								}),
								new Ext.form.FieldContainer({
									id:"BannerTypeCPC1",
									fieldLabel:"클릭당광고비",
									layout:"hbox",
									hidden:true,
									disabled:true,
									items:[
										new Ext.form.NumberField({
											name:"point",
											width:80,
											value:0
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;포인트 (유효클릭당 선입금포인트에서 이 포인트금액만큼 차감됩니다.)",
											flex:1
										})
									]
								}),
								new Ext.form.FieldContainer({
									id:"BannerTypeCPC2",
									fieldLabel:"선입금포인트",
									layout:"hbox",
									hidden:true,
									disabled:true,
									items:[
										new Ext.form.NumberField({
											name:"paid_point",
											width:80,
											value:0
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;포인트 (이 포인트가 소진될때까지 광고가 노출됩니다.)",
											flex:1
										})
									]
								}),
								new Ext.form.FieldContainer({
									id:"BannerTypeCPM",
									fieldLabel:"광고노출일",
									layout:"hbox",
									hidden:true,
									disabled:true,
									items:[
										new Ext.form.DateField({
											name:"start_date",
											format:"Y-m-d",
											value:"<?php echo date('Y-m-d'); ?>",
											width:100,
											listeners:{change:{fn:function(form) {
												Ext.getCmp("ItemForm").getForm().findField("end_date").setValue(Ext.Date.format(Ext.Date.add(new Date(form.getValue()),Ext.Date.DAY,30),"Y-m-d"));
												Ext.getCmp("ItemForm").getForm().findField("end_date").setMinValue(form.getValue());
											}}}
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;부터&nbsp;"
										}),
										new Ext.form.DateField({
											name:"end_date",
											format:"Y-m-d",
											width:100,
											minValue:"<?php echo date('Y-m-d'); ?>",
											value:"<?php echo date('Y-m-d',mktime(0,0,0,date('m')+1,date('d'),date('Y'))); ?>"
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;까지"
										})
									]
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"배너설정",
							items:[
								new Ext.form.FileUploadField({
									fieldLabel:"배너파일",
									name:"bannerfile",
									allowBlank:true,
									buttonText:"",
									buttonConfig:{icon:"<?php echo $_ENV['dir']; ?>/images/common/icon_disk.png"},
									allowBlank:true
								}),
								new Ext.form.TextArea({
									fieldLabel:"텍스트광고문구",
									name:"bannertext",
									allowBlank:true,
									height:60,
									hidden:true,
									emptyText:"텍스트 광고문구를 입력하여 주십시오."
								}),
								new Ext.form.TextField({
									fieldLabel:"광고URL",
									name:"url",
									emptyText:"광고를 클릭시 이동될 주소를 입력하여 주십시오. (http:// 포함)"
								})
							]
						})
					]
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					handler:function() {
						Ext.getCmp("ItemForm").getForm().submit({
							url:"<?php echo $_ENV['dir']; ?>/module/banner/exec/Admin.do.php?action=item&do="+(idx ? "modify&idx="+idx : "add"),
							submitEmptyText:false,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:(idx ? "배너를 수정하고 있습니다." : "배너를 추가하고 있습니다."),
							success:function(form,action) {
								Ext.Msg.show({title:"안내",msg:"성공적으로 "+(idx ? "수정" : "추가")+"하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
									Ext.getCmp("ListPanel").getStore().loadPage(1);
									Ext.getCmp("ItemWindow").close();
								}});
							},
							failure:function(form,action) {
								if (action.result) {
									if (action.result.errors.bannerfile) {
										Ext.Msg.show({title:"에러",msg:action.result.errors.bannerfile,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
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
						Ext.getCmp("ItemWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				if (idx) {
					Ext.getCmp("ItemForm").getForm().load({
						url:"<?php echo $_ENV['dir']; ?>/module/banner/exec/Admin.get.php?action=item&get=data&idx="+(idx ? idx : ""),
						waitTitle:"잠시만 기다려주십시오.",
						waitMsg:"데이터를 로딩중입니다.",
						success:function(form,action) {
							form.findField("code").disable();
							var fileType = action.result.data.section.filetype.replace("IMG","GIF,PNG,JPG");
							if (fileType.search("GIF") > -1 || fileType.search("SWF") > -1) {
								form.findField("bannerfile").emptyText = "가로 "+action.result.data.section.width+"픽셀, 세로 "+action.result.data.section.height+"픽셀 (배너파일을 수정시에만 재등록하여 주십시오.)";
								form.findField("bannerfile").reset();
								form.findField("bannerfile").show();
							} else {
								form.findField("bannerfile").hide();
							}
							
							if (fileType.search("TEXT") > -1) {
								form.findField("bannertext").show();
							} else {
								form.findField("bannertext").hide();
							}
						},
						failure:function(form,action) {
							Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
						}
					});
				}
			}}}
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"배너아이템관리",
		layout:"fit",
		margin:"0 5 0 0",
		tbar:[
			new Ext.Button({
				text:"배너추가",
				icon:"<?php echo $_ENV['dir']; ?>/module/banner/images/admin/icon_layout_add.png",
				handler:function() {
					ItemFormFunction();
				}
			}),
			'-',
			new Ext.form.TextField({
				id:"Keyword",
				width:180,
				emptyText:"검색어를 입력하세요."
			}),
			new Ext.Button({
				text:"검색",
				icon:"<?php echo $_ENV['dir']; ?>/module/banner/images/admin/icon_magnifier.png",
				handler:function() {
					store.getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
					store.loadPage(1);
				}
			}),
			'-',
			new Ext.Button({
				text:"선택한 배너를&nbsp;",
				icon:"<?php echo $_ENV['dir']; ?>/module/banner/images/admin/icon_tick.png",
				menu:new Ext.menu.Menu({
					items:[{
						text:"선택 배너영역 삭제",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"배너영역을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							var codes = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								codes.push(checked[i].get("code"));
							}
							
							Ext.Msg.show({title:"확인",msg:"배너영역을 삭제하면 해당 배너영역의 모든 배너가 삭제됩니다.<br />배너영역을 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.Msg.wait("배너영역을 삭제하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/banner/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("ListPanel").getStore().loadPage(1);
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"section","do":"delete","code":codes.join(",")}
									});
								}
							}});
						}
					},'-',{
						text:"선택 배너 활성화",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"배너를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							var idxs = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								idxs.push(checked[i].get("idx"));
							}
							
							Ext.Msg.show({title:"확인",msg:"선택 배너를 활성화 할때 CPM방식의 배너중 시작일이 오늘날짜보다 이전이면, 오늘날짜 기준으로 활성화하시겠습니까?<br />아니오를 누르면 기존 시작일을 유지하고 상태만 활성화로 변경됩니다.",buttons:Ext.Msg.YESNOCANCEL,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes" || button == "no") {
									var reset = button == "yes" ? "TRUE" : "FALSE";
									Ext.Msg.wait("배너를 활성화하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/banner/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("ListPanel").getStore().loadPage(1);
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"item","do":"activemode","value":"TRUE","reset":reset,"idx":idxs.join(",")}
									});
								}
							}});
						}
					},{
						text:"선택 배너 비활성화",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"배너를 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							var idxs = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								idxs.push(checked[i].get("idx"));
							}
							
							Ext.Msg.show({title:"확인",msg:"선택 배너를 비활성화하시겠습니까?<br />게시기간 및 잔여포인트가 남아있어도 광고가 노출되지 않습니다.",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes" || button == "no") {
									Ext.Msg.wait("배너를 비활성화하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/banner/exec/Admin.do.php",
										success:function(response) {
											var data = Ext.JSON.decode(response.responseText);
											if (data.success == true) {
												Ext.Msg.show({title:"안내",msg:"성공적으로 처리하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function() {
													Ext.getCmp("ListPanel").getStore().loadPage(1);
												}});
											} else {
												Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
											}
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.WARNING});
										},
										params:{"action":"item","do":"activemode","value":"FALSE","idx":idxs.join(",")}
									});
								}
							}});
						}
					}]
				})
			})
		],
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				layout:"fit",
				border:false,
				columns:[
					new Ext.grid.RowNumberer({
						header:"번호",
						dataIndex:"idx",
						sortable:true,
						width:60,
						align:"left",
						renderer:function(value,p,record) {
							p.tdCls = Ext.baseCSSPrefix + 'grid-cell-special';
							return GridNumberFormat(value);
						}
					}),{
						header:"배너영역",
						dataIndex:"code",
						sortable:true,
						width:150
					},{
						header:"배너관리자",
						dataIndex:"master",
						sortable:true,
						width:100
					},{
						header:"형식",
						dataIndex:"type",
						sortable:true,
						width:60
					},{
						header:"배너게시기간",
						width:200,
						renderer:function(value,p,record) {
							if (record.data.type == "CPC") {
								if (record.data.paid_point == "0") {
									return '<span style="color:#666666;">배너게시기간 만료됨</span>';
								} else {
									return '잔여포인트 <span style="color:blue;">('+GetNumberFormat(record.data.paid_point)+'포인트)</span> 소진시까지';
								}
							} else {
								if (record.data.start_date == "0000-00-00") {
									return '<span style="color:red;">등록후 아직 게시상태로 변경된적 없음</span>';
								}
								if (new Date(record.data.start_date).getTime() > new Date().getTime()) {
									return '<span style="color:#666666;">배너게시기간이 아님 ('+record.data.start_date+'시작)</span>';
								} else if (new Date(record.data.end_date).getTime() < new Date().getTime()) {
									return '<span style="color:#666666;">배너게시기간 만료됨 ('+record.data.end_date+'만료)</span>';
								} else {
									return record.data.start_date+' 부터 '+record.data.end_date+' 까지';
								}
							}
						}
					},{
						header:"상태",
						dataIndex:"is_active",
						sortable:true,
						width:60,
						renderer:function(value) {
							if (value == "TRUE") return '<span style="color:blue;">게시중</span>';
							else return '<span style="color:red;">게시대기</span>';
						}
					},{
						header:"클릭시이동될 주소",
						dataIndex:"url",
						sortable:true,
						minWidth:150,
						flex:1
					},{
						header:"누적노출수",
						dataIndex:"view",
						sortable:false,
						width:80,
						renderer:function(value) {
							return '<div style="font-family:tahoma; text-align:right;">'+GetNumberFormat(value)+'회</div>'
						}
					},{
						header:"누적클릭수",
						dataIndex:"hit",
						sortable:false,
						width:80,
						renderer:function(value) {
							return '<div style="font-family:tahoma; text-align:right;">'+GetNumberFormat(value)+'회</div>'
						}
					},{
						header:"노출확률",
						dataIndex:"percent",
						sortable:false,
						width:80,
						renderer:function(value) {
							return '<div style="font-family:tahoma; text-align:right;">'+value.toFixed(2)+'%</div>'
						}
					}
				],
				store:store,
				columnLines:true,
				selModel:new Ext.selection.CheckboxModel({injectCheckbox:"last"}),
				bbar:new Ext.PagingToolbar({
					store:store,
					displayInfo:true
				}),
				listeners:{
					itemdblclick:{fn:function(grid,record) {
						
					}},
					itemcontextmenu:ItemContextMenu
				}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>