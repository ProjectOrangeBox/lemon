<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<title>Export</title>
	<meta name="description" content="">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/mini.css/3.0.1/mini-default.min.css">
</head>

<body>
	<p>Welcome Home!</p>
	<p>
		<a href="/test/abc/123">Test ABC 123</a>
	</p>
	<p>
		<a href="/test/xyz/890">Test XYZ 890</a>
	</p>
	<p>
		<a href="/test/123/123">Test 123 123 - Error</a>
	</p>

	<p>
		<?= $siteUrl ?>
	</p>

	<p>
		<?= $name ?> <?= $version ?>
	</p>

</body>

</html>