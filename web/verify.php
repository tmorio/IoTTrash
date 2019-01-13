<?php
require_once('./myid.php');

$strcode = array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET CHARACTER SET 'utf8mb4'");
try {
                $dbh = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME, DB_ID, DB_PASS, $strcode);
                $dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
                echo $e->getMessage();
                exit;
}

$query = "SELECT * FROM EmailChange WHERE verifyCode = :token";
$stmt = $dbh->prepare($query);
$stmt->bindParam(':token', $_GET['token'], PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch();
if((strtotime($result['expireTime']) >= time()) && !empty($result['expireTime'])){
	$query = "UPDATE Users SET mailAddress = :newmail WHERE ID = :UserID";
	$stmt = $dbh->prepare($query);
	$stmt->bindParam(':UserID', $result['UserID'], PDO::PARAM_INT);
	$stmt->bindParam(':newmail', $result['newMail'], PDO::PARAM_STR);
	$stmt->execute();
	$query = "DELETE FROM EmailChange WHERE verifyCode = :token";
	$stmt = $dbh->prepare($query);
	$stmt->bindParam(':token', $_GET['token'], PDO::PARAM_STR);
	$stmt->execute();
	echo 'メールアドレスの変更が完了しました。';
}else{
	echo 'URLの期限が切れているか、URLが無効です。再度お手続きください。';
}
	echo '<br>3秒後にダッシュボードに戻ります。<META http-equiv="Refresh" content="3;URL=dashboard.php">';
?>
