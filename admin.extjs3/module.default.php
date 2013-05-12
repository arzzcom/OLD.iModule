<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/wysiwyg/script/wysiwyg.js"></script>
<script type="text/javascript" src="<?php echo $_ENV['dir']; ?>/module/uploader/script/AzUploader.js"></script>
<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;
	
	<?php
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
		border:<?php echo Request('module') == null ? 'true' : 'false'; ?>,
		title:"<?php echo Request('module') == null ? '모듈기본설정' : ''; ?>",
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
						labelAlign:"right",
						labelWidth:100,
						errorReader:new Ext.form.XmlErrorReader(),
						items:[
							<?php for ($i=0, $loop=sizeof($setup);$i<$loop;$i++) { ?>
							new Ext.form.FieldSet({
								title:"<?php echo $setup[$i]->attributes()->title; ?>",
								autoHeight:true,
								autoWidth:true,
								defaults:{msgTarget:"side"},
								style:"margin:10px;",
								items:[
									<?php
									$isFirst = true;
									foreach ($setup[$i] as $name=>$conf) {
										if ($isFirst == true) {
											$isFirst = false;
										} else {
											echo ',';
										}
										if ($conf->type == 'input') {
									?>
									new Ext.form.TextField({
										fieldLabel:"<?php echo $conf->name; ?>",
										name:"<?php echo $name; ?>",
										width:400,
										allowBlank:<?php echo isset($conf->allowblank) == true && $conf->allowblank == 'true' ? 'true' : 'false'; ?>,
										emptyText:"<?php echo $conf->msg; ?>"
										<?php if (isset($config[$name]) == true || $conf->default) { ?>,value:"<?php echo isset($config[$name]) == true ? $config[$name] : $conf->default; ?>"<?php } ?>
									})
									<?php } elseif ($conf->type == 'password') { ?>
									new Ext.form.TextField({
										fieldLabel:"<?php echo $conf->name; ?>",
										name:"<?php echo $name; ?>",
										width:400,
										inputType:"password",
										allowBlank:<?php echo isset($conf->allowblank) == true && $conf->allowblank == 'true' ? 'true' : 'false'; ?>
										<?php if (isset($config[$name]) == true || $conf->default) { ?>,value:"<?php echo isset($config[$name]) == true ? $config[$name] : $conf->default; ?>"<?php } ?>
									})
									<?php } elseif ($conf->type == 'checkbox') { ?>
									new Ext.form.Checkbox({
										hideLabel:true,
										name:"<?php echo $name; ?>",
										boxLabel:"<?php echo $conf->msg; ?>",
										checked:<?php echo (isset($config[$name]) == true && $config[$name] == 'on') || $conf->default == 'on' ? 'true' : 'false'; ?>
									})
									<?php } elseif ($conf->type == 'select') { ?>
									new Ext.form.ComboBox({
										fieldLabel:"<?php echo $conf->name; ?>",
										hiddenName:"<?php echo $name; ?>",
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
										<?php if (isset($config[$name]) == true || $conf->default) { ?>,value:"<?php echo isset($config[$name]) == true ? $config[$name] : $conf->default; ?>"<?php } ?>
									})
									<?php } elseif ($conf->type == 'directory') { ?>
									new Ext.form.ComboBox({
										fieldLabel:"<?php echo $conf->name; ?>",
										hiddenName:"<?php echo $name; ?>",
										store:new Ext.data.SimpleStore({
											fields:["name"],
											data:[<?php $skinPath = @opendir($_ENV['path'].'/module/'.$module.'/'.$conf->value); $isFirst = true; while ($skin = @readdir($skinPath)) { if ($skin != '.' && $skin != '..' && is_dir($_ENV['path'].'/module/'.$module.'/'.$conf->value.'/'.$skin) == true) { echo ($isFirst == false ? ',' : '').'["'.$skin.'"]'; $isFirst = false; }} @closedir($skinPath); ?>]
										}),
										displayField:"name",
										valueField:"name",
										typeAhead:true,
										mode:"local",
										triggerAction:"all",
										width:200,
										editable:false,
										emptyText:"<?php echo $conf->msg; ?>"
										<?php if (isset($config[$name]) == true || $conf->default) { ?>,value:"<?php echo isset($config[$name]) == true ? $config[$name] : $conf->default; ?>"<?php } ?>
									})
									<?php } elseif ($conf->type == 'permission') { ?>
									new Ext.form.CompositeField({
										labelWidth:85,
										labelAlign:"right",
										fieldLabel:"<?php echo $conf->name; ?>",
										width:400,
										items:[
											new Ext.form.ComboBox({
												id:"ID_<?php echo $name; ?>_select",
												hiddenName:"<?php echo $name; ?>_select",
												typeAhead:true,
												triggerAction:"all",
												lazyRender:true,
												store:new Ext.data.SimpleStore({
													fields:["display","value"],
													data:[["전체","true"],["회원권한 이상","{$member.type} != 'GUEST'"],["모더레이터권한 이상","{$member.type} == 'MODERATOR'"],["최고관리자","{$member.type} == 'ADMINISTRATOR'"],["회원레벨 10이상","{$member.level} >= 10"],["사용자정의",""]]
												}),
												width:140,
												editable:false,
												mode:"local",
												displayField:"display",
												valueField:"value",
												value:"",
												listeners:{select:{fn:function(form) {
													Ext.getCmp("ConfigForm").getForm().findField(form.getName().replace("_select","")).setValue(form.getValue());
												}}}
											}),
											new Ext.form.TextField({
												name:"<?php echo $name; ?>",
												width:150,
												allowBlank:true,
												value:"<?php echo isset($config[$name]) == true ? $config[$name] : $conf->default; ?>",
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
									<?php } elseif ($conf->type == 'number') { ?>
									new Ext.form.NumberField({
										fieldLabel:"<?php echo $conf->name; ?>",
										name:"<?php echo $name; ?>",
										width:100,
										allowBlank:<?php echo isset($conf->allowblank) == true && $conf->allowblank == 'true' ? 'true' : 'false'; ?>,
										emptyText:"<?php echo $conf->msg; ?>"
										<?php if (isset($config[$name]) == true || $conf->default) { ?>,value:"<?php echo isset($config[$name]) == true ? $config[$name] : $conf->default; ?>"<?php } ?>
									})
									<?php } } ?>
								]
							})
							<?php if ($i+1 != $loop) echo ','; } ?>
						]
					})
					<?php } ?>
				]<?php if (Request('module') == null) { ?>,
				bbar:[
					'->',
					new Ext.Button({
						text:"저장",
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_tick.png",
						handler:function() {
							Ext.getCmp("ConfigForm").getForm().submit({url:"<?php echo $_ENV['dir']; ?>/exec/Admin.do.php?action=module&do=config&module=<?php echo $subpage; ?>",submitEmptyText:false,waitMsg:"데이터를 전송중입니다."});
						}
					}),
					new Ext.Button({
						text:"원래대로",
						icon:"<?php echo $_ENV['dir']; ?>/images/admin/icon_cross.png",
						handler:function() {
							Ext.getCmp("ConfigForm").getForm().reset();
						}
					})
				]<?php } ?>
			})
		]
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>

<script type="text/javascript">
Ext.EventManager.onDocumentReady(function() {
	Ext.getCmp("ConfigForm").addListener("actioncomplete",function(form,action) {
		if (action.type == "submit") {
			<?php if (Request('module') != null) echo 'parent.'; ?>Ext.Msg.show({title:"안내",msg:"성공적으로 저장하였습니다.",buttons:Ext.Msg.OK,icon:Ext.MessageBox.INFO,fn:function() {
				<?php if (Request('module') != null) echo 'parent.Ext.getCmp("ConfigWindow").close();'; ?>
			}});
		}
	});
});
</script>
