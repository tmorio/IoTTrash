<?php
require_once('./myid.php');
require_once('./siteInfo.php');

session_start();

$errorMessage = '';
if (isset($_POST["login"])) {
	if (empty($_POST["userid"])) {
		$errorMessage = 'MyBox IDが入力されていません．';
	} else if (empty($_POST["password"])) {
		$errorMessage = 'パスワードが入力されていません．';
	}
	if (!empty($_POST["userid"]) && !empty($_POST["password"])) {
		$userid = $_POST["userid"];

	$strcode = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8'");
		$pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_ID, DB_PASS, $strcode);
		$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

		try {

			$stmt = $pdo->prepare('SELECT * FROM Users WHERE UserID = ?');
			$stmt->execute(array($userid));

			$password = $_POST["password"];

			if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
				if (password_verify($password, $row['Password'])) {
					session_regenerate_id(true);

					$UserID = $row['UserID'];
					$sql = "SELECT * FROM Users WHERE UserID = $UserID";
					$stmt = $pdo->query($sql);

                    			foreach ($stmt as $row) {
                        			$row['ID'];
                        			$row['Name'];
                                                $row['Group'];
                    			}
                    			$_SESSION['userNo'] = $row['ID'];
                                        $_SESSION['userGroup'] = $row['Group'];
		                        $_SESSION['userName'] = $row['Name'];
					session_regenerate_id(true);
					header("Location: dashboard.php");

				} else {
					$errorMessage = 'MyBox IDまたはパスワードが間違っています．';
				}
			} else {
				$errorMessage = 'MyBox IDまたはパスワードが間違っています．';
			}
		} catch (PDOException $e) {
			$errorMessage = 'データベースへの接続に失敗しました．';
		}
	}
}
?>

<!doctype html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>MyBox Login</title>
		<!-- <link rel="stylesheet" type="text/css" href="style.css"> -->
		<link rel="stylesheet" type="text/css" href="css/materialize.min.css">
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<script type="text/javascript" src="js/jquery-3.3.1.min.js"></script>
		<script type="text/javascript" src="js/materialize.min.js"></script>
		<script type="text/javascript" src="js/footerFixed.js"></script>
		<script>
			$(document).ready(function() {
				M.updateTextFields();
			});
		</script>
		<!-- <script src="https://use.fontawesome.com/12725d4110.js"></script> -->
	</head>
	<body class="grey lighten-5">
		<div class="navbar-fixed">
			<nav>
				<div class="nav-wrapper">
					<img class="logo-image" src="img/logo.png">
				</div>
			</nav>
		</div>
	<div class="dashDisplay">
		<div class="loginForm">
			<!-- <img src="img/logo.png"> -->
			<div class="container">
				<?php
				if($errorMessage!=null){
				echo '
				<div class="row">
					<div class="col s12 m12 pink lighten-5">
						<h5 class="valign-wrapper">
							<i style="font-size: 2.5rem;" class="material-icons orange-text text-darken-5">warning</i>
							<font class="red-text">';
							echo htmlspecialchars($errorMessage, ENT_QUOTES); 
					  echo '</font>
						</h5>
					</div>
				</div>
';
				}
				?>
				<form class="col s12 m12 card blue-grey lighten-5" id="loginForm" name="loginForm" action="" method="POST">
					<div class="card-content grey-text text-darken-4">
						<span class="card-title">ログイン</span>
						<div class="row">
							<div class="input-field col s12 m12">
								<i class="material-icons prefix">person</i>
								<input type="text" id="userid" name="userid" class="validate" value="<?php
									if (!empty($_POST["userid"])) {echo  htmlspecialchars($_POST["userid"], ENT_QUOTES);} ?>">
								<label for="userid" class="active">MyBox ID</label>
							</div>
							<div class="input-field col s12 m12">
								<i class="material-icons prefix">vpn_key</i>
								<input type="password" id="password" name="password" value="">
								<label for="password" class="active">Password</label>
								<br>
							</div>
							<button class="btn waves-effect waves-light" type="submit" id="login" name="login">ログイン</button>
						</div>
					</div>
				</form>

				<br>
				<form action="createID.php" class="col s12 m12 card blue-grey lighten-5">
					<div class="card-content grey-text text-darken-4">
						<span class="card-title">初めての方はこちら</span>
						<button type="submit" class="btn waves-effect waves-light">アカウント作成</button>
						<br>
					</div>
				</form>
			</div>
		</div>
	</div>
		<!-- フッター -->
		<footer id="footer" class="footer center">
			<?php echo FOOTER_INFO; ?>
		</footer>
	</body>
</html>

