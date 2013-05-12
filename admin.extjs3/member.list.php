<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	var MemberAll = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"user_id","group","type","name","nickname","jumin","email","phone","reg_date","last_login","last_week","exp","point"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"DESC"},
		baseParams:{action:"member",get:"list",keyword:"","group":""}
	});
	
	MemberAll.load({params:{start:0,limit:50}});
	<?php
	$group = $mDB->DBfetchs($_ENV['table']['group'],'*');
	for ($i=0, $loop=sizeof($group);$i<$loop;$i++) {
	?>
	var Member<?php echo $group[$i]['group']; ?> = new Ext.data.Store({
		proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/exec/Admin.get.php"}),
		reader:new Ext.data.JsonReader({
			root:"lists",
			totalProperty:"totalCount",
			fields:[{name:"idx",type:"int"},"user_id","group","type","name","nickname","jumin","email","phone","reg_date","last_login","last_week","exp","point"]
		}),
		remoteSort:true,
		sortInfo:{field:"idx",direction:"DESC"},
		baseParams:{action:"member",get:"list",keyword:"","group":"<?php echo $group[$i]['group']; ?>"}
	});
	
	Member<?php echo $group[$i]['group']; ?>.load({params:{start:0,limit:50}});
	<?php } ?>
	
	function GetMemberInfo(idx,group) {
		var signinDefault = {};
		var signinExtension = {};
		var signinField = {};
		<?php
		$getValues = array();
		$signin = $mDB->DBfetchs($_ENV['table']['signin'],array('name','group','type','title','allowblank'),'','sort,asc');
		for ($i=0, $loop=sizeof($signin);$i<$loop;$i++) {
		?>
		if (signinDefault["<?php echo $signin[$i]['group']; ?>"] === undefined) signinDefault["<?php echo $signin[$i]['group']; ?>"] = new Array();
		if (signinExtension["<?php echo $signin[$i]['group']; ?>"] === undefined) signinExtension["<?php echo $signin[$i]['group']; ?>"] = new Array();
		if (signinField["<?php echo $signin[$i]['group']; ?>"] === undefined) signinField["<?php echo $signin[$i]['group']; ?>"] = new Array();
			<?php if ($signin[$i]['type'] == 'name') { ?>
			signinDefault["<?php echo $signin[$i]['group']; ?>"].push(
				new Ext.form.TextField({
					name:"<?php echo $signin[$i]['name']; ?>",
					fieldLabel:"<?php echo $signin[$i]['title']; ?>",
					width:120,
					allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
				})
			);
			signinField["<?php echo $signin[$i]['group']; ?>"].push("<?php echo $signin[$i]['name']; ?>");
			<?php } elseif ($signin[$i]['type'] == 'nickname') { ?>
			signinDefault["<?php echo $signin[$i]['group']; ?>"].push(
				new Ext.form.Hidden({
					name:"nickcon"
				}),
				new Ext.form.CompositeField({
					labelWidth:85,
					labelAlign:"right",
					fieldLabel:"<?php echo $signin[$i]['title']; ?>",
					width:400,
					items:[
						new Ext.form.TextField({
							name:"<?php echo $signin[$i]['name']; ?>",
							fieldLabel:"<?php echo $signin[$i]['title']; ?>",
							width:120,
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						}),
						new Ext.form.DisplayField({
							id:"MemberNickcon",
							width:120,
							html:""
						})
					]
				}),
				new Ext.form.CompositeField({
					labelWidth:85,
					labelAlign:"right",
					fieldLabel:"닉이미지",
					width:400,
					items:[
						new Ext.ux.form.FileUploadField({
							fieldLabel:"닉이미지",
							name:"nickcon",
							buttonText:"",
							buttonCfg:{iconCls:"upload-file"},
							allowBlank:true,
							width:300,
							listeners:{
								focus:{fn:function(form) {
									if (form.getValue()) {
										Ext.Msg.show({title:"초기화선택",msg:"첨부파일을 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
											if (button == "ok") {
												form.reset();
											}
										}});
									}
								}},
								invalid:{fn:function(form,text) {
									if (form.getValue()) {
										form.reset();
										form.markInvalid(text);
									}
								}}
							}
						}),
						new Ext.form.Checkbox({
							name:"delete_nickcon",
							boxLabel:"닉이미지삭제"
						})
					]
				})
			);
			signinField["<?php echo $signin[$i]['group']; ?>"].push("<?php echo $signin[$i]['name']; ?>","nickcon");
			<?php } elseif ($signin[$i]['type'] == 'email' || $signin[$i]['type'] == 'homepage') { ?>
			signinDefault["<?php echo $signin[$i]['group']; ?>"].push(
				new Ext.form.TextField({
					name:"<?php echo $signin[$i]['name']; ?>",
					fieldLabel:"<?php echo $signin[$i]['title']; ?>",
					width:400,
					allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
				})
			);
			signinField["<?php echo $signin[$i]['group']; ?>"].push("<?php echo $signin[$i]['name']; ?>");
			<?php } elseif ($signin[$i]['type'] == 'password') { ?>
			signinDefault["<?php echo $signin[$i]['group']; ?>"].push(
				new Ext.form.CompositeField({
					labelWidth:85,
					labelAlign:"right",
					fieldLabel:"<?php echo $signin[$i]['title']; ?>",
					width:400,
					items:[
						new Ext.form.TextField({
							name:"password1",
							width:120,
							inputType:"password",
							disabled:true,
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						}),
						new Ext.form.TextField({
							name:"password2",
							width:120,
							inputType:"password",
							disabled:true,
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						}),
						new Ext.form.Checkbox({
							name:"password_modify",
							boxLabel:"패스워드변경",
							listeners:{check:{fn:function(form) {
								if (form.checked == true) {
									Ext.getCmp("MemberForm").getForm().findField("password1").enable();
									Ext.getCmp("MemberForm").getForm().findField("password2").enable();
								} else {
									Ext.getCmp("MemberForm").getForm().findField("password1").disable();
									Ext.getCmp("MemberForm").getForm().findField("password2").disable();
								}
							}}}
						})
					]
				})
			);
			signinField["<?php echo $signin[$i]['group']; ?>"].push("<?php echo $signin[$i]['name']; ?>");
			<?php } elseif ($signin[$i]['type'] == 'birthday') { ?>
			signinDefault["<?php echo $signin[$i]['group']; ?>"].push(
				new Ext.form.CompositeField({
					labelWidth:85,
					labelAlign:"right",
					fieldLabel:"<?php echo $signin[$i]['title']; ?>",
					width:300,
					items:[
						new Ext.form.ComboBox({
							hiddenName:"birthday1",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.SimpleStore({
								fields:["text","value"],
								data:[
									["연도",""]
									<?php for ($j=1950;$j<=date('Y');$j++) { ?>,["<?php echo $j; ?>년","<?php echo $j; ?>"]<?php } ?>
								]
							}),
							width:80,
							editable:false,
							mode:"local",
							displayField:"text",
							valueField:"value",
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						}),
						new Ext.form.ComboBox({
							hiddenName:"birthday2",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.SimpleStore({
								fields:["text","value"],
								data:[
									["월",""]
									<?php for ($j=1;$j<=12;$j++) { ?>,["<?php echo $j; ?>월","<?php echo $j; ?>"]<?php } ?>
								]
							}),
							width:60,
							editable:false,
							mode:"local",
							displayField:"text",
							valueField:"value",
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						}),
						new Ext.form.ComboBox({
							hiddenName:"birthday3",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.SimpleStore({
								fields:["text","value"],
								data:[
									["일",""]
									<?php for ($j=1;$j<=31;$j++) { ?>,["<?php echo $j; ?>일","<?php echo $j; ?>"]<?php } ?>
								]
							}),
							width:60,
							editable:false,
							mode:"local",
							displayField:"text",
							valueField:"value",
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						})
					]
				})
			);
			signinField["<?php echo $signin[$i]['group']; ?>"].push("birthday1","birthday2","birthday3");
			<?php } elseif ($signin[$i]['type'] == 'cellphone') { ?>
			signinDefault["<?php echo $signin[$i]['group']; ?>"].push(
				new Ext.form.CompositeField({
					labelWidth:85,
					labelAlign:"right",
					fieldLabel:"<?php echo $signin[$i]['title']; ?>",
					width:300,
					items:[
						new Ext.form.ComboBox({
							hiddenName:"provider",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.SimpleStore({
								fields:["text","value"],
								data:[["통신사",""],["SKT","SKT"],["KT","KT"],["LGT","LGT"]]
							}),
							width:60,
							editable:false,
							mode:"local",
							displayField:"text",
							valueField:"value",
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						}),
						new Ext.form.ComboBox({
							hiddenName:"cellphone1",
							typeAhead:true,
							triggerAction:"all",
							lazyRender:true,
							store:new Ext.data.SimpleStore({
								fields:["value"],
								data:[["010"],["011"],["016"],["017"],["018"],["019"]]
							}),
							width:50,
							editable:false,
							mode:"local",
							displayField:"value",
							valueField:"value",
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						}),
						new Ext.form.DisplayField({
							html:"-"
						}),
						new Ext.form.TextField({
							name:"cellphone2",
							width:50,
							minLength:3,
							maxLength:4,
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						}),
						new Ext.form.DisplayField({
							html:"-"
						}),
						new Ext.form.TextField({
							name:"cellphone3",
							width:50,
							minLength:4,
							maxLength:4,
							allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
						})
					]
				})
			);
			signinField["<?php echo $signin[$i]['group']; ?>"].push("provider","cellphone1","cellphone2","cellphone3");
			<?php } elseif ($signin[$i]['type'] == 'gender') { ?>
			signinDefault["<?php echo $signin[$i]['group']; ?>"].push(
				new Ext.form.ComboBox({
					hiddenName:"<?php echo $signin[$i]['name']; ?>",
					fieldLabel:"<?php echo $signin[$i]['name']; ?>",
					typeAhead:true,
					triggerAction:"all",
					lazyRender:true,
					store:new Ext.data.SimpleStore({
						fields:["text","value"],
						data:[["남자","MALE"],["여자","FEMALE"]]
					}),
					width:60,
					editable:false,
					mode:"local",
					displayField:"text",
					valueField:"value",
					allowBlank:<?php echo $signin[$i]['allowblank'] == 'TRUE' ? 'true' : 'false'; ?>
				})
			);
			signinField["<?php echo $signin[$i]['group']; ?>"].push("<?php echo $signin[$i]['name']; ?>");
			<?php } ?>
		<?php } ?>
		
		signinDefault[group].push(
			new Ext.form.CompositeField({
				labelWidth:85,
				labelAlign:"right",
				fieldLabel:"<?php echo $signin[$i]['title']; ?>",
				width:400,
				items:[
					new Ext.ux.form.FileUploadField({
						fieldLabel:"회원이미지",
						name:"photo",
						buttonText:"",
						buttonCfg:{iconCls:"upload-file"},
						allowBlank:true,
						width:300,
						listeners:{
							focus:{fn:function(form) {
								if (form.getValue()) {
									Ext.Msg.show({title:"초기화선택",msg:"첨부파일을 초기화 하시겠습니까?",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.QUESTION,fn:function(button) {
										if (button == "ok") {
											form.reset();
										}
									}});
								}
							}},
							invalid:{fn:function(form,text) {
								if (form.getValue()) {
									form.reset();
									form.markInvalid(text);
								}
							}}
						}
					}),
					new Ext.form.Checkbox({
						name:"delete_photo",
						boxLabel:"이미지삭제"
					})
				]
			})
		);
		
		signinField[group].push("user_id","photo","reg_date","last_login");

		new Ext.Window({
			id:"MemberWindow",
			title:"회원정보보기",
			width:600,
			height:500,
			minWidth:600,
			minHeight:400,
			modal:true,
			maximizable:true,
			layout:"fit",
			style:"background:#FFFFFF;",
			items:[
				new Ext.form.FormPanel({
					id:"MemberForm",
					labelAlign:"right",
					labelWidth:85,
					border:false,
					autoWidth:true,
					autoScroll:true,
					fileUpload:true,
					errorReader:new Ext.form.XmlErrorReader(),
					reader:new Ext.data.XmlReader(
						{record:"form",success:"@success",errormsg:"@errormsg"},
						signinField[group]
					),
					items:[
						new Ext.form.FieldSet({
							title:"회원정보",
							autoWidth:true,
							autoHeight:true,
							defaults:{msgTarget:"side"},
							style:"margin:10px;",
							items:[
								new Ext.form.Hidden({
									name:"user_id"
								}),
								new Ext.form.Hidden({
									name:"reg_date"
								}),
								new Ext.form.Hidden({
									name:"last_login"
								}),
								new Ext.form.Hidden({
									name:"photo"
								}),
								new Ext.Panel({
									layout:"border",
									height:65,
									border:false,
									items:[
										new Ext.Panel({
											region:"west",
											width:70,
											border:false,
											html:'<img id="MemberPhoto" src="<?php echo $_ENV['dir']; ?>/images/common/nomempic60.gif" style="border:2px solid #E5E5E5; width:60px; height:60px;" />'
										}),
										new Ext.Panel({
											region:"center",
											border:false,
											html:'<span id="MemberUserID" style="font-family:tahoma; font-size:14px; font-weight:bold;">UnKnown</span><br /><br />Joined : <span id="MemberRegDate" style="font-family:tahoma; font-size:11px; font-weight:bold;">1970.01.01 12:00:00</span><br />Last Login : <span id="MemberLastLogin" style="font-family:tahoma; font-size:11px; font-weight:bold;">1970.01.01 12:00:00</span>'
										})
									]
								})
							]
						}),
						new Ext.form.FieldSet({
							title:"기본정보",
							autoWidth:true,
							autoHeight:true,
							defaults:{msgTarget:"side"},
							style:"margin:10px;",
							items:[
								signinDefault[group]
							]
						})
					],
					listeners:{
						render:{fn:function() {
							Ext.getCmp("MemberForm").getForm().load({url:"<?php echo $_ENV['dir']; ?>/exec/Admin.get.php?action=member&get=info&idx="+idx,waitMsg:"정보를 로딩중입니다."});
						}},
						actioncomplete:{fn:function(form,action) {
							if (action.type == "load") {
								document.getElementById("MemberUserID").innerHTML = form.findField("user_id").getValue();
								document.getElementById("MemberRegDate").innerHTML = form.findField("reg_date").getValue();
								document.getElementById("MemberLastLogin").innerHTML = form.findField("last_login").getValue();
								document.getElementById("MemberPhoto").src = form.findField("photo").getValue();

								if (form.findField("nickcon").getValue()) {
									Ext.getCmp("MemberNickcon").getEl().dom.innerHTML = '<img src="'+form.findField("nickcon").getValue()+'" />';
									form.findField("delete_nickcon").enable();
								} else {
									form.findField("delete_nickcon").disable();
								}
							}
							
							if (action.type == "submit") {
								Ext.Msg.show({title:"안내",msg:"성공적으로 수정하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,fn:function(){ Ext.getCmp("ListPanel").getStore().reload(); Ext.getCmp("MemberWindow").close(); }});
							}
						}}
					}
				})
			],
			buttons:[
				new Ext.Button({
					text:"수정하기",
					icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_tick.png",
					handler:function() {
						Ext.getCmp("MemberForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/exec/Admin.do.php?action=member&do=modify&idx="+idx,waitMsg:"데이터를 전송중입니다."});
					}
				}),
				new Ext.Button({
					text:"닫기",
					icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_cross.png",
					handler:function() {
						Ext.getCmp("MemberWindow").close();
					}
				})
			]
		}).show();
	}
	
	function MemberRowMenu(grid,idx,e) {
		GridContextmenuSelect(grid,idx);
		var menu = new Ext.menu.Menu();
		var data = grid.getStore().getAt(idx);

		menu.add('<b class="menu-title">'+data.get("name")+'('+data.get("user_id")+')</b>');
		menu.add({
			text:"회원정보수정",
			icon:"<?php echo $_ENV['dir']; ?>/module/member/images/admin/icon_user_edit.png",
			handler:function(item) {
			}
		});

		e.stopEvent();
		menu.showAt(e.getXY());
	}

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"회원목록",
		layout:"fit",
		items:[
			new Ext.TabPanel({
				id:"ListTab",
				tabPosition:"bottom",
				activeTab:0,
				border:false,
				tbar:[
					new Ext.form.ComboBox({
						id:"Group",
						typeAhead:true,
						triggerAction:"all",
						lazyRender:true,
						store:new Ext.data.Store({
							proxy:new Ext.data.ScriptTagProxy({url:"<?php echo $_ENV['dir']; ?>/exec/Admin.get.php"}),
							reader:new Ext.data.JsonReader({
								root:"lists",
								totalProperty:"totalCount",
								fields:["group","title"]
							}),
							remoteSort:false,
							sortInfo:{field:"group",direction:"ASC"},
							baseParams:{"action":"member","get":"group","is_all":"true"}
						}),
						width:120,
						editable:false,
						mode:"local",
						displayField:"title",
						valueField:"group",
						emptyText:"회원그룹",
						listeners:{
							render:{fn:function() {
								Ext.getCmp("Group").getStore().load();
							}},
							select:{fn:function(form,selected) {
								MemberAll.baseParams.group = form.getValue();
								MemberAll.load({params:{start:0,limit:30}});
							}}
						}
					}),
					' ',
					new Ext.form.TextField({
						id:"keyword",
						emptyText:"아이디, 실명, 닉네임",
						width:150
					}),
					' ',
					new Ext.Button({
						text:"검색",
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_magnifier.png",
						handler:function() {
							MemberAll.baseParams.keyword = Ext.getCmp("keyword").getValue();
							MemberAll.reload();
						}
					}),
					'|',
					<?php $mModule = new Module('sms'); if ($mModule->IsSetup() == true) { ?>
					new Ext.Button({
						text:"SMS보내기",
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_phone.png",
						handler:function() {
							var checked = Ext.getCmp("ListPanel").selModel.getSelections();

							if (checked.length == 0) {
								Ext.Msg.show({title:"에러",msg:"SMS를 보낼 회원을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
								return false;
							}

							function CheckSMSLength() {
								var isOver = false;
								var str = Ext.getCmp("SMSForm").getForm().findField("content").getValue();
								var length = 0;
								for (var i=0, loop=str.length;i<loop;i++) {
									if (escape(str.charAt(i)).length > 4) {
										length+= 2;
									} else {
										length++;
									}

									var tempStr = str;

									if (length > 80) {
										isOver = true;
										Ext.Msg.show({title:"에러",msg:"허용길이 이상을 입력하셨습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function() {
											Ext.getCmp("SMSForm").getForm().findField("content").setValue(tempStr.substr(0,i-1));
											SMSInterval = setTimeout(CheckSMSLength,100);
										}});
									}
								}

								Ext.getCmp("SMSForm").getForm().findField("length").setValue(length+" / 80");
								if (isOver == false) SMSInterval = setTimeout(CheckSMSLength,100);
							}

							var SMSInterval = null;

							var idxs = new Array();
							for (var i=0, loop=checked.length;i<loop;i++) {
								idxs.push(checked[i].get("idx"));
							}
							var idx = idxs.join(",");

							new Ext.Window({
								id:"SMSWindow",
								title:"SMS보내기",
								modal:true,
								items:[
									new Ext.form.FormPanel({
										id:"SMSForm",
										labelWidth:85,
										border:false,
										autoWidth:true,
										autoScroll:true,
										defaults:{hideLabel:true},
										errorReader:new Ext.form.XmlErrorReader(),
										style:"padding:10px; background:#FFFFFF;",
										items:[
											new Ext.form.TextArea({
												name:"content",
												width:110,
												height:100,
												allowBlank:false,
												listeners:{
													focus:{fn:function() {
														CheckSMSLength();
													}},
													blur:{fn:function() {
														clearTimeout(SMSInterval);
													}}
												}
											}),
											new Ext.form.TextField({
												name:"length",
												width:110,
												style:"text-align:center;",
												value:"0 / 80"
											}),
											new Ext.form.Hidden({
												name:"receiver",
												width:110,
												value:idx
											})
										],
										listeners:{actioncomplete:{fn:function(form,action) {
											if (action.type == "submit") {
												Ext.Msg.show({title:"안내",msg:"성공적으로 전송하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
												Ext.getCmp("SMSWindow").close();
											}
										}}}
									})
								],
								buttons:[
									new Ext.Button({
										text:"전송하기",
										icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_phone.png",
										handler:function() {
											Ext.getCmp("SMSForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/sms/exec/Admin.do.php?action=membersend",waitMsg:"SMS전송중입니다."});
										}
									})
								]
							}).show();
						}
					}),
					<?php } ?>
					new Ext.Button({
						text:"회원관리",
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_group_gear.png",
						menu:new Ext.menu.Menu({
							items:[
								new Ext.form.ComboBox({
									typeAhead:true,
									triggerAction:"all",
									lazyRender:true,
									width:100,
									store:new Ext.data.SimpleStore({
										fields:["value","display"],
										data:[["MEMBER","일반회원"],["MODERATOR","관리자"],["ADMINISTRATOR","최고관리자"]]
									}),
									editable:false,
									mode:"local",
									displayField:"display",
									valueField:"value",
									getListParent:function() {
										return this.el.up('.x-menu');
									},
									emptyText:"권한설정",
									iconCls:"no-icon",
									listeners:{select:{fn:function(form,record,idx) {
										var checked = Ext.getCmp("ListPanel").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"권한을 변경할 회원을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										}

										Ext.Msg.show({title:"안내",msg:"선택한 회원의 권한을 "+record.data.display+"(으)로 변경하시겠습니까?.",buttons:Ext.Msg.OKCANCEL,icon:Ext.MessageBox.WARNING,fn:function(button) {
											if (button == "ok") {
												var idxs = new Array();

												for (var i=0, loop=checked.length;i<loop;i++) {
													idxs.push(checked[i].get("idx"));
												}

												var idx = idxs.join(",");

												Ext.Ajax.request({
													url:"<?php echo $_ENV['dir']; ?>/exec/Admin.do.php?do=type",
													success: function() {
														Ext.Msg.show({title:"안내",msg:"성공적으로 처리되었습니다..",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
														Ext.getCmp("ListPanel").getStore().reload();
													},
													failure: function() {
														Ext.Msg.show({title:"안내",msg:"서버에 이상이 있어 삭제하지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
													},
													headers:{},
													params: {action:"member",type:form.getValue(),idx:idx}
												});
											}
										}});
									}}}
								}),
								'-',
								new Ext.menu.Item({
									text:"회원삭제"
								}),
								'-',
								new Ext.menu.Item({
									text:"메세지보내기"
								}),
								new Ext.menu.Item({
									text:"메일보내기"
								})
								<?php $mModule = new Module('sms'); if ($mModule->IsSetup() == true) { ?>,
								new Ext.menu.Item({
									text:"SMS보내기",
									icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_phone.png",
									handler:function() {
										var checked = Ext.getCmp("ListPanel").selModel.getSelections();

										if (checked.length == 0) {
											Ext.Msg.show({title:"에러",msg:"SMS를 보낼 회원을 선택하여 주십시오.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING});
											return false;
										}

										function CheckSMSLength() {
											var isOver = false;
											var str = Ext.getCmp("SMSForm").getForm().findField("content").getValue();
											var length = 0;
											for (var i=0, loop=str.length;i<loop;i++) {
												if (escape(str.charAt(i)).length > 4) {
													length+= 2;
												} else {
													length++;
												}

												var tempStr = str;

												if (length > 80) {
													isOver = true;
													Ext.Msg.show({title:"에러",msg:"허용길이 이상을 입력하셨습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.WARNING,fn:function() {
														Ext.getCmp("SMSForm").getForm().findField("content").setValue(tempStr.substr(0,i-1));
														SMSInterval = setTimeout(CheckSMSLength,100);
													}});
												}
											}

											Ext.getCmp("SMSForm").getForm().findField("length").setValue(length+" / 80");
											if (isOver == false) SMSInterval = setTimeout(CheckSMSLength,100);
										}

										var SMSInterval = null;

										var idxs = new Array();
										for (var i=0, loop=checked.length;i<loop;i++) {
											idxs.push(checked[i].get("idx"));
										}
										var idx = idxs.join(",");

										new Ext.Window({
											id:"SMSWindow",
											title:"SMS보내기",
											modal:true,
											items:[
												new Ext.form.FormPanel({
													id:"SMSForm",
													labelWidth:85,
													border:false,
													autoWidth:true,
													autoScroll:true,
													defaults:{hideLabel:true},
													errorReader:new Ext.form.XmlErrorReader(),
													style:"padding:10px; background:#FFFFFF;",
													items:[
														new Ext.form.TextArea({
															name:"content",
															width:110,
															height:100,
															allowBlank:false,
															listeners:{
																focus:{fn:function() {
																	CheckSMSLength();
																}},
																blur:{fn:function() {
																	clearTimeout(SMSInterval);
																}}
															}
														}),
														new Ext.form.TextField({
															name:"length",
															width:110,
															style:"text-align:center;",
															value:"0 / 80"
														}),
														new Ext.form.Hidden({
															name:"receiver",
															width:110,
															value:idx
														})
													],
													listeners:{actioncomplete:{fn:function(form,action) {
														if (action.type == "submit") {
															Ext.Msg.show({title:"안내",msg:"성공적으로 전송하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO});
															Ext.getCmp("SMSWindow").close();
														}
													}}}
												})
											],
											buttons:[
												new Ext.Button({
													text:"전송하기",
													icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_phone.png",
													handler:function() {
														Ext.getCmp("SMSForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/module/sms/exec/Admin.do.php?action=membersend",waitMsg:"SMS전송중입니다."});
													}
												})
											]
										}).show();
									}
								})
								<?php } ?>
							]
						})
					})
				],
				items:[
					new Ext.grid.GridPanel({
						id:"ListAll",
						title:"전체그룹",
						border:false,
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								header:"아이디",
								dataIndex:"user_id",
								sortable:true,
								width:100,
								renderer:function(value,p,record) {
									return '<div style="font-family:verdana;">'+value+' <span style="font-family:tahoma; font-size:10px; color:#C6C6C6;">['+GetNumberFormat(record.data.idx)+']</span></div>';
								}
							},{
								header:"회원종류",
								dataIndex:"type",
								sortable:true,
								width:80,
								renderer:function(value) {
									if (value == "ADMINISTRATOR") {
										return "최고관리자";
									} else if (value == "MODERATOR") {
										return "관리자";
									} else {
										return "일반회원";
									}
								}
							},{
								header:"이름",
								dataIndex:"name",
								sortable:true,
								width:60
							},{
								header:"닉네임",
								dataIndex:"nickname",
								sortable:true,
								width:80
							},{
								header:"주민등록번호",
								dataIndex:"jumin",
								sortable:true,
								width:100,
								renderer:function(value) {
									return '<div style="font-family:tahoma;">'+value+'</div>';
								}
							},{
								header:"이메일",
								dataIndex:"email",
								sortable:true,
								width:180,
								renderer:function(value) {
									return '<div style="font-family:tahoma;">'+value+'</div>';
								}
							},{
								header:"연락처",
								dataIndex:"phone",
								sortable:false,
								width:120,
								renderer:function(value) {
									var data = value.split("||");
		
									if (data.length == 2) {
										return '<div style="font-family:tahoma;">'+data[0]+' <span style="color:#C6C6C6;">['+data[1]+']</span></div>';
									} else {
										return '<div style="font-family:tahoma;">'+value+'</div>';
									}
								}
							},{
								header:"포인트",
								dataIndex:"point",
								sortable:true,
								width:65,
								renderer:GridNumberFormat
							},{
								header:"경험치",
								dataIndex:"exp",
								sortable:false,
								width:110,
								renderer:function(value) {
									var data = value.split(",");
									var onwidth = Math.ceil(parseInt(data[1])/parseInt(data[2])*60);
									var offwidth = 60-onwidth;
									var sHTML = '<table cellpadding="0" cellspacing="0" style="table-layout:fixed; width:100px;"><col width="30" /><col width="1" /><col width="'+onwidth+'" /><col width="'+offwidth+'" /><col width="2" /><tr style="height:11px; overflow:hidden;" title="'+data[1]+'/'+data[2]+'">';
									sHTML+= '<td style="font-family:verdana; font-weight:bold; font-size:9px;">LV.<span style="color:#EF5600;">'+data[0]+'</span></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/images/admin/exp_start.gif) repeat-x 50%;"></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/images/admin/exp_on.gif) repeat-x 50%;"></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/images/admin/exp_off.gif) repeat-x 50%;"></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/images/admin/exp_end.gif) repeat-x 50%;"></td>';
									sHTML+= '</tr></table>';
		
									return sHTML;
								}
							},{
								header:"가입일",
								dataIndex:"reg_date",
								sortable:true,
								width:100,
								renderer:function(value) {
									return '<div style="font-family:tahoma;">'+value+'</div>';
								}
							},{
								header:"최종접속일",
								dataIndex:"last_login",
								sortable:true,
								width:100,
								renderer:function(value,p,record) {
									if (record.data.last_week == "TRUE") return '<div style="font-family:tahoma;">'+value+'</div>';
									else return '<div style="font-family:tahoma; color:#C6C6C6;">'+value+'</div>';
								}
							},
							new Ext.grid.CheckboxSelectionModel()
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:MemberAll,
						bbar:new Ext.PagingToolbar({
							pageSize:50,
							store:MemberAll,
							displayInfo:true,
							displayMsg:'{0} - {1} of {2}',
							emptyMsg:"데이터없음"
						}),
						listeners:{
							rowdblclick:{fn:function(grid,idx,e) {
								GetMemberInfo(grid.getStore().getAt(idx).get("idx"),grid.getStore().getAt(idx).get("group"));
							}},
							rowcontextmenu:{fn:MemberRowMenu}
						}
					})
					<?php
					$group = $mDB->DBfetchs($_ENV['table']['group'],'*');
					for ($i=0, $loop=sizeof($group);$i<$loop;$i++) {
					?>,
					new Ext.grid.GridPanel({
						id:"List-<?php echo $group[$i]['group']; ?>",
						title:"<?php echo $group[$i]['title']; ?>",
						border:false,
						cm:new Ext.grid.ColumnModel([
							new Ext.grid.RowNumberer(),
							{
								header:"아이디",
								dataIndex:"user_id",
								sortable:true,
								width:100,
								renderer:function(value,p,record) {
									return '<div style="font-family:verdana;">'+value+' <span style="font-family:tahoma; font-size:10px; color:#C6C6C6;">['+GetNumberFormat(record.data.idx)+']</span></div>';
								}
							},{
								header:"회원종류",
								dataIndex:"type",
								sortable:true,
								width:80,
								renderer:function(value) {
									if (value == "ADMINISTRATOR") {
										return "최고관리자";
									} else if (value == "MODERATOR") {
										return "관리자";
									} else {
										return "일반회원";
									}
								}
							},{
								header:"이름",
								dataIndex:"name",
								sortable:true,
								width:60
							},{
								header:"닉네임",
								dataIndex:"nickname",
								sortable:true,
								width:80
							},{
								header:"주민등록번호",
								dataIndex:"jumin",
								sortable:true,
								width:100,
								renderer:function(value) {
									return '<div style="font-family:tahoma;">'+value+'</div>';
								}
							},{
								header:"이메일",
								dataIndex:"email",
								sortable:true,
								width:180,
								renderer:function(value) {
									return '<div style="font-family:tahoma;">'+value+'</div>';
								}
							},{
								header:"연락처",
								dataIndex:"phone",
								sortable:false,
								width:120,
								renderer:function(value) {
									var data = value.split("||");
		
									if (data.length == 2) {
										return '<div style="font-family:tahoma;">'+data[0]+' <span style="color:#C6C6C6;">['+data[1]+']</span></div>';
									} else {
										return '<div style="font-family:tahoma;">'+value+'</div>';
									}
								}
							},{
								header:"포인트",
								dataIndex:"point",
								sortable:true,
								width:65,
								renderer:GridNumberFormat
							},{
								header:"경험치",
								dataIndex:"exp",
								sortable:false,
								width:110,
								renderer:function(value) {
									var data = value.split(",");
									var onwidth = Math.ceil(parseInt(data[1])/parseInt(data[2])*60);
									var offwidth = 60-onwidth;
									var sHTML = '<table cellpadding="0" cellspacing="0" style="table-layout:fixed; width:100px;"><col width="30" /><col width="1" /><col width="'+onwidth+'" /><col width="'+offwidth+'" /><col width="2" /><tr style="height:11px; overflow:hidden;" title="'+data[1]+'/'+data[2]+'">';
									sHTML+= '<td style="font-family:verdana; font-weight:bold; font-size:9px;">LV.<span style="color:#EF5600;">'+data[0]+'</span></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/images/admin/exp_start.gif) repeat-x 50%;"></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/images/admin/exp_on.gif) repeat-x 50%;"></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/images/admin/exp_off.gif) repeat-x 50%;"></td>';
									sHTML+= '<td style="background:url(<?php echo $_ENV['dir']; ?>/images/admin/exp_end.gif) repeat-x 50%;"></td>';
									sHTML+= '</tr></table>';
		
									return sHTML;
								}
							},{
								header:"가입일",
								dataIndex:"reg_date",
								sortable:true,
								width:100,
								renderer:function(value) {
									return '<div style="font-family:tahoma;">'+value+'</div>';
								}
							},{
								header:"최종접속일",
								dataIndex:"last_login",
								sortable:true,
								width:100,
								renderer:function(value,p,record) {
									if (record.data.last_week == "TRUE") return '<div style="font-family:tahoma;">'+value+'</div>';
									else return '<div style="font-family:tahoma; color:#C6C6C6;">'+value+'</div>';
								}
							},
							new Ext.grid.CheckboxSelectionModel()
						]),
						sm:new Ext.grid.CheckboxSelectionModel(),
						store:Member<?php echo $group[$i]['group']; ?>,
						bbar:new Ext.PagingToolbar({
							pageSize:50,
							store:Member<?php echo $group[$i]['group']; ?>,
							displayInfo:true,
							displayMsg:'{0} - {1} of {2}',
							emptyMsg:"데이터없음"
						}),
						listeners:{
							rowdblclick:{fn:function(grid,idx,e) {
								GetMemberInfo(grid.getStore().getAt(idx).get("idx"),grid.getStore().getAt(idx).get("group"));
							}},
							rowcontextmenu:{fn:MemberRowMenu}
						}
					})
					<?php } ?>
				]
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>