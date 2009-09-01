{strip}
{form enctype="multipart/form-data"}
<input type="hidden" name="page" value="{$page}" />
{jstabs}
	{jstab title="Dump Config"}
		{legend legend="Dump bitweaver configuration data to YAML format."}
		<div class="row">
			{formhelp note="Select a package to get its configuration data or select all to get all kernel configuration information."}
			{html_options options=$activePackages name=kernel_config_pkg selected=$smarty.request.kernel_config_pkg}
		</div>
		<div class="row submit">
			<input type="submit" name="dump" value="{tr}Dump Settings to YAML{/tr}" />
		</div>
		{/legend}
		{if $yaml}
		{legend legend="Requested data dump:"}
			{formhelp note="Copy paste this text as is to a .yaml file."}
			<pre>
				{$yaml}
			</pre>
		{/legend}
		{/if}
	{/jstab}
	{jstab title="Upload Config File"}
		{legend legend="Configure bitweaver with YAML File"}
		<div class="row">
			{formhelp note="Select a YAML bitweaver configuration file to upload. It will be automatically processed"}
			<input type="file" name="upload" />
		</div>
		<div class="row submit">
			<input type="submit" name="submit_upload" value="{tr}Upload YAML File{/tr}" />
		</div>
		{/legend}
		{if $config_log}
		{legend legend="Configuration Results:"}
			{formhelp note="Your YAML upload resulted in these changes:"}
			<pre>
				{$config_log}
			</pre>
		{/legend}
		{/if}
	{/jstab}
{/jstabs}
{/form}
{/strip}
