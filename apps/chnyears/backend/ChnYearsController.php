<?php
namespace App\Http\Controllers;

// use App\Models\tv\Channel;
// use App\Models\tv\ChannelOldId;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class ChnYearsController extends Controller
{
  public function __construct() {
  // $this->middleware('auth');
  }

  public function getList($yr) {
    Log::info("-fn-getList($yr)");
    // $title = "公历\t农历\t生肖 对照表";
    // $tiangan = ['甲','乙','丙','丁','戊','己','庚','辛','壬','癸'];
    // $dizhi = ['子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥'];
    // $sxiao = ['鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪'];

    $jzyr = $this->getJiazi($yr);
    $yrlst = $this->getYearList($jzyr);
    return ['lst' => $yrlst, 'status' => "OK"];
  }

  private function getYearList($yr) {
    $title = "公历\t农历\t生肖 对照表";
    $tiang = ['甲','乙','丙','丁','戊','己','庚','辛','壬','癸'];
    $dizhi = ['子','丑','寅','卯','辰','巳','午','未','申','酉','戌','亥'];
    $sxiao = ['鼠','牛','虎','兔','龙','蛇','马','羊','猴','鸡','狗','猪'];
    $lst = [];
    $d = 0;
    $s = 0;
    $year = (int)$yr;
    for ($i=0; $i<60; $i++) {
    // for ($i=0; $i<12; $i++) {
      $jzyr = $tiang[$i%10] . $dizhi[$d%12];
      $shng = $sxiao[$s%12];
      array_push($lst, [$year, $jzyr, $shng]); 
      $d++; $s++; $year++;
    }
    return $lst;
  }
  private function getJiazi($yr) {
    if ($yr == 1924) return $yr;
    for ($i=0; ;$i++) {
      if ($yr < 1924) {
        $a = 1924 - ($i + 1) * 60;
        $b = 1924 - $i * 60;
        if ($a <= $yr and $yr < $b) return $a;
      } else if ($yr > 1924) {
        $a = 1924 + $i * 60;
        $b = 1924 + ($i + 1) * 60;
        if ($a <= $yr and $yr < $b) return $a;
      } else return 1924;
    }
  }
}