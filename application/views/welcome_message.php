<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to CodeIgniter</title>
	<?php // add css files
		$this->minify->css(array('browser-specific.css', 'style.css'));
		echo $this->minify->deploy_css();

		$this->minify->js(array('helpers.js', 'jqModal.js'));
		echo $this->minify->deploy_js();

	?>
</head>
<body>

<div id="container">
	<h1>Welcome to CodeIgniter Minify library!</h1>

	<div id="body">
		<p>Check out if minified files are working for you.</p>
		<p>Click here to get <a href="assets/script.min.js">JavaScript</a> file</p>
		<p>Click here to check out <a href="assets/style.min.css">CSS</a> </p>
	</div>

	<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
</div>

</body>
</html>