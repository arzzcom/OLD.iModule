<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/wysiwyg/script/wysiwyg.js"></script>
<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;

	var store = new Ext.data.JsonStore({
		proxy:{
			type:"ajax",
			simpleSortMode:true,
			url:"<?php echo $_ENV['dir']; ?>/module/coupon/exec/Admin.get.php",
			reader:{type:"json",root:"lists",totalProperty:"totalCount"},
			extraParams:{action:"item",get:"list"}
		},
		remoteSort:true,
		sorters:[{property:"idx",direction:"DESC"}],
		autoLoad:true,
		pageSize:50,
		fields:["idx","code","category","code","title","infor",{name:"point",type:"int"},{name:"expire",type:"int"},{name:"ea",type:"int"},"sell","is_new","is_vote","is_gift"]
	});

	function ItemContextMenu(grid,record,row,index,e) {
		grid.getSelectionModel().select(index);
		var menu = new Ext.menu.Menu();
		
		menu.add('<b class="menu-title">'+record.data.title+'</b>');
		
		menu.add({
			text:"쿠폰이미지보기",
			handler:function() {
				new Ext.Window({
					id:"PreviewWindow",
					title:"쿠폰이미지보기",
					modal:true,
					maxWidth:800,
					maxHeight:500,
					autoScroll:true,
					html:'<img src="<?php echo $_ENV['userfileDir']; ?>/coupon/'+record.data.idx+'.gif" onload="Ext.getCmp(\'PreviewWindow\').doLayout().center()" />'
				}).show();
			}
		});
		
		menu.add({
			text:"쿠폰아이템수정",
			handler:function() {
				ItemFormFunction(record.data.idx);
			}
		});

		menu.add('-');
		
		menu.add({
			text:"쿠폰아이템삭제",
			handler:function () {
				Ext.Msg.show({title:"확인",msg:"쿠폰아이템을 삭제하면 모든 구입내역 및 쿠폰아이템이미지가 삭제됩니다.<br />쿠폰아이템을 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
					if (button == "yes") {
						Ext.Msg.wait("쿠폰아이템을 삭제하고 있습니다.","잠시만 기다려주십시오.");
						Ext.Ajax.request({
							url:"<?php echo $_ENV['dir']; ?>/module/coupon/exec/Admin.do.php",
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
							params:{"action":"item","do":"delete","idx":record.data.idx}
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
			var title = "쿠폰아이템추가";
		} else {
			var title = "쿠폰아이템수정";
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
					fieldDefaults:{labelAlign:"right",labelWidth:80,anchor:"100%",allowBlank:false},
					items:[
						new Ext.form.FieldSet({
							title:"쿠폰기본정보",
							items:[
								new Ext.form.ComboBox({
									fieldLabel:"카테고리",
									name:"category",
									store:new Ext.data.JsonStore({
										proxy:{
											type:"ajax",
											simpleSortMode:true,
											url:"<?php echo $_ENV['dir']; ?>/module/coupon/exec/Admin.get.php",
											reader:{type:"json",root:"lists",totalProperty:"totalCount"},
											extraParams:{action:"category",get:"list"}
										},
										remoteSort:true,
										sorters:[{property:"sort",direction:"ASC"}],
										autoLoad:true,
										pageSize:50,
										fields:["idx","category",{name:"sort",type:"int"}]
									}),
									editable:false,
									allowBlank:false,
									mode:"local",
									valueField:"idx",
									displayField:"category",
									triggerAction:"all",
									emptyText:"카테고리를 선택하세요."
								}),
								new Ext.form.TextField({
									fieldLabel:"쿠폰제목",
									name:"title",
									allowBlank:false
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"쿠폰코드",
									layout:"hbox",
									items:[
										new Ext.form.TextField({
											name:"code",
											allowBlank:false,
											flex:1
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;(중복되지 않는 쿠폰고유코드를 입력하세요. 차후API에서 사용)"
										})
									]
								}),
								new Ext.form.TextField({
									fieldLabel:"간략정보",
									name:"infor",
									allowBlank:false
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"쿠폰가격",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"point",
											width:100,
											value:0,
											allowBlank:false
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;포인트 (0 입력시 무료)"
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"판매수량",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"ea",
											width:100,
											value:0,
											allowBlank:false
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;개 (0 입력시 매진)"
										})
									]
								}),
								new Ext.form.FieldContainer({
									fieldLabel:"만료일",
									layout:"hbox",
									items:[
										new Ext.form.NumberField({
											name:"expire",
											width:60,
											value:0,
											allowBlank:false
										}),
										new Ext.form.DisplayField({
											value:"&nbsp;일 (구매일로 부터 설정한 일자까지만 사용할 수 있습니다. 0 입력시 무제한)"
										})
									]
								}),
								new Ext.form.FileUploadField({
									fieldLabel:"쿠폰이미지",
									name:"file",
									allowBlank:(idx ? true : false),
									buttonText:"",
									buttonConfig:{icon:"<?php echo $_ENV['dir']; ?>/images/common/icon_disk.png"},
									emptyText:(idx ? "이미지를 변경하고자할 경우 새로운 GIF파일을 선택하십시오." : "GIF이미지만 업로드 가능합니다.")
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"쿠폰설정",
							items:[
								new Ext.form.Checkbox({
									name:"is_new",
									boxLabel:"신규 쿠폰으로 표시합니다."
								}),
								new Ext.form.Checkbox({
									name:"is_vote",
									boxLabel:"추천 쿠폰으로 표시합니다."
								}),
								new Ext.form.Checkbox({
									name:"is_gift",
									boxLabel:"이 쿠폰은 선물이 가능합니다."
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"상세설명",
							items:[
								new Ext.form.TextArea({
									id:"Wysiwyg",
									anchor:"100%",
									name:"content",
									height:200
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
						oEditors.getById["Wysiwyg-inputEl"].exec("UPDATE_IR_FIELD",[]);
						Ext.getCmp("ItemForm").getForm().submit({
							url:"<?php echo $_ENV['dir']; ?>/module/coupon/exec/Admin.do.php?action=item&do="+(idx ? "modify&idx="+idx : "add"),
							submitEmptyText:false,
							waitTitle:"잠시만 기다려주십시오.",
							waitMsg:(idx ? "쿠폰아이템를 수정하고 있습니다." : "쿠폰아이템를 추가하고 있습니다."),
							success:function(form,action) {
								Ext.Msg.show({title:"안내",msg:"성공적으로 "+(idx ? "수정" : "추가")+"하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
									Ext.getCmp("ListPanel").getStore().loadPage(1);
									Ext.getCmp("ItemWindow").close();
								}});
							},
							failure:function(form,action) {
								if (action.result) {
									if (action.result.errors.file) {
										Ext.Msg.show({title:"에러",msg:action.result.errors.file,buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
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
						url:"<?php echo $_ENV['dir']; ?>/module/coupon/exec/Admin.get.php?action=item&get=data&idx="+(idx ? idx : ""),
						waitTitle:"잠시만 기다려주십시오.",
						waitMsg:"데이터를 로딩중입니다.",
						success:function(form,action) {
							
						},
						failure:function(form,action) {
							Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
						}
					});
				}
				
				nhn.husky.EZCreator.createInIFrame({oAppRef:oEditors,elPlaceHolder:"Wysiwyg-inputEl",sSkinURI:"<?php echo $_ENV['dir']; ?>/module/wysiwyg/wysiwyg.php?resize=false",fCreator:"createSEditorInIFrame"});
			}}}
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"쿠폰아이템관리",
		layout:"fit",
		margin:"0 5 0 0",
		tbar:[
			new Ext.Button({
				text:"쿠폰아이템추가",
				icon:"<?php echo $_ENV['dir']; ?>/module/coupon/images/admin/icon_item.png",
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
				icon:"<?php echo $_ENV['dir']; ?>/module/coupon/images/admin/icon_magnifier.png",
				handler:function() {
					store.getProxy().setExtraParam("keyword",Ext.getCmp("Keyword").getValue());
					store.loadPage(1);
				}
			}),
			'-',
			new Ext.Button({
				text:"선택한 쿠폰아이템을&nbsp;",
				icon:"<?php echo $_ENV['dir']; ?>/module/coupon/images/admin/icon_tick.png",
				menu:new Ext.menu.Menu({
					items:[{
						text:"선택 쿠폰아이템 삭제",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").getSelectionModel().getSelection();
							if (checked.length == 0) {
								Ext.Msg.show({title:"안내",msg:"쿠폰아이템을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
								return;
							}
							
							var idxs = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								idxs.push(checked[i].get("idx"));
							}
							
							Ext.Msg.show({title:"확인",msg:"쿠폰아이템를 삭제하면 모든 구입내역 및 쿠폰아이템이미지가 삭제됩니다.<br />쿠폰아이템을 삭제하시겠습니까?",buttons:Ext.Msg.YESNO,icon:Ext.Msg.QUESTION,fn:function(button) {
								if (button == "yes") {
									Ext.Msg.wait("쿠폰아이템을 삭제하고 있습니다.","잠시만 기다려주십시오.");
									Ext.Ajax.request({
										url:"<?php echo $_ENV['dir']; ?>/module/coupon/exec/Admin.do.php",
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
										params:{"action":"item","do":"delete","idx":idxs.join(",")}
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
					new Ext.grid.RowNumberer(),
					{
						header:"카테고리",
						dataIndex:"category",
						width:100
					},{
						header:"쿠폰제목",
						dataIndex:"title",
						sortable:true,
						width:200
					},{
						header:"쿠폰코드",
						dataIndex:"code",
						sortable:true,
						width:180
					},{
						header:"쿠폰소개",
						dataIndex:"infor",
						flex:1,
						renderer:function(value,p,record) {
							var sHTML = "";
							if (record.data.is_new == "TRUE") sHTML+= '<span style="color:blue;">[신규]</span> ';
							if (record.data.is_vote == "TRUE") sHTML+= '<span style="color:red;">[추천]</span> ';
							if (record.data.is_gift == "TRUE") sHTML+= '<span style="color:green;">[선물가능]</span> ';
							sHTML+= value;
							
							return sHTML;
						}
					},{
						header:"구매가격",
						dataIndex:"point",
						width:120,
						renderer:function(value) {
							if (value == 0) {
								return '<div style="color:blue; text-align:center;">무료</div>';
							} else {
								if (value >= 1000000) {
									return '<div style="text-align:right; color:violet;">'+GetNumberFormat(value)+'포인트</div>';
								} else if (value >= 100000) {
									return '<div style="text-align:right; color:red;">'+GetNumberFormat(value)+'포인트</div>';
								} else if (value >= 10000) {
									return '<div style="text-align:right; color:orange;">'+GetNumberFormat(value)+'포인트</div>';
								} else {
									return '<div style="text-align:right;">'+GetNumberFormat(value)+'포인트</div>';
								}
							}
						}
					},{
						header:"만료일",
						dataIndex:"expire",
						sortable:true,
						width:80,
						renderer:function(value) {
							if (value == 0) return '<div style="color:blue; text-align:center;">무제한</div>';
							else return '<div style="color:red; text-align:center;">'+GetNumberFormat(value)+'일</div>';
						}
					},{
						header:"판매수량",
						dataIndex:"sell",
						width:60,
						renderer:function(value) {
							return '<div style="color:blue; text-align:right;">'+GetNumberFormat(value)+'EA</div>';
						}
					},{
						header:"남은수량",
						dataIndex:"ea",
						sortable:true,
						width:60,
						renderer:function(value) {
							if (value == 0) return '<div style="color:red; text-align:center;">매진</div>';
							else return '<div style="color:blue; text-align:right;">'+GetNumberFormat(value)+'EA</div>';
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