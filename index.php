<?php
require_once('classes/search.Class.php');
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Search</title>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" />
		<link rel="stylesheet" href="css/style.css" />
	</head>
	<body>
		<div class="container">
			<div class="content">

				<form action="" id="form" method="GET">
					<div class="form-group">
						<input type="text" class="form-control" name="sentence"/>
					</div>
					<input type="submit" class="btn btn-primary" value="Ok" />
				</form>
			</div>
			<?php
			$sentence = isset($_GET['sentence']) ? $_GET['sentence'] : '';
			if($sentence){
				$Search = new Search;
				echo $Search -> SearchSpider($sentence);
			}
			?>
		</div>
	</body>
</html>