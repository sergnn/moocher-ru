<?php
header('Refresh: 0; URL=/travel/' . $_REQUEST["sname"]);
include_once "../includes/inc.db-pdo.php";
include_once "../includes/inc.filter.php";

$sname = Filter::sql_string($_POST["sname"]);
$name = Filter::sql_string($_POST["name"]);
$bdate = Filter::sql_string($_POST["bdate"]);
$edate = Filter::sql_string($_POST["edate"]);
$visible = intval($_POST["visible"]);
$text = $_POST["text"];
$id = intval($_POST["id"]);

include "remotetypograf.php";

$remoteTypograf = new RemoteTypograf('UTF-8');

$remoteTypograf->htmlEntities();
$remoteTypograf->br(false);
$remoteTypograf->p(false);
$remoteTypograf->nobr(3);
$remoteTypograf->quotA('laquo raquo');
$remoteTypograf->quotB('bdquo ldquo');

$text = $pdo->quote($remoteTypograf->processText($text));
$name = $pdo->quote($remoteTypograf->processText($name));

$sql = "UPDATE `travel` SET `sname` = '" . $sname . "', `name` = '" . $name . "', `bdate` = '" . $bdate . "', `edate` = '" . $edate . "', `visible` = '" . $visible . "', `text` = '" . $text . "' WHERE `travel`.`id` = '" . $id . "';";

$pdo->query($sql);