<?php
	session_start();
	include_once('config.php');

	$errorMsg = "";

	if(isset($_POST['submit']))
	{
		$username = $_POST['username'];
		$password = $_POST['password'];

		if(empty($username) || empty($password))
		{
			$errorMsg = "Please fill in all fields.";
		}
		else
		{
			$sql = "SELECT user_id, name, surname, username, email, password FROM users WHERE username = :username";
			$selectUser = $conn->prepare($sql);
			$selectUser->bindParam(":username", $username);
			$selectUser->execute();

			$data = $selectUser->fetch();

			if($data == false)
			{
				$errorMsg = "The user does not exist.";
			}
			else
			{
				if(password_verify($password, $data['password']))
				{
					$_SESSION['user_id']  = $data['user_id'];
					$_SESSION['username'] = $data['username'];
					$_SESSION['email']    = $data['email'];
					$_SESSION['name']     = $data['name'];
					$_SESSION['surname']  = $data['surname'];

					header('Location: home.php');
					exit();
				}
				else
				{
					$errorMsg = "The password is not valid.";
				}
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Sign In - SproutSync</title>

	<!-- Bootstrap 5 -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<!-- Boxicons -->
	<link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">

	<style>
		:root {
			--bg-lavender:   #e7e3f4;
			--bg-lavender-2: #ded9ef;
			--card-white:    #ffffff;
			--teal-dark:     #18352f;
			--teal-mid:      #1f4a40;
			--teal-soft:     #2c6b5c;
			--text-dark:     #1c2b27;
			--text-muted:    #7c7a88;
			--alert-red:     #b23a4a;
			--alert-bg:      #f7e6ea;
			--input-bg:      #f3f1fa;
			--border-soft:   #e3e0ee;
		}

		* { box-sizing: border-box; }

		body {
			margin: 0;
			min-height: 100vh;
			background: linear-gradient(160deg, var(--bg-lavender) 0%, var(--bg-lavender-2) 100%);
			font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
			color: var(--text-dark);
			display: flex;
			justify-content: center;
		}

		/* phone-style app shell */
		.app-shell {
			width: 100%;
			max-width: 430px;
			min-height: 100vh;
			background: linear-gradient(160deg, var(--bg-lavender) 0%, var(--bg-lavender-2) 100%);
			display: flex;
			flex-direction: column;
			position: relative;
			box-shadow: 0 0 40px rgba(0,0,0,0.08);
			padding-bottom: 20px;
		}

		/* top bar */
		.app-header {
			display: flex;
			align-items: center;
			justify-content: space-between;
			padding: 18px 22px;
		}
		.app-header .brand {
			display: flex;
			align-items: center;
			gap: 10px;
			font-weight: 700;
			font-size: 1.25rem;
			color: var(--teal-dark);
		}
		.app-header .logo-circle {
			width: 38px;
			height: 38px;
			border-radius: 50%;
			background: var(--teal-soft);
			display: flex;
			align-items: center;
			justify-content: center;
			color: #cdeede;
			font-size: 1.3rem;
		}
		.app-header .bx-bell {
			font-size: 1.4rem;
			color: var(--teal-dark);
		}

		/* heading area */
		.page-intro {
			padding: 8px 22px 4px;
		}
		.page-intro h1 {
			font-weight: 800;
			font-size: 1.9rem;
			color: var(--teal-dark);
			margin: 0 0 6px;
		}
		.page-intro p {
			color: var(--text-muted);
			font-size: 0.95rem;
			margin: 0;
			line-height: 1.4;
		}

		/* card holding the form */
		.form-card {
			background: var(--card-white);
			margin: 18px 18px 24px;
			border-radius: 22px;
			padding: 24px 20px 28px;
			box-shadow: 0 10px 30px rgba(40, 30, 70, 0.08);
		}

		.input-box {
			position: relative;
			margin-bottom: 14px;
		}
		.input-box input {
			width: 100%;
			border: 1px solid var(--border-soft);
			background: var(--input-bg);
			border-radius: 14px;
			padding: 14px 44px 14px 16px;
			font-size: 0.95rem;
			color: var(--text-dark);
			outline: none;
			transition: border-color .2s, box-shadow .2s;
		}
		.input-box input::placeholder { color: #a7a4b6; }
		.input-box input:focus {
			border-color: var(--teal-soft);
			box-shadow: 0 0 0 3px rgba(44, 107, 92, 0.12);
		}
		.input-box i {
			position: absolute;
			right: 16px;
			top: 50%;
			transform: translateY(-50%);
			color: var(--teal-soft);
			font-size: 1.15rem;
		}

		.forgot-link {
			text-align: right;
			margin: -4px 0 12px;
		}
		.forgot-link a {
			color: var(--teal-soft);
			font-size: 0.85rem;
			text-decoration: none;
			font-weight: 600;
		}
		.forgot-link a:hover { text-decoration: underline; }

		.btn-sprout {
			width: 100%;
			background: var(--teal-dark);
			color: #eafaf2;
			border: none;
			border-radius: 14px;
			padding: 15px;
			font-weight: 700;
			letter-spacing: 0.5px;
			text-transform: uppercase;
			font-size: 0.85rem;
			margin-top: 6px;
			transition: background .2s, transform .1s;
		}
		.btn-sprout:hover { background: var(--teal-mid); }
		.btn-sprout:active { transform: scale(0.99); }

		.divider {
			text-align: center;
			color: var(--text-muted);
			font-size: 0.82rem;
			margin: 20px 0 14px;
			position: relative;
		}
		.divider::before, .divider::after {
			content: "";
			position: absolute;
			top: 50%;
			width: 28%;
			height: 1px;
			background: var(--border-soft);
		}
		.divider::before { left: 0; }
		.divider::after  { right: 0; }

		.social-icons {
			display: flex;
			justify-content: center;
			gap: 14px;
			margin-bottom: 4px;
		}
		.social-icons a {
			width: 50px;
			height: 50px;
			border-radius: 14px;
			border: 1px solid var(--border-soft);
			background: var(--input-bg);
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 1.4rem;
			color: var(--teal-dark);
			text-decoration: none;
			transition: background .2s, transform .1s;
		}
		.social-icons a:hover { background: #e9e6f5; transform: translateY(-2px); }

		.register-link {
			text-align: center;
			margin-top: 18px;
			font-size: 0.9rem;
			color: var(--text-muted);
		}
		.register-link a {
			color: var(--teal-dark);
			font-weight: 700;
			text-decoration: none;
		}
		.register-link a:hover { text-decoration: underline; }

		.error-alert {
			background: var(--alert-bg);
			color: var(--alert-red);
			border-radius: 12px;
			padding: 12px 14px;
			font-size: 0.88rem;
			font-weight: 600;
			margin-bottom: 16px;
			display: flex;
			align-items: center;
			gap: 8px;
		}

		/* bottom tab navigation */
		.tab-nav {
			position: sticky;
			bottom: 0;
			background: var(--teal-dark);
			display: flex;
			justify-content: space-around;
			padding: 10px 8px calc(10px + env(safe-area-inset-bottom));
			border-top-left-radius: 22px;
			border-top-right-radius: 22px;
		}
		.tab-nav a {
			color: #8fb6aa;
			text-decoration: none;
			display: flex;
			flex-direction: column;
			align-items: center;
			gap: 2px;
			font-size: 0.68rem;
			font-weight: 600;
			letter-spacing: 0.3px;
			flex: 1;
			transition: color .2s;
		}
		.tab-nav a i { font-size: 1.35rem; }
		.tab-nav a.active, .tab-nav a:hover { color: #eafaf2; }
	</style>
</head>
<body>
	<div class="app-shell">

		<!-- Header -->
		<div class="app-header">
			<div class="brand">
				<span class="logo-circle"><i class='bx bxs-leaf'></i></span>
				SproutSync
			</div>

		</div>

		<!-- Intro -->
		<div class="page-intro">
			<h1>Welcome Back</h1>
			<p>Sign in to keep tracking your plants and sensors.</p>
		</div>

		<!-- Form card -->
		<div class="form-card">

			<?php if(!empty($errorMsg)): ?>
				<div class="error-alert">
					<i class='bx bxs-error-circle'></i>
					<?php echo htmlspecialchars($errorMsg); ?>
				</div>
			<?php endif; ?>

			<form action="login.php" method="post">

				<div class="input-box">
					<input type="text" placeholder="Username" name="username" required>
					<i class='bx bxs-user'></i>
				</div>

				<div class="input-box">
					<input type="password" placeholder="Password" name="password" required>
					<i class='bx bxs-lock-alt'></i>
				</div>

				<div class="forgot-link">
					<a href="#">Forgot Password?</a>
				</div>

				<button name="submit" type="submit" class="btn-sprout">Sign In</button>

				<div class="divider">or sign in with</div>

				<!-- Fake social buttons (UI only) -->
				<div class="social-icons">
					<a href="#" title="Google"><i class='bx bxl-google'></i></a>
					<a href="#" title="Facebook"><i class='bx bxl-facebook'></i></a>
					<a href="#" title="GitHub"><i class='bx bxl-github'></i></a>
					<a href="#" title="Apple"><i class='bx bxl-apple'></i></a>
				</div>

				<div class="register-link">
					Don't have an account? <a href="index.php">Register</a>
				</div>
			</form>
		</div>

	

	</div>

	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script>
		// Fake social login buttons — just a friendly message for the demo
		document.querySelectorAll('.social-icons a').forEach(function (btn) {
			btn.addEventListener('click', function (e) {
				e.preventDefault();
				var provider = this.getAttribute('title');
				alert(provider + ' sign-in is coming soon! 🌱');
			});
		});
	</script>
</body>
</html>