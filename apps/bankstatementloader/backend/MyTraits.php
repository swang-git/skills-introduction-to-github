<?php
namespace App\Traits;
// require_once 'vendor/autoload.php';
use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Log;
use App\Models\Track;

use App\Models\golf\LogPage;

trait MyTraits {
    protected function check_date_ddmmyy($d) {
        if (preg_match('@\d\d\/\d\d\/\d\d@',  $d)) {
          Log::info("date=$d\n");
          return true;
        } else {
        Log::info("not date=$d\n");
          return false;
        }
      }
    protected function check_money($d) {
        if ($d > 999999999) {
            Log::info("not money=$d\n");
            return false;
        }
        $d = $this->cleanMoney($d);
        if (preg_match('/^[-]?\d{1,}\.\d{1,}$/',  $d)) {
            Log::info("amnt=$d\n");
            return true;
        } else {
            Log::info("not money=$d\n");
            return false;
        }
    }
    protected function get_date_desc_amnt_from_line($s) {
        Log::debug("-fn-get_date_desc_amnt_from_line:[$s]", [__line__, __file__]);
        // Log::debug("==CK==line:[$s]", [__line__, __file__]);
        $x = explode(' ', $s);
        // Log::debug("==CK==x", $x);
        $date = array_shift($x);
        if (!$this->check_date_ddmmyy($date)) {
            return null;
        }
        $amnt = array_pop($x);
        if (!$this->check_money($amnt)) {
            array_push($x, $amnt);
            $amnt = null;
        }
        $desc = implode(' ', $x);
        Log::debug("date=$date desc=$desc amnt=$amnt", [__line__, __file__]);
        return [$date, $desc, $amnt];
    }
    public function testPrint() {
        Log::info('---testing');
        // $this->saveLog('artsHome');
    }
    private function getLast2Files($dirnm) {
        $lastMod = 0;
        $lastFile = null;
        $last2ndMod = 0;
        $last2ndFile = null;
        foreach (scandir($dirnm) as $entry) {
            if (is_file("$dirnm/$entry") && filemtime("$dirnm/$entry") > $lastMod) {
                $lastMod = filemtime("$dirnm/$entry");
                $lastFile = $entry;
            }
        }

        foreach (scandir($dirnm) as $entry) {
            if (is_file("$dirnm/$entry") && filemtime("$dirnm/$entry") > $last2ndMod && $entry != $lastFile) {
                $last2ndMod = filemtime("$dirnm/$entry");
                $last2ndFile = $entry;
            }
        }
        // xdiff_file_diff_binary($last2ndFile, $lastFile);
        return [$last2ndFile, $lastFile];
    }
    public function getLatestFile($dirnm) {
        $lastMod = 0;
        $lastModFile = '';
        foreach (scandir($dirnm) as $entry) {
            if (is_file("$dirnm/$entry") && filemtime("$dirnm/$entry") > $lastMod) {
                $lastMod = filemtime("$dirnm/$entry");
                $lastModFile = $entry;
            }
        }
        return $lastModFile;
    }
    private function now() {
		return Date('Y-m-d H:i:s', time());
	}
	private function logPage($params=null) {
		Log::info($_SERVER['REMOTE_ADDR'] . ' ' . $_SERVER['REQUEST_URI'] . ' ' . $this->now());
		$dx = new LogPage();
		$dx->ip_address = $_SERVER['REMOTE_ADDR'];
		$dx->page_name = preg_replace('/\/golf\//', '', $_SERVER['REQUEST_URI']);
		$dx->params = $params;
		$dx->save();
	}	
    public function saveLog ($page) {
        // Log::info(['FROM backend app/MyTrait $_SERVER["HTTP_USER_AGENT"]:', $_SERVER['HTTP_USER_AGENT']]);
        $myIP = '71.59.72.103';
        // This creates the Reader object, which should be reused across lookups.
        $reader = new Reader('/usr/share/GeoIP/GeoLite2-City.mmdb');
        // Replace "city" with the appropriate method for your database, e.g.,"country".
        $remoteIP = $_SERVER['REMOTE_ADDR'];
        // if (strncmp($remoteIP, "192.168.1.135", 12) == 0) $remoteIP = $myIP;
        if (strncmp($remoteIP, "192.168", 7) == 0) $remoteIP = $myIP;
        // Log::info($remoteIP);
        $d = null;
        try {
            $d = $reader->city($remoteIP);
        } catch(Exception $e) {
            Log::error($e->getMessage());
        }
        // $this->checkData($d);
        $httpUserAgent = $_SERVER['HTTP_USER_AGENT'];
		$host = $_SERVER['HTTP_HOST'];
		$dm = new Track;
		$dm['ip'] = $remoteIP;
		$dm['hostname'] = $host;
		$dm['page'] = $page;
		$dm['userAgent'] = $httpUserAgent;
		$dm['country'] = $d->country->name;
		$dm['countryCode'] = $d->country->isoCode;
		$dm['region'] = $d->mostSpecificSubdivision->isoCode;
		$dm['regionName'] = $d->mostSpecificSubdivision->name;
		$dm['city'] = $d->city->name;
		$dm['zip'] = $d->postal->code;
		$dm['latitude'] = $d->location->latitude;
		$dm['longitude'] = $d->location->longitude;
        // Log::info($dm);
        // $this->logInfo($dm);
		try {
            $dm->save();
            return "OK";
		} catch(Exception $e) {
            return $e->getMessage();
		}
    }
    private function logInfo($dm) {
        Log::info([
            'this log is form backend app/MyTrait $_SERVER["HTTP_USER_AGENT"]:', 
            $dm['userAgent']
        ]);
    }
    private function checkData($record) {
        Log::info($record->country->isoCode . "\n"); // 'US'
        Log::info($record->country->name . "\n"); // 'United States'
        Log::info($record->country->names['zh-CN'] . "\n"); // '美国'
        Log::info($record->mostSpecificSubdivision->name . "\n"); // 'Minnesota'
        Log::info($record->mostSpecificSubdivision->isoCode . "\n"); // 'MN'
        Log::info($record->city->name . "\n"); // 'Minneapolis'
        Log::info($record->postal->code . "\n"); // '55455'
        Log::info($record->location->latitude . "\n"); // 44.9733
        Log::info($record->location->longitude . "\n"); // -93.2323
    }
}
