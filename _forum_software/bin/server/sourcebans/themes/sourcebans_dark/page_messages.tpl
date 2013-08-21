<h3 align="left">Messages Left</i></h3>
{foreach from=$messages item=msg}
	<span title="{$msg.Client_ID}">{$msg.Client_Name}</span>: {$msg.Message}<br />
{/foreach}