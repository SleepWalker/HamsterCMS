<div id="page">

<div id="content">
<h2>Description</h2>
<p>
This script checks if your server configuration meets the requirements
for running <a href="http://hamstecms.com">HamsterCMS</a> and <a href="http://www.yiiframework.com/">Yii</a> Web applications.
It checks if the server is running the right version of PHP,
if appropriate PHP extensions have been loaded, and if php.ini file settings are correct.
</p>

<h2>Conclusion</h2>
<p>
<?php if($result>0): ?>
Congratulations! Your server configuration satisfies all requirements by HamsterCMS.
<?php elseif($result<0): ?>
Your server configuration satisfies the minimum requirements by HamsterCMS. Please pay attention to the warnings listed below if your application will use the corresponding features.
<?php else: ?>
Unfortunately your server configuration does not satisfy the requirements by HamsterCMS.
<?php endif; ?>
</p>

<h2>Details</h2>

<table class="result">
<tr><th>Name</th><th>Result</th><th>Required By</th><th>Memo</th></tr>
<?php foreach($requirements as $requirement): ?>
<tr>
	<td>
	<?php echo $requirement[0]; ?>
	</td>
	<td class="<?php echo $requirement[2] ? 'passed' : ($requirement[1] ? 'failed' : 'warning'); ?>">
	<?php echo $requirement[2] ? 'Passed' : ($requirement[1] ? 'Failed' : 'Warning'); ?>
	</td>
	<td>
	<?php echo $requirement[3]; ?>
	</td>
	<td>
	<?php echo $requirement[4]; ?>
	</td>
</tr>
<?php endforeach; ?>
</table>

<table>
<tr>
<td class="passed">&nbsp;</td><td>passed</td>
<td class="failed">&nbsp;</td><td>failed</td>
<td class="warning">&nbsp;</td><td>warning</td>
</tr>
</table>

</div><!-- content -->

</div><!-- page -->
