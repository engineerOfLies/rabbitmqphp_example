<?php 
session_start(); 
$user = $_SESSION["user"];
if(isset($user) && $user["logged"] == 1){
    header("Location: ../index.php");
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
		integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
	<title>Login Page</title>
</head>

<body>
<?php if(isset($_SESSION["error_msg"])): ?>
<div class="alert alert-danger" role="alert">
	<?= $_SESSION["error_msg"]; ?>
</div>
<?php unset($_SESSION['error_msg']); ?>
<?php endif; ?>

		
	<div class="card mx-auto p-3 mt-5 shadow-sm" style="width: 20rem;">
		
		<div class="card-body">
			<form id="login_form" action="authenticate.php" method="POST">
				<input type="hidden" value="login" />
				<div class="mb-3">
					<label for="email" class="form-label">Email address</label>
					<input name="email" type="email" class="form-control" id="email">

				</div>
				<div class="mb-3">
					<label for="password" class="form-label">Password</label>
					<input name="password" type="password" class="form-control" id="password">
				</div>
				<div class="d-grid gap-2 mt-5">
					<input type="submit" value="login" class="btn btn-primary" />
				</div>
			</form>
		</div>
	</div>

</body>


</html>


</body>

</html>