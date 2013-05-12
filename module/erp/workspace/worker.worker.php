<script type="text/javascript">
function TakePhotoNew(result) {
	var object = document.getElementById("TakePhoto");
	if (result == false) {
		Ext.Msg.show({title:"에러",msg:"사진을 저장하지 못하였습니다.<br />다시 한번 시도하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function() {
			object.cancel();
		}});
	} else {
		if (result == "FAIL") {
			Ext.Msg.show({title:"에러",msg:"사진을 저장하지 못하였습니다.<br />다시 한번 시도하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function() {
				object.cancel();
			}});
		} else {
			result = result.split("|");
			var image = document.images["PreviewPhoto"];
			image.src = result[1]+"?rnd="+Math.random();

			Ext.getCmp("WorkerForm").getForm().findField("photo").setValue(result[0]);
			Ext.getCmp("TakePhotoWindow").close();
		}
	}
}

ContentArea = function(viewport) {
	this.viewport = viewport;

	var WorkerListStore1 = new Ext.data.GroupingStore({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"name","phone","group","grade","workernum","jumin","enter_date","workstart_date","workend_date","pay_type",{name:"payment",type:"int"},"account"]
		}),
		remoteSort:false,
		groupField:"group",
		sortInfo:{field:"name",direction:"ASC"},
		baseParams:{"wno":"<?php echo $this->wno; ?>","action":"worker","get":"worker","mode":"list","type":"all","keyword":""}
	});

	var WorkspaceListCm = new Ext.grid.ColumnModel([
		new Ext.grid.RowNumberer(),
		{
			dataIndex:"idx",
			hidden:true,
			hideable:false
		},{
			header:"그룹",
			dataIndex:"group",
			sortable:false,
			width:60
		},{
			header:"이름",
			dataIndex:"name",
			sortable:true,
			width:60
		},{
			header:"직위",
			dataIndex:"grade",
			width:80,
			renderer:function(value) {
				var temp = value.split("||");
				var sHTML = temp[0];

				if (temp.length > 1 && temp[1]) {
					sHTML+= " ("+temp[1]+")";
				}

				return sHTML;
			}
		},{
			header:"직원번호",
			dataIndex:"workernum",
			sortable:true,
			width:90
		},{
			header:"주민등록번호",
			dataIndex:"jumin",
			sortable:true,
			width:110
		},{
			header:"전화번호",
			dataIndex:"phone",
			sortable:true,
			width:90
		},{
			header:"급여형태",
			dataIndex:"pay_type",
			sortable:true,
			width:70
		},{
			header:"급여",
			dataIndex:"payment",
			sortable:true,
			width:70,
			renderer:GridNumberFormat
		},{
			header:"입사일",
			dataIndex:"enter_date",
			sortable:true,
			width:90
		},{
			header:"근무시작일",
			dataIndex:"workstart_date",
			sortable:true,
			width:90
		},{
			header:"근무종료일",
			dataIndex:"workend_date",
			sortable:true,
			width:90
		},{
			header:"퇴사일",
			dataIndex:"retire_date",
			sortable:true,
			width:90
		},{
			header:"계좌번호",
			dataIndex:"account",
			sortable:true,
			width:250,
			renderer:GridAccount
		},
		new Ext.grid.CheckboxSelectionModel()
	]);

	function WorkerCardFunction(idx) {
		new Ext.Window({
			id:"WorkerCardWindow",
			title:"근로자카드인쇄",
			modal:true,
			resizable:false,
			html:'<iframe id="WorkerCardFrame" src="<?php echo $this->moduleDir; ?>/worker.card.php?idx='+idx+'" style="width:447px; height:318px;" frameborder="0"></iframe>',
			buttons:[
				new Ext.Button({
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_printer.png",
					text:"인쇄하기",
					handler:function() {
						document.getElementById("WorkerCardFrame").contentWindow.window.focus();

						if (Ext.isIE8) {
							document.getElementById("WorkerCardFrame").contentWindow.document.body.style.zoom = "165%";
						}
						document.getElementById("WorkerCardFrame").contentWindow.window.print();
						document.getElementById("WorkerCardFrame").contentWindow.document.body.style.zoom = "100%";
					}
				})
			]
		}).show();
	}

	function WorkerMenuFunction(grid,idx,e) {
		GridContextmenuSelect(grid,idx);
		var data = grid.getStore().getAt(idx);

		var menu = new Ext.menu.Menu();
		menu.add('<b class="menu-title">'+data.get("name")+'</b>');
		menu.add({
			text:"근로자정보수정",
			icon:"<?php echo $this->moduleDir; ?>/images/common/icon_user_edit.png",
			handler:function(item) {
				WorkerFormFunction(data.get("idx"));
			}
		});
		menu.add({
			text:"근로자카드인쇄",
			icon:"<?php echo $this->moduleDir; ?>/images/common/icon_vcard.png",
			handler:function(item) {
				WorkerCardFunction(data.get("idx"));
			}
		});
		menu.add('-');
		menu.add({
			text:"근로자삭제",
			icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
			handler:function(item) {
				Ext.Msg.show({title:"안내",msg:"정말 삭제하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
					if (button == "ok") {
						Ext.Msg.wait("처리중입니다.","Please Wait...");
						Ext.Ajax.request({
							url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php",
							success:function() {
								Ext.Msg.show({title:"안내",msg:"성공적으로 삭제하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
								grid.getStore().reload();
							},
							failure:function() {
								Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 처리하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
							},
							headers:{},
							params:{"action":"worker","do":"worker","mode":"delete","idx":data.get("idx"),"wno":"<?php echo $this->wno; ?>"}
						});
					}
				}});
			}
		});
		e.stopEvent();
		menu.showAt(e.getXY());
	}

	function WorkerFormFunction(idx) {
		new Ext.Window({
			id:"WorkerWindow",
			title:(idx ? "근로자정보수정" : "신규근로자등록"),
			width:600,
			height:400,
			minWidth:600,
			minHeight:400,
			modal:true,
			maximizable:true,
			layout:"fit",
			style:"background:#FFFFFF;",
			items:[
				new Ext.form.FormPanel({
					id:"WorkerForm",
					labelAlign:"right",
					labelWidth:85,
					border:false,
					autoWidth:true,
					autoScroll:true,
					errorReader:new Ext.form.XmlErrorReader(),
					reader:new Ext.data.XmlReader(
						{record:"form",success:"@success",errormsg:"@errormsg"},
						["name","jumin","grade","grade_handwrite","enter_date","retire_date","workstart_date","zipcode","address1","address2","pay_type","work_type","payment","account_name","account_bank","account_number","telephone","cellphone","photo"]
					),
					items:[
						new Ext.form.FieldSet({
							title:"기본정보",
							autoWidth:true,
							autoHeight:true,
							defaults:{msgTarget:"side"},
							style:"margin:10px;",
							layout:"table",
							layoutConfig:{columns:2},
							items:[{
									colspan:2,
									border:false,
									layout:"form",
									items:[
										new Ext.form.TextField({
											msgTarget:"side",
											fieldLabel:"이름",
											name:"name",
											width:100,
											allowBlank:false,
											listeners:{blur:{fn:function(form){
												Ext.getCmp("WorkerForm").getForm().findField("account_name").setValue(form.getValue());
											}}}
										})
									]
								},{
									width:245,
									border:false,
									layout:"form",
									items:[
										new Ext.form.TextField({
											fieldLabel:"주민등록번호",
											name:"jumin",
											width:150,
											allowBlank:false,
											validator:CheckJumin,
											listeners:{blur:{fn:BlurJumin}}
										})
									]
								},{
									width:280,
									border:false,
									items:[
										new Ext.Button({
											text:"근로자검색",
											style:"margin-bottom:4px;",
											handler:function() {
												if (!insertForm.getForm().findField("jumin").getValue()) {
													Ext.Msg.show({title:"에러",msg:"검색할 직원의 주민등록번호를 검입력하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function(){insertForm.getForm().findField("jumin").focus();}});
												} else {
													beforeLoadData["name"] = insertForm.getForm().findField("name").getValue();
													beforeLoadData["jumin"] = insertForm.getForm().findField("jumin").getValue();
													insertForm.getForm().load({url:"/GetForm.do?ems=<?php echo $ems; ?>&mode=<?php echo $mode; ?>&action=worker&wno=<?php echo $wno; ?>&jumin="+insertForm.getForm().findField("jumin").getValue()});
												}
											}
										})
									]
								},{
									width:245,
									border:false,
									layout:"form",
									items:[
										new Ext.form.ComboBox({
											fieldLabel:"직위",
											hiddenName:"grade",
											store:new Ext.data.SimpleStore({
												fields:["grade"],
												data:[["임원"],["직원"],["직영"],["협력사"]]
											}),
											displayField:"grade",
											valueField:"grade",
											typeAhead:true,
											mode:"local",
											triggerAction:"all",
											width:150,
											editable:false,
											emptyText:"직위를 선택하세요.",
											listeners:{select:{fn:function(form) {
												if (form.getValue() == "협력사") {
													Ext.getCmp("WorkerForm").getForm().findField("grade_handwrite").enable();
													Ext.getCmp("WorkerForm").getForm().findField("grade_handwrite").focus();
												} else {
													Ext.getCmp("WorkerForm").getForm().findField("grade_handwrite").disable();
													Ext.getCmp("WorkerForm").getForm().findField("grade_handwrite").setValue("");
												}
											}}}
										})
									]
								},{
									width:280,
									border:false,
									layout:"form",
									items:[
										new Ext.form.TextField({
											hideLabel:true,
											name:"grade_handwrite",
											disabled:true,
											width:200,
											emptyText:"협력사 직위 직접 입력"
										})
									]
								},{
									colspan:2,
									width:500,
									border:false,
									layout:"form",
									items:[
										new Ext.form.DateField({
											msgTarget:"side",
											fieldLabel:"입사일",
											format:"Y-m-d",
											name:"enter_date",
											width:100
										}),
										new Ext.form.DateField({
											msgTarget:"side",
											fieldLabel:"퇴사일",
											format:"Y-m-d",
											name:"retire_date",
											width:100
										}),
										new Ext.form.DateField({
											msgTarget:"side",
											fieldLabel:"근무시작일",
											format:"Y-m-d",
											name:"workstart_date",
											width:100,
											value:new Date().format("Y-m-d")
										})
									]
								}
							]
						}),
						FormAddressFieldSet("WorkerForm"),
						new Ext.form.FieldSet({
							defaults:{msgTarget:"side"},
							title:"급여/계좌정보",
							autoWidth:true,
							autoHeight:true,
							style:"margin:10px",
							items:[
								new Ext.form.ComboBox({
									fieldLabel:"급여형태",
									hiddenName:"pay_type",
									store:new Ext.data.SimpleStore({
										fields:["pay_type","value"],
										data:[<?php $isFirst = true; foreach ($this->paytype as $type=>$name) { echo ($isFirst == true ? '' : ',').'["'.$name.'","'.$type.'"]'; $isFirst = false; } ?>]
									}),
									displayField:"pay_type",
									valueField:"value",
									typeAhead:true,
									mode:"local",
									triggerAction:"all",
									emptyText:"급여형태를 선택하세요.",
									width:190,
									editable:false
								}),
								new Ext.form.ComboBox({
									fieldLabel:"업무형태",
									hiddenName:"work_type",
									store:new Ext.data.SimpleStore({
										fields:["work_type","value"],
										data:[["현장근로자","WORKER"],["현장소장","MASTER"]]
									}),
									displayField:"work_type",
									valueField:"value",
									typeAhead:true,
									mode:"local",
									triggerAction:"all",
									emptyText:"업무형태를 선택하세요.",
									width:190,
									editable:false
								}),
								new Ext.form.TextField({
									fieldLabel:"단가",
									name:"payment",
									width:100,
									style:"text-align:right;",
									enableKeyEvents:true,
									listeners:{
										keydown:{fn:PressNumberOnly},
										blur:{fn:BlurNumberFormat},
										focus:{fn:FocusNumberOnly}
									}
								}),
								new Ext.form.TextField({
									fieldLabel:"예금주",
									name:"account_name",
									width:100
								}),
								new Ext.form.ComboBox({
									fieldLabel:"은행명",
									hiddenName:"account_bank",
									store:BankSimpleStore,
									displayField:"bank",
									valueField:"bank",
									typeAhead:true,
									mode:"local",
									triggerAction:"all",
									emptyText:"은행을 선택하세요.",
									width:190,
									editable:false
								}),
								new Ext.form.TextField({
									fieldLabel:"계좌번호",
									name:"account_number",
									width:190
								})
							]
						}),
						new Ext.form.FieldSet({
							defaults:{msgTarget:"side"},
							title:"연락처정보",
							autoWidth:true,
							autoHeight:true,
							style:"margin:10px",
							items:[
								new Ext.form.TextField({
									fieldLabel:"전화번호",
									name:"telephone",
									width:200,
									emptyText:"'-' 는 제외하고 입력하세요.",
									listeners:{
										blur:{fn:BlurTelephoneFormat},
										focus:{fn:FocusNumberOnly}
									}
								}),
								new Ext.form.TextField({
									fieldLabel:"핸드폰번호",
									name:"cellphone",
									width:200,
									emptyText:"'-' 는 제외하고 입력하세요.",
									listeners:{
										blur:{fn:BlurTelephoneFormat},
										focus:{fn:FocusNumberOnly}
									}
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"사진찍기",
							autoWidth:true,
							autoHeight:true,
							style:"margin:10px",
							items:[
								new Ext.form.Hidden({
									name:"photo"
								}),
								new Ext.Panel({
									border:false,
									width:340,
									html:'<img name="PreviewPhoto" src="<?php echo $this->moduleDir; ?>/images/common/nopic320.gif" />',
									buttonAlign:"left",
									buttons:[
										new Ext.Button({
											text:"사진찍기",
											width:305,
											handler:function() {
												new Ext.Window({
													title:"사진찍기",
													id:"TakePhotoWindow",
													modal:true,
													width:320,
													resizable:false,
													html:'<div style="width:320px; height:240px;">'+(Ext.isIE == true ? '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=10,0,0,0" width="320" height="240" id="TakePhoto" align="middle"><param name="allowScriptAccess" value="always" /><param name="wmode" value="transparent" /><param name="movie" value="<?php echo $this->moduleDir; ?>/flash/TakePhoto.swf" /><param name="quality" value="high" /><embed src="<?php echo $this->moduleDir; ?>/flash/TakePhoto.swf" quality="high" style="width:320px; height:240px;" align="middle" allowScriptAccess="always" wmode="transparent" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed></object>' : '<embed id="TakePhoto" src="<?php echo $this->moduleDir; ?>/flash/TakePhoto.swf" quality="high" style="width:320px; height:240px;" align="middle" allowScriptAccess="always" wmode="transparent" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed>')+'</div>',
													buttons:[
														new Ext.Button({
															text:"사진찍기",
															icon:"<?php echo $this->moduleDir; ?>/images/common/icon_camera.png",
															handler:function() {
																var object = document.getElementById("TakePhoto");
																object.capture("<?php echo $this->moduleDir; ?>/exec/TakePhoto.do.php?action=new","TakePhotoNew");

																Ext.Msg.show({title:"확인",msg:"현재 사진으로 등록하시겠습니까?<br />취소를 클릭하시면 다시 찍으실 수 있습니다..",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(btn){
																	if (btn == "ok") {
																		object.save();
																	} else {
																		object.cancel();
																	}
																}});
															}
														}),
														new Ext.Button({
															text:"도움말",
															icon:"<?php echo $this->moduleDir; ?>/images/common/icon_help.png",
															handler:function() {

															}
														})
													]
												}).show();
											}
										})
									]
								})
							]
						})
					],
					listeners:{
						actioncomplete:{fn:function(form,action) {
							if (action.type == "submit") {
								Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,fn:function(){ Ext.getCmp("ListTab").getActiveTab().getStore().reload(); Ext.getCmp("WorkerWindow").close(); }});
							}

							if (action.type == "load") {
								if (Ext.getCmp("WorkerForm").getForm().findField("photo").getValue()) {
									document.images["PreviewPhoto"].src = Ext.getCmp("WorkerForm").getForm().findField("photo").getValue()+"?rnd="+Math.random();
									Ext.getCmp("WorkerForm").getForm().findField("photo").setValue();
								}
							}
						}}
					}
				})
			],
			buttons:[
				new Ext.Button({
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_tick.png",
					text:"확인",
					handler:function() {
						if (idx) {
							Ext.getCmp("WorkerForm").getForm().submit({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?action=worker&do=worker&mode=modify&wno=<?php echo $this->wno; ?>&idx="+idx,waitMsg:"근로자를 수정중입니다.",submitEmptyText:false});
						} else {
							Ext.getCmp("WorkerForm").getForm().submit({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.do.php?action=worker&do=worker&mode=add&wno=<?php echo $this->wno; ?>",waitMsg:"근로자를 추가중입니다.",submitEmptyText:false});
						}
					}
				}),
				new Ext.Button({
					icon:"<?php echo $this->moduleDir; ?>/images/common/icon_cross.png",
					text:"취소",
					handler:function() {
						Ext.getCmp("WorkerWindow").close();
					}
				})
			],
			listeners:{show:{fn:function() {
				if (idx) {
					Ext.getCmp("WorkerForm").load({url:"<?php echo $this->moduleDir; ?>/exec/Workspace.get.php?action=worker&get=worker&mode=data&idx="+idx,waitMsg:"정보를 로딩중입니다."});
				}
			}}}
		}).show();
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"현장근로자관리",
		layout:"fit",
		tbar:[
			new Ext.form.TextField({
				id:"KeywordInput",
				width:150,
				emptyText:"검색어를 입력하세요."
			}),
			' ',
			new Ext.Button({
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_magnifier.png",
				text:"검색",
				handler:function() {
					Ext.getCmp("ListTab").getActiveTab().getStore().baseParams.keyword = Ext.getCmp("KeywordInput").getValue();
					Ext.getCmp("ListTab").getActiveTab().getStore().load();
				}
			}),
			'-',
			new Ext.Button({
				icon:"<?php echo $this->moduleDir; ?>/images/common/icon_user_add.png",
				text:"신규근로자등록",
				handler:function() {
					WorkerFormFunction();
				}
			})
		],
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				items:[
					new Ext.grid.GridPanel({
						id:"ListTab1",
						title:"전체근로자",
						border:false,
						autoScroll:true,
						cm:WorkspaceListCm,
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:WorkerListStore1,
						trackMouseOver:true,
						loadMask:{msg:"데이터를 로딩중입니다."},
						view:new Ext.grid.GroupingView({
							enableGroupingMenu:false,
							hideGroupedColumn:true,
							groupTextTpl:'{text} ({[values.rs.length]}명)'
						}),
						listeners:{
							rowcontextmenu:{fn:WorkerMenuFunction}
						}
					})
				],
				listeners:{tabchange:{fn:function(tabs,tab) {
					tab.getStore().load();
					Ext.getCmp("KeywordInput").setValue(tab.getStore().baseParams.keyword);
					if (tab.getId() != "ListTab3") {
						Ext.getCmp(tab.getId()).getColumnModel().setHidden(9,true);
						Ext.getCmp(tab.getId()).getColumnModel().setHidden(10,true);
					} else {
						Ext.getCmp(tab.getId()).getColumnModel().setHidden(9,false);
						Ext.getCmp(tab.getId()).getColumnModel().setHidden(10,false);
					}
				}}}
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>