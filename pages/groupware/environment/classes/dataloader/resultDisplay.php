<?php

?>

<html><head></head><body>

<h1>OK population done</h1>
<br/>

<ul><li>Companies: <?php echo $params["Companies"] ?></li>
<li>Users: <?php echo $params["Users"] ?></li>
<li>Messages: <?php echo $params["Messages"] ?></li>
<li>Contacts: <?php echo $params["Contacts"] ?></li>
<li>Emails: <?php echo $params["Emails"] ?></li>
<li>Documents: <?php echo $params["Documents"] ?></li>
<li>Webpages: <?php echo $params["Webpages"] ?></li>
<li>Tasks: <?php echo $params["Tasks"] ?></li>
<li>Milestones: <?php echo $params["Milestones"] ?></li>
<li>Linked Objects: <?php echo $params["Link objects"] ?></li>
</ul>

<br/>

<h2>Total time: <?php echo sprintf("%01.4f",$time) ?></h2>

</body></html>