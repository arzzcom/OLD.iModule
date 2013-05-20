<script type="text/javascript">
var ContentArea = function(viewport) {
	this.viewport = viewport;
	
	<?php
	$module = Request('module');
	if ($module != null) {
		$mModule = new Module($module);
	}
	$config = $mModule->GetConfig();

	if (file_exists($mModule->modulePath.'/admin/default.php') == true) {
		ob_start();
		REQUIRE_ONCE $mModule->modulePath.'/admin/default.php';
		$formObject = ob_get_contents();
		ob_end_clean();
		
		echo 'var formObject = '.str_replace(array('<script type="text/javascript">','</script>'),'',$formObject);
	}
	?>

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		layout:"fit",
		margin:"<?php echo $module == null ? '0 5 0 0' : '0 0 0 0'; ?>",
		border:<?php echo $module == null ? 'true' : 'false'; ?>,
		title:"<?php echo $module == null ? '모듈기본설정' : ''; ?>",
		items:[
			new Ext.Panel({
				autoScroll:true,
				border:false,
				lazyRender:true,
				items:[
					<?php
					if (file_exists($mModule->modulePath.'/admin/default.php') == true) {
						echo 'formObject';
					} else {
						$setup = $mModule->GetModuleXML('config');
						$setup = $setup;
					?>
					new Ext.form.FormPanel({
						id:"ConfigForm",
						border:false,
						autoScroll:true,
						bodyPadding:"10 10 5 10",
						fieldDefaults:{labelAlign:"right",labelWidth:100,anchor:"100%"},
						items:[
							<?php for ($i=0, $loop=sizeof($setup);$i<$loop;$i++) { ?>
							new Ext.form.FieldSet({
								title:"<?php echo $setup[$i]->attributes()->title; ?>",
								items:[
									<?php
									$isFirst = true;
									foreach ($setup[$i] as $name=>$conf) {
										if ($isFirst == true) {
											$isFirst = false;
										} else {
											echo ',';
										}
										if ($conf->type == 'input') { if ($conf->fixmsg) {
									?>
									new Ext.form.FieldContainer({
										fieldLabel:"<?php echo $conf->name; ?>",
										layout:"hbox",
										items:[
											new Ext.form.TextField({
												name:"<?php echo $name; ?>",
												width:200,
												allowBlank:<?php echo isset($conf->allowblank) == true && $conf->allowblank == 'true' ? 'true' : 'false'; ?>,
												emptyText:"<?php echo $conf->msg; ?>"
											}),
											new Ext.form.DisplayField({
												value:"&nbsp;<?php echo $conf->fixmsg; ?>"
											})
										]
									})
									<?php } else { ?>
									new Ext.form.TextField({
										fieldLabel:"<?php echo $conf->name; ?>",
										name:"<?php echo $name; ?>",
										allowBlank:<?php echo isset($conf->allowblank) == true && $conf->allowblank == 'true' ? 'true' : 'false'; ?>,
										emptyText:"<?php echo $conf->msg; ?>"
									})
									<?php } } elseif ($conf->type == 'password') { ?>
									new Ext.form.TextField({
										fieldLabel:"<?php echo $conf->name; ?>",
										name:"<?php echo $name; ?>",
										inputType:"password",
										allowBlank:<?php echo isset($conf->allowblank) == true && $conf->allowblank == 'true' ? 'true' : 'false'; ?>
									})
									<?php } elseif ($conf->type == 'checkbox') { ?>
									new Ext.form.Checkbox({
										fieldLabel:"<?php echo $conf->name; ?>",
										name:"<?php echo $name; ?>",
										boxLabel:"<?php echo $conf->msg; ?>"
									})
									<?php } elseif ($conf->type == 'select') { ?>
									new Ext.form.ComboBox({
										fieldLabel:"<?php echo $conf->name; ?>",
										name:"<?php echo $name; ?>",
										store:new Ext.data.SimpleStore({
											fields:["display","value"],
											data:[<?php echo $conf->option; ?>]
										}),
										displayField:"display",
										valueField:"value",
										typeAhead:true,
										mode:"local",
										triggerAction:"all",
										width:200,
										editable:false,
										emptyText:"<?php echo $conf->msg; ?>"
									})
									<?php } elseif ($conf->type == 'directory') { ?>
									new Ext.form.ComboBox({
										fieldLabel:"<?php echo $conf->name; ?>",
										name:"<?php echo $name; ?>",
										store:new Ext.data.SimpleStore({
											fields:["name"],
											data:[<?php $skinPath = @opendir($_ENV['path'].'/module/'.$module.'/'.$conf->value); $isFirst = true; while ($skin = @readdir($skinPath)) { if ($skin != '.' && $skin != '..' && is_dir($_ENV['path'].'/module/'.$module.'/'.$conf->value.'/'.$skin) == true) { echo ($isFirst == false ? ',' : '').'["'.$skin.'"]'; $isFirst = false; }} @closedir($skinPath); ?>]
										}),
										displayField:"name",
										valueField:"name",
										typeAhead:true,
										mode:"local",
										triggerAction:"all",
										editable:false,
										emptyText:"<?php echo $conf->msg; ?>"
									})
									<?php } elseif ($conf->type == 'permission') { ?>
									new Ext.form.FieldContainer({
										fieldLabel:"<?php echo $conf->name; ?>",
										layout:"hbox",
										items:[
											new Ext.form.ComboBox({
												id:"ID_<?php echo $name; ?>_select",
												name:"<?php echo $name; ?>_select",
												typeAhead:true,
												triggerAction:"all",
												lazyRender:true,
												store:new Ext.data.ArrayStore({
													fields:["display","value"],
													data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
												}),
												width:140,
												editable:false,
												mode:"local",
												displayField:"display",
												valueField:"value",
												style:{marginRight:"5px"},
												listeners:{select:{fn:function(form) {
													Ext.getCmp("ConfigForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
												}}}
											}),
											new Ext.form.TextField({
												name:"<?php echo $name; ?>",
												flex:1,
												allowBlank:true,
												style:{marginRight:"5px"},
												listeners:{
													afterrender:{fn:function(form){
														if (Ext.getCmp("ID_"+form.getName()+"_select").getStore().find("value","<?php echo isset($config[$name]) == true ? $config[$name] : $conf->default; ?>",false,false) == -1) {
															Ext.getCmp("ID_"+form.getName()+"_select").setValue("");
														} else {
															Ext.getCmp("ID_"+form.getName()+"_select").setValue("<?php echo isset($config[$name]) == true ? $config[$name] : $conf->default; ?>");
														}
													}},
													blur:{fn:function(form) {
														if (Ext.getCmp("ConfigForm").getForm().findField(form.getName()+"_select").getStore().find("value",form.getValue(),false,false) == -1) {
															Ext.getCmp("ConfigForm").getForm().findField(form.getName()+"_select").setValue("");
														} else {
															Ext.getCmp("ConfigForm").getForm().findField(form.getName()+"_select").setValue(form.getValue());
														}
													}}
												}
											}),
											new Ext.Button({
												text:"권한설정도움말",
												handler:function() {
													parent.PermissionHelp();
												}
											})
										]
									})
									<?php } elseif ($conf->type == 'number') { if ($conf->fixmsg) { ?>
									new Ext.form.FieldContainer({
										fieldLabel:"<?php echo $conf->name; ?>",
										layout:"hbox",
										items:[
											new Ext.form.NumberField({
												name:"<?php echo $name; ?>",
												width:100,
												allowBlank:<?php echo isset($conf->allowblank) == true && $conf->allowblank == 'true' ? 'true' : 'false'; ?>,
												emptyText:"<?php echo $conf->msg; ?>"
											}),
											new Ext.form.DisplayField({
												flex:1,
												value:"&nbsp;<?php echo $conf->fixmsg; ?>"
											})
										]
									})
									<?php } else { ?>
									new Ext.form.NumberField({
										fieldLabel:"<?php echo $conf->name; ?>",
										name:"<?php echo $name; ?>",
										allowBlank:<?php echo isset($conf->allowblank) == true && $conf->allowblank == 'true' ? 'true' : 'false'; ?>,
										emptyText:"<?php echo $conf->msg; ?>"
									})
									<?php } } } ?>
								]
							})
							<?php if ($i+1 != $loop) echo ','; } ?>
						]
					})
					<?php } ?>
				]
			})
		]<?php if ($module == null) { ?>,
		bbar:[
			'->',
			new Ext.Button({
				text:"저장",
				icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_tick.png",
				handler:function() {
					Ext.getCmp("ConfigForm").getForm().submit({
						url:"<?php echo $_ENV['dir']; ?>/exec/Admin.do.php?action=module&do=config&module=<?php echo $subpage; ?>",
						submitEmptyText:false,
						waitTitle:"잠시만 기다려주십시오.",
						waitMsg:"모듈 기본설정을 저장하고 있습니다.",
						success:function(form,action) {
							Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.INFO,fn:function(button) {
								<?php if ($module != null) echo 'parent.Ext.getCmp("ConfigWindow").close();'; ?>
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
				text:"원래대로",
				icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_cross.png",
				handler:function() {
					Ext.getCmp("ConfigForm").getForm().reset();
				}
			})
		]<?php } ?>,
		listeners:{render:{fn:function() {
			Ext.getCmp("ConfigForm").getForm().load({
				url:"<?php echo $_ENV['dir']; ?>/exec/Admin.get.php?action=module&get=config&module=<?php echo $mModule->GetModuleName(); ?>",
				submitEmptyText:false,
				waitTitle:"잠시만 기다려주십시오.",
				waitMsg:"데이터를 로딩중입니다.",
				success:function(form,action) {},
				failure:function(form,action) {
					Ext.Msg.show({title:"에러",msg:"서버에 이상이 있어 데이터를 불러오지 못하였습니다.<br />잠시후 다시 시도해보시기 바랍니다.",buttons:Ext.Msg.OK,icon:Ext.Msg.ERROR});
				}
			})
		}}}
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>