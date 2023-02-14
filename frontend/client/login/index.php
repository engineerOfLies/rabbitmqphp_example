<?php 
session_start(); 
$user = $_SESSION["user"];
if(isset($user) && $user["logged"] == 1){
    header("Location: home.php");
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

	<div class="card mx-auto p-3 mt-5 shadow-sm" style="width: 20rem;">
		
		<div class="card-body">
			<form id="login_form" action="login.php" method="POST">
				<div class="mb-3">
					<label for="email" class="form-label">Email address</label>
					<input name="email" type="email" class="form-control" id="email">

				</div>

				<div class="mb-3">
					<label for="password" class="form-label">Password</label>
					<input name="password" type="password" class="form-control" id="password">
				</div>


				<div class="d-grid gap-2 mt-5">
					<button class="btn btn-primary" type="submit">Login</button>
				</div>
			</form>
		</div>
	</div>


	<script>
		/*
		function HandleLoginResponse(response) {
			console.log(response)
		}

		const form = document.getElementById("login_form")

		function SendLoginRequest(e) {
			e.preventDefault();

			const email = document.getElementById("email").value
			const password = document.getElementById("password").value
			
			var request = new XMLHttpRequest();
			request.open("POST","login.php",true);
			request.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
			request.onreadystatechange= function ()
			{
				
				if ((this.readyState == 4)&&(this.status == 200))
				{
					HandleLoginResponse(this.responseText);
				}		
			}
			request.send("type=login&email="+email+"&password="+password);
		}

		form.addEventListener('submit', SendLoginRequest);

		*/
	</script>
</body>


</html>


</body>

</html>