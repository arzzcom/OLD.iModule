<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var MonthListStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:["date","display"]
		}),
		remoteSort:false,
		sortInfo:{field:"date",direction:"ASC"},
		baseParams:{"action":"monthly","wno":"<?php echo $this->wno; ?>"}
	});

	var OrderStore = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"title","date",{name:"item",type:"int"},"is_confirm","is_estimate","is_order","is_contract","is_complete","etc"]
		}),
		remoteSort:false,
		sortInfo:{field:"date",direction:"ASC"},
		baseParams:{"wno":"<?php echo $this->wno; ?>","action":"order","get":"list","date":"<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>"}
	});


	function ShowOrderFunction(grid,idx,e) {
		new Ext.Window({
			id:"OrderWindow",
			title:grid.getStore().getAt(idx).get("title")+" 발주요청서 보기",
			width:950,
			height:550,
			modal:true,
			maximizable:true,
			layout:"border",
			items:[
				new Ext.grid.GridPanel({
					title:"품목보기",
					split:true,
					region:"center",
					id:"OrderListPanel",
					margins:"5 5 0 5",
					border:true,
					cm:new Ext.grid.ColumnModel([
						new Ext.grid.RowNumberer(),
						{
							header:"그룹",
							dataIndex:"workgroup",
							width:80
						},{
							header:"공종명",
							dataIndex:"worktype",
							width:100
						},{
							header:"품명",
							dataIndex:"title",
							width:250,
							renderer:GridContractItemNotFound
						},{
							header:"규격",
							dataIndex:"size",
							width:100,
							renderer:GridContractItemNotFound
						},{
							header:"단위",
							dataIndex:"unit",
							width:60,
							renderer:GridContractItemNotFound
						},{
							header:"계약",
							dataIndex:"contract_ea",
							width:50,
							renderer:GridNumberFormat
						},{
							header:"발주",
							dataIndex:"order_ea",
							width:180,
							renderer:GridItemOrderEA
						},{
							header:"수량",
							dataIndex:"ea",
							width:50,
							renderer:GridNumberFormat
						}
					]),
					store:new Ext.data.Store({
						proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
						reader:new Ext.data.JsonReader({
							root:"lists",
							totalProperty:"totalCount",
							fields:["code","workgroup","worktype","title","size","unit",{name:"contract_ea",type:"float"},"order_ea",{name:"ea",type:"float"},{name:"sort",type:"int"}]
						}),
						remoteSort:false,
						sortInfo:{field:"sort",direction:"ASC"},
						baseParams:{"wno":"<?php echo $this->wno; ?>","action":"order","get":"data","idx":grid.getStore().getAt(idx).get("idx")}
					}),
					sm:new Ext.grid.CheckboxSelectionModel()
				}),
				new Ext.Panel({
					id:"OrderEtcPanel",
					title:"비고",
					region:"south",
					split:true,
					margins:"0 5 5 5",
					height:90,
					items:[
						new Ext.form.TextArea({
							id:"OrderEtc",
							style:"margin:5px;",
							value:GetExtReplace(grid.getStore().getAt(idx).get("etc"))
						})
					],
					listeners:{resize:{fn:function() {
						Ext.getCmp("OrderEtc").setWidth(Ext.getCmp("OrderEtcPanel").getInnerWidth()-12);
						Ext.getCmp("OrderEtc").setHeight(Ext.getCmp("OrderEtcPanel").getInnerHeight()-12);
					}}}
				})
			],
			buttons:[
				new Ext.Button({
					text:"엑셀파일로 변환",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_page_white_excel.png",
					handler:function() {
						ExcelConvert("<?php echo $this->moduleDir; ?>/exec/GetExcel.do.php?action=workspace&get=order&idx="+grid.getStore().getAt(idx).get("idx"));
					}
				}),
				new Ext.Button({
					text:"닫기",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("OrderWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				Ext.getCmp("OrderListPanel").getStore().load();
				Ext.getCmp("OrderEtc").setWidth(Ext.getCmp("OrderEtcPanel").getInnerWidth()-12);
				Ext.getCmp("OrderEtc").setHeight(Ext.getCmp("OrderEtcPanel").getInnerHeight()-12);
			}}}
		}).show();
	}

	function WriteOrderFunction() {
		new Ext.Window({
			id:"OrderWindow",
			title:"발주요청서 작성",
			width:950,
			height:550,
			modal:true,
			maximizable:true,
			layout:"fit",
			items:[
				new Ext.Panel({
					layout:"border",
					border:false,
					items:[
						GridContractSearchList("workspace","<?php echo $this->wno; ?>"),
						new Ext.grid.EditorGridPanel({
							id:"OrderListPanel",
							title:"발주요청서 작성",
							margins:"0 5 0 5",
							region:"center",
							tbar:[
								new Ext.form.TextField({
									id:"OrderTitle",
									width:210,
									emptyText:"발주요청서 제목을 입력하여 주십시오."
								}),
								'-',
								new Ext.Button({
									text:"선택항목추가하기",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_arrow_down.png",
									handler:function() {
										var checked = Ext.getCmp("ContractSearchList").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"추가할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										}

										Ext.getCmp("OrderListPanel").stopEditing();
										for (var i=0, loop=checked.length;i<loop;i++) {
											var data = checked[i];
											var insert = new Array();
											insert["is_new"] = "FALSE";
											insert["workgroup"] = data.get("workgroup");
											insert["worktype"] = data.get("worktype");
											insert["gno"] = data.get("gno");
											insert["tno"] = data.get("tno");
											insert["code"] = data.get("code");
											insert["itemcode"] = data.get("itemcode");
											insert["title"] = data.get("title");
											insert["size"] = data.get("size");
											insert["unit"] = data.get("unit");
											insert["order_ea"] = data.get("order_ea");

											GridInsertRow(Ext.getCmp("OrderListPanel"),insert);
										}
										Ext.getCmp("OrderListPanel").startEditing(0,0);
									}
								}),
								'-',
								new Ext.Button({
									text:"새항목추가하기",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_row_insert.png",
									handler:function() {
										var insert = new Array();
										insert["is_new"] = "TRUE";
										insert["order_ea"] = "0,0,0,0";
										GridInsertRow(Ext.getCmp("OrderListPanel"),insert);
									}
								}),
								new Ext.Button({
									text:"선택항목삭제하기",
									icon:"<?php echo $this->moduleDir; ?>/images/common/icon_table_row_delete.png",
									handler:function() {
										GridDeleteRow(Ext.getCmp("OrderListPanel"));
									}
								})
							],
							cm:new Ext.grid.ColumnModel([
								new Ext.grid.CheckboxSelectionModel(),
								{
									header:"그룹",
									dataIndex:"gno",
									width:80,
									sortable:false,
									renderer:function(value,p,record,row,col,store) {
										return GridWorkgroup(value,p,record,Ext.getCmp("OrderListPanel").getColumnModel().getCellEditor(col,row).field);
									}
								},{
									header:"공종명",
									dataIndex:"tno",
									width:120,
									sortable:false,
									renderer:function(value,p,record,row,col,store) {
										return GridWorktype(value,p,record,Ext.getCmp("OrderListPanel").getColumnModel().getCellEditor(col,row).field);
									}
								},{
									header:"품명",
									dataIndex:"title",
									width:280,
									sortable:false,
									renderer:GridContractItemNotFound
								},{
									header:"규격",
									dataIndex:"size",
									width:100,
									renderer:GridContractItemNotFound,
									editor:new Ext.form.TextField({selectOnFocus:true})
								},{
									header:"단위",
									dataIndex:"unit",
									width:50,
									sortable:false,
									renderer:GridContractItemNotFound,
									editor:new Ext.form.TextField({selectOnFocus:true})
								},{
									header:"발주수량",
									dataIndex:"order_ea",
									width:180,
									sortable:false,
									renderer:GridItemOrderEA
								},{
									header:"수량",
									dataIndex:"ea",
									width:60,
									sortable:false,
									renderer:GridNumberFormat,
									editor:new Ext.form.NumberField({selectOnFocus:true})
								}
							]),
							store:new Ext.data.SimpleStore({
								fields:["is_new","itemcode","code","workgroup","gno","worktype","tno","title","size","unit",{name:"ea",type:"float"},"order_ea"]
							}),
							sm:new Ext.grid.CheckboxSelectionModel(),
							clicksToEdit:1,
							trackMouseOver:true,
							listeners:{
								render:{fn:function() {
									GridEditorAutoMatchItem(Ext.getCmp("OrderListPanel"),"<?php echo $this->wno; ?>");
									GridEditorWorkgroupType(Ext.getCmp("OrderListPanel"),"<?php echo $this->wno; ?>");
								}},
								beforeedit:{fn:function(object) {
									GridEditorBeforeWorkgroupType(object);
								}},
								afteredit:{fn:function(object) {
									GridAutoMatchItem(object,"<?php echo $this->wno; ?>");
									GridEditorAfterWorkgroupType(object)

									if (object.field == "ea" && object.value == "") {
										object.grid.getStore().getAt(object.row).set(object.field,0);
									}

									if (object.field == "gno") {
										object.grid.getStore().getAt(object.row).set("tno","");
									}
								}}
							}
						}),
						new Ext.Panel({
							id:"OrderEtcPanel",
							title:"비고",
							region:"south",
							collapsible:true,
							collapsed:true,
							split:true,
							margins:"0 5 5 5",
							height:90,
							items:[
								new Ext.form.TextArea({
									id:"OrderEtc",
									style:"margin:5px;"
								})
							],
							listeners:{
								resize:{fn:function() {
									Ext.getCmp("OrderEtc").setWidth(Ext.getCmp("OrderEtcPanel").getInnerWidth()-12);
									Ext.getCmp("OrderEtc").setHeight(Ext.getCmp("OrderEtcPanel").getInnerHeight()-12);
								}}
							}
						})
					]
				})
			],
			buttons:[
				new Ext.Button({
					text:"확인",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_tick.png",
					handler:function() {
						if (Ext.getCmp("OrderListPanel").getStore().getCount() == 0) {
							Ext.Msg.show({title:"에러",msg:"발주요청할 품명을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							return false;
						}

						if (!Ext.getCmp("OrderTitle").getValue()) {
							Ext.getCmp("OrderTitle").setValue(Ext.getCmp("OrderListPanel").getStore().getAt(0).get("title")+"외 "+(Ext.getCmp("OrderListPanel").getStore().getCount()-1)+"개 품목");
						}

						var data = GetGridData(Ext.getCmp("OrderListPanel"));

						Ext.Ajax.request({
							url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
							success:function() {
								Ext.Msg.show({title:"안내",msg:"성공적으로 요청하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								Ext.getCmp("ListPanel").getStore().reload();
								Ext.getCmp("OrderWindow").close();
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							},
							headers:{},
							params:{"action":"order","do":"add","wno":<?php echo $this->wno; ?>,"data":data,"title":Ext.getCmp("OrderTitle").getValue(),"etc":Ext.getCmp("OrderEtc").getValue()}
						});
					}
				}),
				new Ext.Button({
					text:"취소",
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
					handler:function() {
						Ext.getCmp("OrderWindow").close();
					}
				})
			]
		}).show();
	}
	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"자재발주관리",
		layout:"fit",
		items:[
			new Ext.grid.GridPanel({
				id:"ListPanel",
				border:false,
				autoScroll:true,
				tbar:[
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_left.png",
						text:"이전달",
						handler:function() {
							if (Ext.getCmp("month").selectedIndex == 0) {
								Ext.Msg.show({title:"에러",msg:"이전달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							} else {
								Ext.getCmp("ListPanel").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex-1).get("date");
								Ext.getCmp("month").setValue(Ext.getCmp("ListPanel").getStore().baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex - 1;
								Ext.getCmp("ListPanel").getStore().reload();
							}
						}
					}),
					' ',
					new Ext.form.ComboBox({
						id:"month",
						store:MonthListStore,
						displayField:"display",
						valueField:"date",
						typeAhead:true,
						mode:"local",
						triggerAction:"all",
						width:90,
						editable:false,
						listeners:{
							render:{fn:function(form) {
								form.getStore().load();
								form.getStore().on("load",function() {
									form.setValue("<?php echo Request('iErpMonth','cookie') != null ? Request('iErpMonth','cookie') : GetTime('Y-m'); ?>");
									for (var i=0, loop=form.getStore().getCount();i<loop;i++) {
										if (form.getStore().getAt(i).get("date") == form.getValue()) {
											form.selectedIndex = i;
											break;
										}
									}

									if (form.selectedIndex == -1) {
										form.selectedIndex = form.getStore().getCount()-1;
										form.setValue(form.getStore().getAt(form.getStore().getCount()-1).get("date"));
										SetCookie("iErpMonth",form.getValue());
									}

									Ext.getCmp("ListPanel").getStore().baseParams.date = Ext.getCmp("month").getValue();
									Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:30}});
								});
							}},
							select:{fn:function(form) {
								Ext.getCmp("ListPanel").getStore().baseParams.date = form.getValue();
								Ext.getCmp("ListPanel").getStore().load({params:{start:0,limit:30}});
							}}
						}
					}),
					' ',
					new Ext.Button({
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_control_right.png",
						iconAlign:"right",
						text:"다음달",
						handler:function() {
							if (Ext.getCmp("month").selectedIndex+1 == Ext.getCmp("month").getStore().getCount()) {
								Ext.Msg.show({title:"에러",msg:"다음달 기록이 없습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
							} else {
								Ext.getCmp("ListPanel").getStore().baseParams.date = Ext.getCmp("month").getStore().getAt(Ext.getCmp("month").selectedIndex+1).get("date");
								Ext.getCmp("month").setValue(Ext.getCmp("ListPanel").getStore().baseParams.date);
								Ext.getCmp("month").selectedIndex = Ext.getCmp("month").selectedIndex + 1;
								Ext.getCmp("ListPanel").getStore().reload();
							}
						}
					}),
					'-',
					new Ext.Button({
						text:"발주요청서 작성",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_paste_plain.png",
						handler:function() {
							WriteOrderFunction();
						}
					}),
					'-',
					new Ext.Button({
						id:"DeleteButton",
						text:"발주요청서 삭제",
						icon:"<?php echo $this->moduleDir; ?>/images/common/icon_page_delete.png",
						handler:function() {
							var checked = Ext.getCmp("ListTab").getActiveTab().selModel.getSelections();

							if (checked.length == 0) {
								Ext.Msg.show({title:"에러",msg:"삭제할 항목을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
								return false;
							}

							var idxs = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								idxs[i] = checked[0].get("idx");
							}
							var idx = idxs.join(",");

							Ext.Ajax.request({
								url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
								success:function() {
									Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
									Ext.getCmp("ListPanel").getStore().reload();
								},
								failure:function() {
									Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								},
								headers:{},
								params:{"action":"order","do":"delete","wno":<?php echo $this->wno; ?>,"idx":idx}
							});
						}
					})
				],
				cm:new Ext.grid.ColumnModel([
					new Ext.grid.RowNumberer(),
					{
						dataIndex:"idx",
						hidden:true,
						hideable:false
					},{
						header:"요청서명",
						dataIndex:"title",
						sortable:false,
						width:450
					},{
						header:"품목수",
						dataIndex:"item",
						width:80,
						sortable:false,
						renderer:GridNumberFormat
					},{
						header:"상태",
						width:150,
						sortable:true,
						renderer:function(value,p,record) {
							var sHTML = '<div style="text-align:center; font:0/0 arial;">';
							sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_confirm_';
							sHTML+= record.data.is_confirm == "TRUE" ? "on" : "off";
							sHTML+= '.gif" style="margin-right:1px;" />';
							sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_estimate_';
							sHTML+= record.data.is_estimate == "TRUE" ? "on" : "off";
							sHTML+= '.gif" style="margin-right:1px;" />';
							sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_order_';
							sHTML+= record.data.is_order == "TRUE" ? "on" : "off";
							sHTML+= '.gif" style="margin-right:1px;" />';
							sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_contract_';
							sHTML+= record.data.is_contract == "TRUE" ? "on" : "off";
							sHTML+= '.gif" style="margin-right:1px;" />';
							sHTML+= '<img src="<?php echo $this->moduleDir; ?>/images/common/icon_stored_';
							sHTML+= record.data.is_complete == "TRUE" ? "on" : "off";
							sHTML+= '.gif" />';
							sHTML+= '</div>';

							return sHTML;
						}
					},{
						header:"요청일",
						dataIndex:"date",
						sortable:true,
						width:110
					},
					new Ext.grid.CheckboxSelectionModel()
				]),
				sm:new Ext.grid.CheckboxSelectionModel(),
				store:OrderStore,
				loadMask:{msg:"데이터를 로딩중입니다."},
				bbar:new Ext.PagingToolbar({
					pageSize:30,
					store:OrderStore,
					displayInfo:true,
					displayMsg:'{0} - {1} of {2}',
					emptyMsg:"데이터없음"
				}),
				listeners:{
					rowdblclick:ShowOrderFunction,
					rowcontextmenu:{fn:function(grid,idx,e) {
						GridContextmenuSelect(grid,idx);

						var data = grid.getStore().getAt(idx);
						var menu = new Ext.menu.Menu();
						menu.add('<b class="menu-title">'+data.get("title")+'</b>');

						menu.add({
							text:"입고처리",
							icon:(Ext.isIE6 ? "" : "<?php echo $this->moduleDir; ?>/images/common/icon_table_edit.png"),
							handler:function() {
								if (data.get("is_contract") == "FALSE") {
									Ext.Msg.show({title:"에러",msg:"본사의 발주계약이 아직 되지 않았습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
									return false;
								} else {
									Ext.Ajax.request({
										url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
										success:function() {
											Ext.Msg.show({title:"안내",msg:"성공적으로 처리되었습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
											Ext.getCmp("ListPanel").getStore().reload();
										},
										failure:function() {
											Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
										},
										headers:{},
										params:{"action":"order","do":"complete","wno":<?php echo $this->wno; ?>,"idx":data.get("idx")}
									});
								}
							}
						});
						e.stopEvent();
						menu.showAt(e.getXY());
					}}
				}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>