<?php
namespace App\Http\Controllers;
use App\Models\watcher\HealthRecord;
use App\Models\watcher\StockPrice;
use App\Models\watcher\Portfolio;
use App\Models\watcher\PortfolioNote;
use App\Models\watcher\PortfolioAccount;
use App\Models\watcher\PortfolioStock;
use App\Models\watcher\PortfolioAction;
use App\Models\watcher\FidelityPosition;
use App\Models\watcher\FidelityPositionView;
use App\Models\expense\Spend;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
// use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class WatcherController extends Controller {
    public function __construct() {
        // $this->middleware('auth');
    }
    public function index() {
        return view('welcome');
    }
    public function getPositions($date) { Log::info("WatcherController/getpositions $date");
        $x = explode('-', $date);
        $ccSta3 = $x[0].'-'.$x[1].'-1';
        $ccEnd3 = $x[0].'-'.($x[1]+1).'-3'; Log::info("WatcherController/getpositions $date start=$ccSta3 end=$ccEnd3");
        $ccBalance = Spend::where([['status', 'A'], ['cat_id', 15], ['purchasedon', '>', $ccSta3], ['purchasedon', '<=', $ccEnd3]])->orderByDesc('purchasedon')->limit(1)->value('unitprice');
        $ccDueDate = Spend::where([['status', 'A'], ['cat_id', 15], ['purchasedon', '>', $ccSta3], ['purchasedon', '<=', $ccEnd3]])->max('purchasedon');
        $today_sec_cnt = FidelityPosition::where('date', $date)->count('*');
        Log::info("today security count for $date: $today_sec_cnt");
        if ($today_sec_cnt == 0) return $this->loadPositions($date);
        $pos = DB::select("CALL get_positions(?)", [$date]); // union data from stock_quotes
        return ['positions' => $pos, 'ccBalance' => $ccBalance, 'ccDueDate' => $ccDueDate, 'status' => 'OK'];
    }
    // public function getPositions($date) { Log::info("WatcherController/getpositions $date");
    //     // $pos = FidelityPositionView::where('date', $date)->select('*')->get();
    //     // $pos = DB::select("CALL get_fidelity_positions(?)", [$date]);
    //     $pos = DB::select("CALL get_positions(?)", [$date]);
    //     $numSecurities = count($pos);
    //     $preDate = FidelityPosition::where('date', '<', $date)->max('date');
    //     $ownSecurities = FidelityPosition::where('date', $preDate)->count('*');
    //     Log::info("Number of securities=$numSecurities, preNumSecurities=$ownSecurities, preDate=$preDate");
    //     if ($numSecurities > 0 and $numSecurities != $ownSecurities) {
    //         return ['positions' => $pos, 'currSecurities' => $numSecurities, 'preNumSecurities' => $ownSecurities, 'preDate' => $preDate, 'status' => 'misMatch'];
    //     } else if ($numSecurities == $ownSecurities) {
    //         // $today = Date('Y-m-d', time());
    //         $x = explode('-', $date);
    //         $ccSta3 = $x[0].'-'.$x[1].'-1';
    //         $ccEnd3 = $x[0].'-'.($x[1]+1).'-3'; Log::info("WatcherController/getpositions $date start=$ccSta3 end=$ccEnd3");
    //         $ccBalance = Spend::where([['status', 'A'], ['cat_id', 15], ['purchasedon', '>', $ccSta3], ['purchasedon', '<=', $ccEnd3]])->orderByDesc('purchasedon')->limit(1)->value('unitprice');
    //         $ccDueDate = Spend::where([['status', 'A'], ['cat_id', 15], ['purchasedon', '>', $ccSta3], ['purchasedon', '<=', $ccEnd3]])->max('purchasedon');
    //         return ['positions' => $pos, 'ccBalance' => $ccBalance, 'ccDueDate' => $ccDueDate, 'status' => 'OK'];
    //     } else {
    //         Log::info("load fidelity position for date=$dae");
    //         return $this->loadPositions($date);
    //     }
    // }
    public function loadPositions($date) { Log::info("WatcherController/Loading positions $date");
        $yyyymmdd = str_replace("-", "", $date);
        // $fname = "/sites/webdata/docs/Portfolio/snapshot_$yyyymmdd.csv";
        $fname = config('constants.DOC_DIR') . "/Portfolio/snapshot_$yyyymmdd.csv";
        if (!file_exists($fname)) {
            Log::info("$fname not exists");
            return [ 'status' => "${fname} not exists" ];
        }
        Log::info("WatcherController/Loading positions $fname");
        $lines = file($fname, FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);
        // $numSecurities = count($lines);
        // Log::info("Number of securities: $numSecurities");
        foreach ($lines as $line) {
            if (preg_match('/^[Z|X]\d{7}/', $line) or preg_match('/^\d{8}/', $line)) {
                // Log::info($line);
                $x = explode(',', $line);
                $acct_num = $x[0];
                // Log::info("acct_num: $acct_num");
                $acct_name = substr($x[1], 0, 16);
                // Log::info("acct_name: $acct_name");
                $symbol = $x[2];
                // Log::info("symbol: $symbol");
                if ($symbol == 'Pending Activity') continue;
                else if ($symbol == '928563402') $symbol = 'VNW';
                $company = $x[3];
                // Log::info("company: $company");
                $quantity = preg_replace('/\$/', '', $x[4]);
                // Log::info("quantity: $quantity");
                $price = preg_replace('/\$/', '', $x[5]);
                // Log::info("price: $price");
                $pchange = preg_replace('/\$|\+/', '', $x[6]);
                // Log::info("pchange: $pchange");
                $current_val = preg_replace('/\$/', '', $x[7]);
                // Log::info("current_val: $current_val");
                $today_gl = preg_replace('/\$|\+/', '', $x[8]);
                // Log::info("today_gl: $today_gl");
                $today_gl_p = preg_replace('/%|\+/', '', $x[9]);
                // Log::info("today_gl_p: $today_gl_p");
                $total_gl = preg_replace('/\$|\+/', '', $x[10]);
                // Log::info("total_gl: $total_gl");
                $total_gl_p = preg_replace('/%|\+/', '', $x[11]);
                // Log::info("total_gl_p: $total_gl_p");
                $pct_of_acct = preg_replace('/%/', '', $x[12]);
                // Log::info("pct_of_acct: $pct_of_acct");
                $cost_per_share = preg_replace('/\$/', '', $x[14]);
                // Log::info("cost_per_share: $cost_per_share");
                $cost_base = preg_replace('/\$/', '', $x[13]);
                // Log::info("cost_base: $cost_base");
                if (str_contains($symbol, 'XX') or str_contains($symbol, 'CORE')) {
                    if ($price == '') $price = 1.0;
                    if ($pchange == '') $pchange = 0.0;
                    if ($quantity == '') $quantity = $current_val;
                }

                $posData= ['date' => $date, 'acct_num' => $acct_num, 'acct_name' => $acct_name, 'symbol' => $symbol,
                    'company' => $company, 'price' => $price, 'pchange' => $pchange,
                    'today_gl' => $today_gl, 'today_gl_p' => $today_gl_p, 'total_gl' => $total_gl, 'total_gl_p' => $total_gl_p,
                    'current_val' => $current_val, 'pct_of_acct' => $pct_of_acct, 'quantity' => $quantity,
                    'cost_basis_per_share' => $cost_per_share, 'cost_basis' => $cost_base];
                // Log::info($posData);
                $upsertReturn = FidelityPosition::upsert($posData, ['date', 'acct_num', 'symbol'], ['price', 'pchange', 'quantity']);
                // $dm = new FidelityPosition;
                // $dm->date = $date;
                // $dm->acct_num = $acct_num;
                // $dm->acct_name = $acct_name;
                // $dm->symbol = $symbol;
                // $dm->company = $company;
                // $dm->price = $price;
                // $dm->pchange = $pchange;
                // $dm->quantity = $quantity;
                // $dm->today_gl = $today_gl;
                // $dm->today_gl_p = $today_gl_p;
                // $dm->total_gl = $total_gl;
                // $dm->total_gl_p = $total_gl_p;
                // $dm->current_val = $current_val;
                // $dm->pct_of_acct = $pct_of_acct;
                // $dm->cost_basis_per_share = $cost_per_share;
                // $dm->cost_basis = $cost_base;
                // $dm->save();
                Log::info("upsertReturn = $upsertReturn of $date for $acct_num $symbol");
            }
        }
        $pos = FidelityPosition::where([['status', 'A'], ['date', $date]])->select('*')->get();
        // $latestCreditCardBalance = Spend::where([['status', 'A'], ['cat_id', 15]])->orderByDesc('purchasedon')->limit(1)->value('unitprice');
        if (count($pos) > 0) return ['positions' => $pos,  'status' => 'OK'];
        else return ['status' => "no position for $date"];
    }
    public function getStockPriceList($date=NULL) {
        $dats = StockPrice::where([ ['status', 'A'], ['sdatetime', 'LIKE', '%09:30%'] ])
            ->orWhere([ ['status', 'A'], ['sdatetime', 'LIKE', '%11:30%'] ])
            ->orWhere([ ['status', 'A'], ['sdatetime', 'LIKE', '%18:30%'] ])
            ->select('id', 'sdatetime as date','price', 'pchange', 'plow', 'phigh', 'symbol')
            ->orderBy('date', 'desc')->take(10000)->get(); // Log::info($dats);
        return [ 'dats' => $dats ];
    }
    public function getPortfolio($date) {
        $pdata = Portfolio::where('status', 'A')->whereDate('asof_time', $date)
            ->select('id', 'asof_time', 'account', 'symbol', 'price', 'change', 'today_gain_loss', 'today_gl_pct',
            'total_gain_loss', 'total_gl_pct', 'current_value', 'quantity', 'share_cost', 'total_cost',
            'low_52_week', 'high_52_week')->get(); //->orderBy('id', 'asc')->get();
        $pnote = PortfolioNote::where([['status', 'A'], ['date', $date]])
            ->select('id', 'date', 'user_id', 'faccount', 'symbol', 'action', 'taccount', 'price', 'share', 'note')->get();
        Log::info('getPortfolio for date:', [$date, count($pdata), count($pnote)]);
        return [ 'portf' => $pdata, 'pnote' => $pnote, 'status' => "OK" ];
    }
    public function getList() { Log::info('watcher->getList()');
        $user = Auth::user();
        $dats = HealthRecord::where([ ['health_records.status', 'A'], ['user_id', $user->id] ])
            // ->leftjoin('portfolio_notes', 'health_records.date', '=', 'portfolio_notes.date')
            ->select('health_records.id','user_id','health_records.date','weight as kilo','weight','portfolio','dow_jones as dowjones','nasdaq','sp500','ftse100','nikkei','note', )->orderBy('date', 'desc')->get();
        // $healthRecords = Collect($dats);
        $accnts = PortfolioAccount::where([ ['status', 'A'], ['user_id', $user->id] ])->select('id', 'accnt_num', 'accnt_nam')->orderby('id')->get();
        $stocks = PortfolioStock::where([ ['status', 'A'], ['user_id', $user->id] ])->select('id as value', 'symbol as label')->orderby('id')->get();
        $actions = PortfolioAction::where([ ['status', 'A'], ['user_id', $user->id] ])->select('id as value', 'type as label')->orderby('id')->get();

        return [ 'dats' => $dats, 'accnts' => $accnts, 'stocks' => $stocks, 'actions' => $actions, 'status' => "OK" ];
    }
    public function addPNote(Request $dx) {
        foreach($dx->toArray() as $d) {
            $dm = new PortfolioNote($d); Log::info('addPNote', $dm->toArray());
            $dm->save();
        }
        return [ 'status' => "OK" ];
    }
    public function updPNote(Request $dx) {
        $da = $dx->toArray();
        $date = $da[0]['date'];
        $affectedRows = PortfolioNote::where('date', $date)->delete();
        foreach($da as $d) {
            $dm = new PortfolioNote($d); Log::info('updPNote', $dm->toArray());
            $dm->save();
        }
        return [ 'status' => "OK" ];
    }
    public function delPNote(Request $d) { Log::info('delete PNote');
        $affectedRows = PortfolioNote::where('date', $d->date)->delete();
        return [ 'status' => "OK" ];
    }
    // private function pipe_streams($in, $out) {
    //     $size = 0;
    //     while (!feof($in)) $size += fwrite($out, fread($in, 8192));
    //     return $size;
    // }
    // private function mvPositionFiles() { Log::info('mvPositionFiles');
    //     $yyyymmdd = date('Ymd');
    //     $infile = "/home/swang/Documents/dailydownload/snapshot_$yyyymmdd.pdf";
    //     $outfile = "/sites/webdata/docs/Portfolio/snapshot_$yyyymmdd.pdf";
    //     $lines = file($infile, FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);
    //     $out = fopen($outfile, 'w');
    //     foreach($lines as $line) fwrite($out, $line);
    //     // $sz = $this->pipe_streams($in, $out);
    //     // Log::info("mvPositionFiles sz=$sz, infile=$infile outfile=$outfile");
    //     // fclose($in);
    //     fclose($out);
    // }
    // private function XXXmvPositionFiles() { Log::info('mvPositionFiles');
    //     // $outcome = shell_exec('/home/swang/bin/mvPositionFiles');
    //     // $outcome = shell_exec('/sites/projects/utils/mvPositionFile.sh');
    //     // $outcome = shell_exec('/sites/devx/public/phpinfo.php');
    //     $outcome = shell_exec('/sites/devx/public/mvPositionFile.sh');
    //     Log::info("shell_exec mvPositionFiles outcome=$outcome");
    //     // system('/sites/projects/utils/mvPositionFile.sh', $retval);
    //     // $source = "/home/swang/Documents/dailydownload/snapshot_20230331.pdf";
    //     // $destination = "/sites/webdata/docs/Portfolio/lll.pdf";
    //     // exec("/bin/cp $source $destination" , $outcome, $retval);
    //     // exec('/sites/devx/public/mvPositionFile.sh', $outcome, $retval);
    //     // exec('/sites/devx/public/phpinfo.php', $outcome, $retval);
    //     // Log::info("shell_exec mvPositionFiles retval=$retval", $outcome);
    //     // $outcome = shell_exec("/bin/cp $source $destination");
    //     // if (!copy($source, $destination)) Log::error("$source cannot be copied to $destination");
    //     // else Log::info("$source been copied to $destination");
    // }
    public function add(Request $d) {
        $dm = new HealthRecord();
        $dm->date = $d['date'];
        $dm->weight = $d['kilo'];
        $dm->portfolio = $d['portfolio'];
        $dm->dow_jones = $d['dowjones'];
        $dm->sp500 = $d['sp500'];
        $dm->nasdaq = $d['nasdaq'];
        $dm->ftse100 = $d['ftse100'];
        $dm->nikkei = $d['nikkei'];
        $dm->note = $d['note'];
        $dm->user_id = $d['user_id'];
        $dm->save();
        // $this->mvPositionFiles();
        return [ 'status' => "OK", 'da' => $dm ];
    }
    public function upd(Request $d) { Log::info("Updating Watcher data", $d->toArray());
        $dm = HealthRecord::find($d['id']);
        $dm->date = $d['date'];
        $dm->weight = $d['kilo'];
        $dm->portfolio = $d['portfolio'];
        $dm->dow_jones = $d['dowjones'];
        $dm->sp500 = $d['sp500'];
        $dm->nasdaq = $d['nasdaq'];
        $dm->ftse100 = $d['ftse100'];
        $dm->nikkei = $d['nikkei'];
        $dm->note = $d['note'];
        $dm->user_id = $d['user_id'];
        $dm->save();
        // $this->mvPositionFiles();
        return [ 'status' => "OK", 'da' => $dm ];
    }
    public function del(Request $d) { Log::info('delete healthrecord');
        $Id = $d['id'];
        $dm = HealthRecord::find($Id);
        $dm->status = 'D';
        $dm->save();
        return [ 'status' => 'OK', 'da' => $dm ];
    }
    public function XXXsaveRecord(Request $d) {
        $userId = Auth::user()->id;
        $Id = $d['id'];
        if ($d['act'] == 'del') {
            $dm = HealthRecord::find($Id);
            $dm->status = 'D';
            $dm->save();
            return [ 'status' => 'OK', 'da' => $dm ];
        } else if ($Id > 0) {
            $dm = HealthRecord::find($Id);
            $dm->date = $d['date'];
            $dm->weight = $d['kilo'];
            $dm->portfolio = $d['portfolio'];
            $dm->user_id = $userId;
            $dm->save();
        } else {
            $dm = new HealthRecord();
            $dm->date = $d['date'];
            $dm->weight = $d['kilo'];
            $dm->portfolio = $d['portfolio'];
            $dm->user_id = $userId;
            $dm->save();
        }
        return [ 'status' => "OK", 'da' => $dm ];
    }
    public function saveWeight(Request $d) {
        $userId = Auth::user()->id;                     //dd($userId);
        // $today = Date('Y-m-d', time());
        // $d = input::All();
        $id = $d['id'];
        if ($id > 0) {
            $da = HealthRecord::find($id);
            $da->date = $d['date'];
            $da->weight = $d['kilo'];
            $da->portfolio = $d['portfolio'];
            $da->user_id = $userId;
            $da->save();
        } else {
            $da = new HealthRecord();
            $da->date = $d['date'];
            $da->weight = $d['kilo'];
            $da->portfolio = $d['portfolio'];
            $da->user_id = $userId;
            $da->save();
        }
        // $check = HealthRecord::where([ ['status', 'A'], ['user_id', $userId], ['date', $date] ])->select('date')->get();
        // if (count($check) <= 0) {   // weight not stored for the date yet, store it
        //     $dm = new HealthRecord;
        //     $dm->user_id = $userId;
        //     $dm->date = $date;
        //     $dm->weight = $kilo;
        //     $dm->save();
        // }
        // return $this->get_health_record();
        // return view('Calc.converter', compact('healthRecords'));
    }
    private function get_health_record() {
        $user = Auth::user();
        // dd($user);
        if (empty($user)) $user = Auth::loginUsingId(1);
        $dats = new HealthRecord;
        $dats = HealthRecord::where([ ['status', 'A'], ['user_id', $user->id] ])
        ->select('date as 日期','weight as 公斤')->orderBy('date', 'desc')->get();
        // dd($dats);
        foreach($dats as $d) {
            $d['市斤'] = $d['公斤'] * 2.0;
            $d['英磅'] = sprintf('%5.2f', $d['公斤'] / 0.45359290943564);
        }                                                                   //dd($dats);
        $healthRecords = Collect($dats);
        return view('Calc.converter', compact('healthRecords'));
    }
    public function get_measurement_name_list() {
        $dats = Measurement::select('name')->get();             //dd($dats);
        return $dats;
    }
    public function get_measurement_unit_list($measurementName): array {
        $measureName = preg_replace('@ @', '', $measurementName);
        $table_name = strtolower($measureName) . 'es';
        match($measureName) {
            'Mass' => $table_name = 'masses',
            'Length' => $table_name = 'lengths',
            'FuelEconomy' => $table_name = 'fuel_economies',
            'Volume' => $table_name = 'volumes',
            'Area' => $table_name = 'areas',
            'Temperature' => $table_name = 'temperatures',
            default => throw new Exception (message: "Unknown measurement $measureName"),
        };
        $dats = DB::table('information_schema.columns')->select('column_name')->where('table_name', $table_name)->get();
        return $dats;
    }
    // public function get_measurement_unit_list($measurementName) {
    //     $measurementName = preg_replace('@ @', '', $measurementName);
    //     $table_name = strtolower($measurementName) . 'es';
    //     switch($measurementName) {
    //         case 'Mass':
    //         $table_name = 'masses';
    //         break;
    //         case 'Length':
    //         $table_name = 'lengths';
    //         break;
    //         case 'FuelEconomy':
    //         $table_name = 'fuel_economies';
    //         break;
    //         case 'Volume':
    //         $table_name = 'volumes';
    //         break;
    //         case 'Area':
    //         $table_name = 'areas';
    //         break;
    //         case 'Temperature':
    //         $table_name = 'temperatures';
    //         break;
    //         default:
    //         echo "Unknown measurement $measurementName <br />";
    //         break;
    //     }
    //     $dats = DB::table('information_schema.columns')->select('column_name')->where('table_name', $table_name)->get();
    //     return $dats;
    // }
    public function get_conversion($measurementName, $inputNum, $convFrom, $convTo) {
        $measurementName = preg_replace('@ @', '', $measurementName);
        if ($measurementName == 'Temperature') return $this->convert_temperature($inputNum, $convFrom, $convTo);
        $convFrom = preg_replace('/ /', '_', strtolower($convFrom));
        $convTo   = preg_replace('/ /', '_', strtolower($convTo));
        switch($measurementName) {
            case 'Temperature':
            $factors = Temperature::first();
            break;
            case 'Area':
            $factors = Area::first();
            break;
            case 'Volume':
            $factors = Volume::first();
            break;
            case 'FuelEconomy':
            $factors = FuelEconomy::first();
            break;
            case 'Length':
            $factors = Length::first();
            break;
            case 'Mass':
            $factors = Mass::first();
            break;
            default:
            echo "Unknown Model $measurementName <br />";
            break;
            return null;
        }
        $fromfac = $factors[$convFrom];
        $tofac = $factors[$convTo];
        $fac = $tofac / $fromfac;
        $toVal = $inputNum * $fac;
        // $toVal = $inputNum * 0.45359237;
        $dats = collect([ 'value' =>$toVal ]);
        return $dats;
    }
    function  convert_temperature($inputNum, $convFrom, $convTo) {
        $from_to = $convFrom . "_" . $convTo;
        $toVal = 0;
        if ($convFrom == $convTo) return $dats = collect([ 'value' => $inputNum ]);
        switch($from_to) {
            case 'Fahrenheit_Celsius':
            $toVal = ($inputNum - 32.0) * 5.0 / 9.0;
            break;
            case 'Celsius_Fahrenheit':
            $toVal = $inputNum * 9.0 / 5.0 + 32.0;
            break;
            case 'Celsius_Kelvin':
            $toVal = $inputNum + 273.15;
            break;
            case 'Kelvin_Celsius':
            $toVal = $inputNum - 273.15;
            break;
            case 'Kelvin_Fahrenheit':
            $toVal = ($inputNum - 273.15) * 9.0 /5.0 + 32.0;
            break;
            case 'Fahrenheit_Kelvin':
            $toVal = ($inputNum - 32.0) * 5.0 /9.0 + 273.15;
            break;
            default:
            $toVal = 0;
            break;
        }
        return $dats = collect([ 'value' =>$toVal ]);
    }
}
