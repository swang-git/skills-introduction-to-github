<?php namespace App\Traits;
// require_once 'vendor/autoload.php';
use GeoIp2\Database\Reader;
use Illuminate\Support\Facades\Log;
use App\Models\Track;

trait MyTraits {
    public function testPrint() {
        Log::info('---testing');
        // $this->saveLog('artsHome');
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