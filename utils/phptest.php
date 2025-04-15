#!/usr/bin/php
<?php
// use \\sites\\devx\\vendor\\laravel\\framework\\src\\Illuminate\\Support\\Facades\\Log;
// use \sites\devx\App\Traits\MyTraits;
function check_date_ddmmyy($d) {
  if (preg_match('@\d\d\/\d\d\/\d\d@',  $d)) {
    // print("date=$d\n");
    return true;
  } else {
    print("not date=$d\n");
    return false;
  }
}
function check_money($d) {
  $d = 1881130153;
  if (preg_match('/^[-]?\d{1,}\\.\d{1,2}$/',  $d)) {
    print("amnt=$d\n");
    return true;
  } else {
    print("not money=$d\n");
    return false;
  }
}
function get_date_desc_amnt_from_line($s) {
  print("-fn-get_date_desc_amnt_from_line $__line__, $__file__");
  $x = explode(' ', $s);
  // print_r($x);
  $date = array_shift($x);
  if (!check_date_ddmmyy($date)) {
    return null;
  }
  $amnt = array_pop($x);
  if (!check_money($amnt)) {
    array_push($x, $amnt);
    $amnt = null;
  }
  $desc = implode(' ', $x);
  print("$date, $desc, $amnt\n");
  return [$date, $desc, $amnt];


  if (preg_match('@\d\d\/\d\d\/\d\d@',  $x[0])) {
    $date = $x[0];
    print("date=$date\n");
  } else {
    print("not date=$x\n");
    exit(1);
  }
  $amnt = $x[count($x) - 1];
  $amnt = +101.30;
  print("amnt=$amnt\n");
  if (preg_match('@[-]?\d{1,}\.\d{1,}@',  $amnt)) {
    print("correct amnt=$amnt\n");
  } else {
    print("not amnt=$amnt\n");
    exit(2);
  }
}
$s = '07/01/24 Zelle payment to Ray Xue Tang for "RBW green fee.Thank you,Shengli Wang"; Conf# m38uyq6zm -50.00';
print("$s\n");
get_date_desc_amnt_from_line($s);
exit(0);

$current_date_time = date('Y-m-d H:i');
$newTime = date("Y-m-d H:i",strtotime("+15 minutes", strtotime($current_date_time)));
$newsub = substr($newTime, 0, 10);
print("$current_date_time $newTime $newsub\n");
exit(0);
// print("php testing\n");
// $t1 = '2020-08-11 12:44:11';
// $t2 = '2019-08-12 23:45:12';
// $mx = max(array($t1, $t2));
// print("$mx\n");
$dt1 = new DateTime('2014-05-07 18:53:22', new DateTimeZone('America/New_York'));
$dt2 = new DateTime('2014-05-07 16:53:44', new DateTimeZone('America/New_York'));
echo max($dt1,$dt2)->format(DateTime::RFC3339) . PHP_EOL; // 2014-05-07T16:53:00+00:00
echo min($dt1,$dt2)->format(DateTime::RFC3339) . PHP_EOL; // 2014-05-07T18:53:00+03:00
echo min($dt1,$dt2)->format('Y-m-d G:i:s') . PHP_EOL; // 2014-05-07T18:53:00+03:00
// echo Date(max($dt1, $dt2), 'Y-m-d G:i:s');

$dt1 = "2014-05-07 18:53:22";
list($y1,$m1,$d1,$h1,$i1,$s1) = explode("-", strtr(strtr($dt1, " ", "-"), ":", "-"));

echo 'type of $y1 is ';
echo gettype($y1);
echo "\n";

$xx = explode("-", strtr(strtr($dt1, " ", "-"), ":", "-"));
$x = explode("-", strtr(strtr($dt1, " ", "-"), ":", "-"));
$y = array($y1,$m1,$d1,$h1,$i1,$s1);
echo "$y1,$m1,$d1,$h1,$i1,$s1\n";

echo "===================\n";

$today = date("Y-m-d H:i:s");
echo "$today\n";
$xoday = date("Y-m-d H:i:s", strtotime($dt1));
echo "$xoday\n";
echo strtotime($dt1);
echo "\n";

//print_r($x);
//print_r($y);
//print_r($xx);
//array($a1,$m1,$d1,$h1,$i1,$s1) = $xx;
//print($a1);

echo time();
echo "\n";
echo time() - strtotime("2020-11-30 11:50:00");
echo "\n";
echo date("Y-m-d H:i:s", time());
echo "\n";

