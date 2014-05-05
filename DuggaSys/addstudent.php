<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
			<link type="text/css" href="css/style.css" rel="stylesheet">
		<script type="text/javascript" src="duggasys.js"></script>
	</head>
<body>
	<header>
		<nav id="navigate">
			<img src="css/svg/Up.svg">
			<img onclick="menuDugga()" src="css/svg/SkipB.svg">
		</nav>
		<nav id="user">
			Emil & Martina
			<img src="css/svg/Man.svg">
		</nav>
	</header>
	<div id="content">
		<div id="student-box">
	<form action="" method="post">
		<div id="student-header">Lägg till student!</div>
		<br>
		<br>
		<textarea placeholder="Namn, Pnr, password" name="string" id="string" cols="30"></textarea>
		<br>
		<input type="submit" value="Lägg till student"/>
		<a href="students.php"><input type="button" value="Cancel"/></a>
	</form>

<?php	if(isset($_POST['string'])){
	$pdo = new PDO('mysql:dbname=Imperious;host=localhost', 'root', 'galvaniseradapa');
	$pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );

	function random_password( $length = 8 ) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_-=+;:,.?";
    $password = substr( str_shuffle( $chars ), 0, $length );
    return $password;
	}

	$password = random_password(8);

	$str = $_POST['string'];
	list($username, $ssn)=(explode(", ",$str));

			echo "<br>";
			echo "<br>";
			// Lägger till värden i tabellen.
			$querystring='INSERT INTO user (username, ssn, password, newpassword) VALUES(:username,:ssn,:password, 1);';	
			$stmt = $pdo->prepare($querystring);
			$stmt->bindParam(':username', $username);
			$stmt->bindParam(':ssn', $ssn);
			$stmt->bindParam(':password', $password);
			try {
				$stmt->execute();
				// Skriver ut en alert om användaren är tillagd.
				echo "<script type='text/javascript'>alert('Användare är tillagd')</script>";
				echo $password;
			} catch (PDOException $e) {
				if ($e->getCode()=="23000") {
					// Finns användaren redan i databasen skrivs det ut
				echo "Användare finns redan";
				}
			}
		}
		?>
</div>
</div>

</body>
</html>