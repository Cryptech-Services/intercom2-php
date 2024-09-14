<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>sender.php</title>
</head>
<body>

<?php

require_once "../intercom2.php";
require_once "./intercom2_config_B.php";

$txData = "Garbanzo";
$msgId  = "echo.2";

$reply = $intercom2->sendMsg($endPoint9, $msgId, $txData);
$error = $reply->getError();
echo "\n<br>TX: " . $txData;
if ($error)
    {
    echo "\n<br>There was an Intercom2 error:\n<br>";
    echo $error;
    }
else
    echo "\n<br>RX: " . $reply->getRxData();

?>

</body>
</html>
