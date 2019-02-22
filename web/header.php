<div class="serviceHeader navbar-fixed">
	<nav>
		<div class="nav-wrapper black-text">
			<!-- ロゴ -->
			<img class="logo-image" src="img/logo.png">
			<ul class="right">
				<!-- ユーザー名 -->
				<div class="chip dropdown-trigger" data-target="UserMenu">
					<img src="img/default.jpg" alt="Contact Person">
					&nbsp;<?php print $_SESSION['userName']; ?>&nbsp;&nbsp;
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
