<script type="text/javascript">
ContentArea = function(viewport) {
	this.viewport = viewport;

	ContentArea.superclass.constructor.call(this,{
		id:"content",
		region:"center",
		title:"대시보드",
		layout:"fit",
		html:""
	});
};
Ext.extend(ContentArea, Ext.Panel,{});
</script>