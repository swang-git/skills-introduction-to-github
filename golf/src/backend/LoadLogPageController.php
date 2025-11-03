<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\golf\LogPage;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;


\Config::set('database.default', 'golf_dev');
class LoadLogPageController extends Controller {
	public function __construct() {
		// Log::info('$_SERVER["REMOTE_ADDR"]=' . $_SERVER['REMOTE_ADDR'] . ' $_SERVER["REQUEST_URI"]=' . $_SERVER['REQUEST_URI']);
	}
	public function index() { 
	// public function __invoke() { // Log::info("__invoke get called");
    $lst = LogPage::select('datetime', 'ip_address', 'page_name', 'params')->orderBy('datetime', 'desc')->get();
    return [ 'status' => "OK", 'lst' => $lst ];
  }
}
