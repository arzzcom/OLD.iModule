{foreach name=list from=$data item=data}
	{$data.bannerStart}
	{if $data.bannertype != 'TEXT'}
		{$data.bannerfile}
	{else}
		{$data.bannertext}
	{/if}
	{$data.bannerEnd}
{/foreach}