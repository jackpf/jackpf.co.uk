<?php
include $_SERVER['DOCUMENT_ROOT'].'/config/lib.php';

$unix = (empty($_GET['month'])) ? time() : strtotime('1 '.$_GET['month'].' '.date('Y', time()));

$date = array(
'year'      => date('Y', $unix),
'year_leap' => (bool) date('L', $unix),
'month'     => date('n', $unix),
'month2'    => date('F', $unix),
'day'       => date('j', $unix)
);

function leap_year()
{
	global $date;
	
	return (($date['year'] % 4 == 0 && ($date['year'] % 100 != 0 || $date['year'] % 400 == 0)) || $date['year_leap']) ? true : false;
}
function month_start()
{
	global $date, $days;
	
	$day = date('D', strtotime('1 '.$date['month2'].' '.$date['year']));
	
	foreach($days as $key => $value)
	{
		if(stristr($value, $day))
		{
			return $key;
		}
	}
}
function get_time_range($month, $day = null)
{
	global $date, $months;
	
	return ($day !== null) ?
	"`Unix` BETWEEN '".strtotime($day.' '.$month.' '.$date['year'])."' AND '".strtotime(($day + 1).' '.$month.' '.$date['year'])."'" :
	"`Unix` BETWEEN '".strtotime('1 '.$month.' '.$date['year'])."' AND '".strtotime($months[$date['month']][$date['month2']].' '.$month.' '.$date['year'])."'";
}

$months = array(
1  => array('January'   => 31),
2  => array('February'  => (leap_year()) ? 29 : 28),
3  => array('March'     => 31),
4  => array('April'     => 30),
5  => array('May'       => 31),
6  => array('June'      => 30),
7  => array('July'      => 31),
8  => array('August'    => 31),
9  => array('September' => 30),
10 => array('October'   => 31),
11 => array('November'  => 30),
12 => array('December'  => 31)
);

$days = array(
1 => 'Monday',
2 => 'Tuesday',
3 => 'Wednesday',
4 => 'Thursday',
5 => 'Friday',
6 => 'Saturday',
7 => 'Sunday'
);

$db = new connection;

$sql = $db->query("SELECT `Unix` FROM `{$db->tb->Blog}` WHERE ".get_time_range($date['month2'])." AND `Type`='entry' AND `Status`='visible';");

$entries = array();

while($fetch = $db->fetch_array($sql))
{
	$entries[] = date('j', $fetch['Unix']);
}

echo '<style type="text/css">
	/*<![CDATA[*/
		table.calendar td, table.calendar th
		{
			border: 1px solid gray;
			text-align: center;
			width: 20px;
		}
		th div
		{
			float: left;
			width: 33%;
		}
		th div.c_h_left, th div.c_h_right
		{
			font-weight: normal;
			font-size: 0.9em;
		}
		th div.c_h_left
		{
			text-align: left;
		}
		th div.c_h_center
		{
			text-align: center;
		}
		th div.c_h_right
		{
			text-align: right;
		}
		div.c_e
		{
			border: 1px solid purple;
			position: relative;
		}
	/*]]>*/
</style>
<table class="calendar">
	<thead>
		<tr>
			<th colspan="100%">
				<div class="c_h_left">'.((reset(array_keys($months[$date['month'] - 1])) != null) ? '<a href="#" onclick="AJAX(null, document.getElementsByClassName(\'calendar\')[0].parentNode, \''.val::encode($_SERVER['PHP_SELF']).'?month='.reset(array_keys($months[$date['month'] - 1])).'\', false);">&laquo; '.val::str_trim(reset(array_keys($months[$date['month'] - 1])), 4, null).'</a>' : '&nbsp;').'</div>
				<div class="c_h_center">'.$date['month2'].'</div>
				<div class="c_h_right">'.((reset(array_keys($months[$date['month'] + 1])) != null) ? '<a href="#" onclick="AJAX(null, document.getElementsByClassName(\'calendar\')[0].parentNode, \''.val::encode($_SERVER['PHP_SELF']).'?month='.reset(array_keys($months[$date['month'] + 1])).'\', false);">'.val::str_trim(reset(array_keys($months[$date['month'] + 1])), 4, null).' &raquo;</a>' : '&nbsp;').'</div>
			</th>
		</tr>
		<tr>';
			for($i = 1; $i <= count($days); $i++)
			{
				echo '<th>'.val::str_trim($days[$i], 1, null).'</td>';
			}
		echo '</tr>
	</thead>
	<tbody>
		<tr>';
			$date['month3'] = reset($months[$date['month'] - ((isset($months[$date['month'] - 1])) ? 1 : -12)]);
			for($ii = $date['month3'] - (month_start() - 1), $pre = 0; $ii < $date['month3']; $ii++, $pre++)
			{
				echo '<td><a style="color: gray;">'.$ii.'</a></td>';
			}
			for($i = 1, $ii = $pre + 1; $i <= $months[$date['month']][$date['month2']]; $i++, $ii++)
			{
				echo '<td style="'.(($i == date('j') && $date['month'] == date('n')) ? 'border: 1px solid red;' : null).((in_array($i, $entries)) ? 'border: 1px solid green;' : null).'"><a href="#" onclick="AJAX(null, document.getElementsByClassName(\'calendar\')[0].parentNode, \''.val::encode($_SERVER['PHP_SELF']).'?month='.reset(array_keys($months[$date['month']])).'&day='.$i.'\', false);">'.$i.'</td>'.(($ii % 7 == 0) ? '</tr><tr>' : null);
			}
			if(strstr((string) $i / 7, '.'))
			{
				for($iii = 1; $ii < ceil($ii / 7) * 7; $ii++, $iii++)
				{
					echo '<td><a style="color: gray;">'.$iii.'</a></td>';
				}
				echo '<td><a style="color: gray;">'.$iii .'</a></td>';
			}
		echo '</tr>
	</tbody>
</table>';

if(isset($_GET['day']))
{
	$month = reset(array_keys($months[$date['month']]));
	$day = val::encode($_GET['day']);
	
	$sql = $db->query("SELECT `ID`, `Subject` FROM `{$db->tb->Blog}` WHERE ".get_time_range($month, $day)." AND `Type`='entry' AND `Status`='visible';");
	
	echo '<div class="c_e">';
		if($db->count_rows($sql) > 0)
		{
			while($fetch = $db->fetch_array($sql))
			{
				echo '<a href="/blog/'.$fetch['ID'].'">'.$fetch['Subject'].'</a><br />';
			}
		}
		else
		{
			echo 'There are no entries to be found.';
		}
		
		echo '<div style="position: absolute; top: 0; right: 0;">
			<a style="border: 1px solid red; color: red;" href="#" onclick="AJAX(null, this.parentNode.parentNode.parentNode, \''.val::encode($_SERVER['PHP_SELF']).'?month='.$month.'\', false);">X</a>
		</div>
	</div>';
}
?>