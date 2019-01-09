<?php

require_once('./myid.php');
session_start();

$errorMessage = "";
$signUpMessage = "";

if (isset($_POST["signUp"])) {
    if (empty($_POST["username"])) {
        $errorMessage = 'ユーザーIDが未入力です。';
    } else if (empty($_POST["password"])) {
        $errorMessage = 'パスワードが未入力です。';
    } else if (empty($_POST["password2"])) {
        $errorMessage = 'パスワードが未入力です。';
    }

    if (!empty($_POST["userID"]) && !empty($_POST["username"]) && !empty($_POST["password"]) && !empty($_POST["password2"]) && $_POST["password"] === $_POST["password2"]) {
        $userID = $_POST["userID"];
        $username = $_POST["username"];
        $password = $_POST["password"];

        $strcode = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8'");
        $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_ID, DB_PASS, $strcode);
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        try {

            $stmt = $pdo->prepare("INSERT INTO Users(UserID, Name, Password) VALUES (?, ?, ?)");

            $stmt->execute(array($userID, $username, password_hash($password, PASSWORD_DEFAULT)));

            $signUpMessage = '登録成功しました．MyBox IDは '. $userID . ' です．パスワードは '. $password. ' です．ログインページで試してみましょう．';
        } catch (PDOException $e) {
            $errorMessage = 'データベースエラー';
        }
    } else if($_POST["password"] != $_POST["password2"]) {
        $errorMessage = 'パスワードが一致しません．';
    }
}
?>

<!doctype html>
<html>
    <head>
            <meta charset="UTF-8">
            <title>MyBox ID Register</title>
            <link rel="stylesheet" type="text/css" href="style.css">
            <script src="https://use.fontawesome.com/12725d4110.js"></script>
    </head>
    <body>
	<div class="loginForm">
	    <img src="img/logo.png">
            <form id="loginForm" name="loginForm" action="" method="POST">
                <fieldset>
　　　　            <legend><h3>MyBox Cloud ID 作成フォーム</h3></legend>
                    <div><font color="#ff0000"><?php echo htmlspecialchars($errorMessage, ENT_QUOTES); ?></font></div>
                    <div><font color="#0000ff"><?php echo htmlspecialchars($signUpMessage, ENT_QUOTES); ?></font></div>
		    <div class="cp_iptxt">
                        <input type="text" id="userID" name="userID" placeholder="希望のMyBox ID" value="<?php if (!empty($_POST["userID"])) {echo htmlspecialchars($_POST["userID"], ENT_QUOTES);} ?>">
                        <i class="fa fa-user fa-lg fa-fw" aria-hidden="true"></i>
		    </div>
                    <div class="cp_iptxt">
                        <input type="text" id="username" name="username" placeholder="お名前" value="<?php if (!empty($_POST["username"])) {echo htmlspecialchars($_POST["username"], ENT_QUOTES);} ?>">
		        <i class="fa fa-pencil fa-lg fa-fw" aria-hidden="true"></i>
                    </div>
                    <div class="cp_iptxt">
                        <input type="password" id="password" name="password" value="" placeholder="パスワード">
                        <i class="fa fa-key fa-lg fa-fw" aria-hidden="true"></i>
		    </div>
                    <div class="cp_iptxt">
                        <input type="password" id="password2" name="password2" value="" placeholder="パスワード(確認)">
                        <i class="fa fa-key fa-lg fa-fw" aria-hidden="true"></i>
		    </div>
		    <br>
                    <input type="submit" id="signUp" name="signUp" value="新規登録">
                </fieldset>
            </form>
            <br>
            <form action="login.php">
                <input type="submit" value="ログインページへ戻る">
            </form>
	</div>
    </body>
</html>
