<?php

$month_r = array("января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря");
$month_i = array("январь", "февраль", "март", "апрель", "май", "июнь", "июль", "август", "сентябрь", "октябрь", "ноябрь", "декабрь");

function printform($action="new", $id='', $sname='', $name='', $bdate='0000-00-00 00:00:00', $edate='0000-00-00 00:00:00', $visible=false, $text=''){
	echo '<form action="/action/action.' . $action . '.php" method=POST>';
	echo '<input type="hidden" name="page" value="travel">';
	echo '<input type="hidden" name="id" value="' . $id . '">';
	echo '<input type="text" size="40" value="' . $sname . '" placeholder="отображаемое название" name="sname">';
	echo '<input type="text" size="40" value="' . $name . '" placeholder="название статьи" name="name"><br>';
	echo '<input type="text" size="40" value="' . $bdate . '" placeholder="первый день" name="bdate">';
	echo '<input type="text" size="40" value="' . $edate . '" placeholder="последний день" name="edate"><br>';
	echo '<input type="checkbox" ' . ($visible ? 'checked' : '') . ' name="visible" value="1">Видимый<br>';
	echo '<textarea cols=120 rows=40 name="text">' . $text . '</textarea><br><br>';
	echo '<input type="submit" value="Сохранить">';
	echo '</form>';
}


$list = true;

if (isset($_GET["theme"])){
	if ($_GET["theme"] == 'new'){
		printform();
		$list = false;
	}

	$theme = Filter::sql_string($_GET["theme"]);
	$res = $pdo->query("SELECT * FROM travel WHERE sname = '" . $theme . "' LIMIT 1") or die(mysql_error());
	if($res->rowCount() > 0){
		foreach($res as $row) {
			if((intval($_GET["edit"]) == 1) && (intval($_SESSION["uid"]) > 0)) {
				printform('edit', $row['id'], $row['sname'], $row['name'], $row['bdate'], $row['edate'], $row['visible'], $row['text']);
			}else{
				$bdate = strtotime($row['bdate']);
				$edate = strtotime($row['edate']);
				echo '<div class="pole">';
				echo '<h1 class="main_title">' . $row['name'] . '</h1>'; 
				echo '<p class="body_smaller">';
				if (($bdate != $edate) && ($edate != strtotime("0000-00-00 00:00:00"))){
					if(date('Y', $bdate) != date('Y', $edate)){
						echo date('j', $bdate) . ' ' . $month_r[date('n', $bdate)-1] . ' ' . date('Y', $bdate);
						echo ' ... ' . date('j', $edate) . ' ' . $month_r[date('n', $edate)-1] . ' ' . date('Y', $edate);
					}elseif(date('n', $bdate) != date('n', $edate)){
						echo date('j', $bdate) . ' ' . $month_r[date('n', $bdate)-1];
						echo ' ... ' . date('j', $edate) . ' ' . $month_r[date('n', $edate)-1] . ' ' . date('Y', $edate);
					}else{
						echo date('j', $bdate) . '–' . date('j', $edate) . ' ' . $month_r[date('n', $edate)-1] . ' ' . date('Y', $edate);
					}
				}else
					echo date('j', $bdate) . ' ' . $month_r[date('n', $bdate)-1] . ' ' . date('Y', $bdate);
				echo '</p>';

				$patterns = array();
				$patterns[0] = '/^<img(.*)>/im';
				$patterns[1] = '/^(.+)$/im';
				$patterns[2] = '/<lj user="([a-zA-Z0-9]*)" \/>/im';
				$patterns[3] = '/<b><span style="font-size: 1\.4em"><\/span><\/b><br>/';
				
				$replacements = array();
				$replacements[0] = '<div class="image"><img${1} border=1></div><br>';
				$replacements[1] = '<p class="body">${1}</p>';
				$replacements[2] = '<a href="http://${1}.livejournal.com/">${1}</a>';
				$replacements[3] = '';

				$text = $row["text"];
				$month = '';
				$i = 1;
				preg_match_all('/<a name="([a-zA-z-]+)"><\/a>(.*)/i', $text, $matches);
				foreach ($matches[1] as $value) {
					$txt = '';
					$res1 = $pdo->query("SELECT * FROM `travel` WHERE `text` LIKE '%name=\"" . $value . "\"%' ORDER BY bdate DESC") or die(mysql_error());
					if($res1->rowCount() > 1){
						$txt .= '<table class="mini">';
						$year = "0";
						foreach($res1 as $row1){
							if(date('Y', strtotime($row['bdate'])) != $year)
								$txt .= '<tr><td><b>' . ($year = date('Y', strtotime($row['bdate']))) . '</b><br>';
							$month = $month_i[date('n', strtotime($row1['bdate'])) - 1] . ($month_i[date('n', strtotime($row1['bdate'])) - 1] == $month ? ' ' . ++$i : '');
							if($row1['sname'] != $row['sname'])
								$txt .= '<a href="/travel/'. $row1['sname'] . '/#' . $value . '">' . $month . '</a><br>';
							else
								$txt .= '<span class="active">' . $month . '</span><br>';
						}
						$txt .= '</table><br>';
					}
					$text = preg_replace('/<a name="' . $value . '"><\/a>(.*)/i', '<a name="' . $value . '"></a><b><span style="font-size: 1.4em">${1}</span></b><br><br>' . $txt, $text);
				}
				
				echo preg_replace($patterns, $replacements, $text);
				echo '</div>';
				
				echo '<br><br><table border="0" width="100%" id="Timeline" lang="ru">';
				echo '<tr valign="top">';

				$list1 = array();
				$list2 = array();

				$res1 = $pdo->query("SELECT * FROM travel WHERE (`bdate` < '" . $row['bdate'] . "') AND (visible = 1) ORDER BY `bdate` DESC LIMIT 4;") or die(mysql_error());
				if($res1->rowCount() > 0)
					foreach($res1 as $row1)
						$list1[] = $row1;
				krsort($list1);

				$list = array($row);

				$res1 = $pdo->query("SELECT * FROM travel WHERE (`bdate` > '" . $row['bdate'] . "') AND (visible = 1) ORDER BY `bdate` ASC LIMIT 4;") or die(mysql_error());
				if($res1->rowCount() > 0)
					foreach($res1 as $row1)
						$list2[] = $row1;
				$list = array_merge($list1, $list, $list2);

				if(count($list1)<2)
					array_splice($list, 5);
				elseif(count($list2)<2)
					array_splice($list, 0, count($list2));
				else{
					array_splice($list, 7);
					array_splice($list, 0, 2);
				}


				foreach ($list as $value) {
					echo '<td width="20%">';
					echo '<p class="smaller">';
					if($value['id'] == $row['id'])
						echo '<b>' . $value['name'] . '</b>';
					else
						echo '<a href="/travel/' . $value['sname'] . '/" class="underline">' . $value['name'] . '</a>';
					echo '</p>';
				}
						

				echo '</table>';
			}
		}
		$list = false;
	}else{
		$res = $pdo->query("SELECT * FROM travel_countries WHERE country = '" . $theme . "' LIMIT 1") or die(mysql_error());
		if($res->rowCount() > 0)
			foreach($res as $row) {
				echo "<h1>" . $row["country_name"] . "</h1>";
				$j = 1;
				$res1 = $pdo->query("SELECT * FROM travel_cities WHERE country = '" . $row["id"] . "' ORDER BY city_name") or die(mysql_error());
				if($res1->rowCount() > 0)
					foreach($res1 as $row1) {
						echo $j++ , '. ' . $row1["city_name"] . "<br>";
					}
			}
		$list = false;
	}
}

if ($list) {
	echo '<br><img style="margin-left: 2em;margin-bottom: 1em;" src="/jj/2013.08.20_ukraine_day4/IMG_7899.jpg"><br>';
	$res = $pdo->query("SELECT * FROM travel ORDER BY bdate DESC , `id` DESC ") or die(mysql_error());
	$year = 0;
	$month = 0;
	if($res->rowCount() > 0)
		foreach($res as $row) {
			echo '<p class="list p-double-margin ">';
			if ($year != date('Y', strtotime($row['bdate'])))
				echo '<span class="classis-in-pb">' . ($year = date('Y', strtotime($row['bdate']))) . '</span><br>';
			if ($month != date('n', strtotime($row['bdate'])))
				echo '<span class="classis-in-p"><small>' . $month_i[($month = date('n', strtotime($row['bdate']))) -1] . '</small></span>';
			if(intval($_SESSION["uid"]) > 0){
				echo $row['visible'] ? '' : '✖ ';
				echo '<a class="underline" href="/travel/' . $row['sname'] . '/">' . $row['name'] . '</a>';
				echo ' <a class="underline" href="/travel/' . $row['sname'] . '/edit/">✐</a>';
			}else
				echo $row['visible'] ? '<a class="underline" href="/travel/' . $row['sname'] . '/">' . $row['name'] . '</a></p>' : $row['name'];
			echo '</p>' . PHP_EOL;
		}
}