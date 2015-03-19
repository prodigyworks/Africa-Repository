<ul class="tabstrip">
	<li <?php if (! isset($_GET['mode']) || $_GET['mode'] == "I") echo " class='current'"; ?>><a href="messages.php?mode=I">Inbox <span id='messages'></span></a></li><li<?php if (isset($_GET['mode']) && $_GET['mode'] == "S") echo " class='current'"; ?>><a href="messages.php?mode=S">Sent</a></li><li <?php if (isset($_GET['mode']) && $_GET['mode'] == "D") echo " class='current'"; ?>><a href="messages.php?mode=D">Archive</a></li><li class="last"><a href="messagecompose.php"><img src='images/add.png' />&nbsp;Compose</a></li>
</ul>
<script>
	$(document).ready(
			function() {
				if($("#messagecount").text() != "") {
					$("#messages").text("(" + $("#messagecount").text() + ")");
				}
			}
		);
</script>

