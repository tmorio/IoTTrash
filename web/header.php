<div class="serviceHeader navbar-fixed">
	<nav>
		<div class="nav-wrapper black-text">
			<!-- ロゴ -->
			<a href="dashboard.php"><img class="logo-image" src="img/logo.png"></a>
			<ul class="right">
				<!-- ユーザー名 -->
				<div class="chip dropdown-trigger" data-target="UserMenu">
					<?php
					if(!empty($_SESSION['PhotoID'])){
						echo '<img src="img/users/' . $_SESSION['PhotoID'] . '.jpg" alt="Contact Person">';
					}else{
						echo '<img src="img/default.jpg" alt="Contact Person">';
					}
					?>
					&nbsp;<?php print htmlspecialchars($_SESSION['userName'], ENT_QUOTES, 'UTF-8'); ?>&nbsp;&nbsp;
				</div>
				<ul id='UserMenu' class='dropdown-content'>
					<li><a href="./settings.php">設定</a></li>
					<li><a href="./logout.php">ログアウト</a></li>
				</ul>
				&thinsp;
			</ul>
		</div>
	</nav>
</div>
<script>
	$('.dropdown-trigger').dropdown();
</script>
