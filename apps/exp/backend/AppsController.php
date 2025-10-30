<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppsController extends Controller {
  public function __construct() {
          // $this->middleware('auth');
  }
  /**
    * [index description]
    * @method index
    * author: swang
    * created at 2021-05-07T12:27:40-040
    * revised at 2021-05-07T12:30:40-040
    * version [version]
    * @return [type] [description]
    */
  public function index() {
          // $this->middleware('auth');
          // return view('fin');
  }
  public function getAttached($docDir) {
    $ddir = "/sites/webdata/docs/" . $docDir;
    $files = [];
    if (is_dir($ddir)) {
      if ($dh = opendir($ddir)) {
        while (($file = readdir($dh)) !== false) {
          // Log:info("filename: $file");
          if ($file != "." and $file != "..") array_push($files, $file);
        }
        closedir($dh);
      }
    } else {
      return ['status' => "FAIL", 'errmsg' => $docDir . " is not a dir"];
    }
    return ['files' => $files, 'status' => "OK"];
  }
  public function loginAdmin(Request $request) { Log::info("loginAdmin from frontend loginAdmin");
    $input = $request->all();
    Log::info("login", $input);
    if (auth()->attempt(array('username' => $input['username'], 'password' => $input['password']))) {
        return ['user' => auth()->user(), 'status' => "OK"];
    }
  }
  // public function loginAdmin(Request $request) {
  //   $input = $request->all();
  //   Log::info("loginAmin", $input);
  //   $this->validate($request, [
  //       // 'email' => 'required|email',
  //       'email' => 'required|email',
  //       'password' => 'required',
  //   ]);

  //   if (auth()->attempt(array('email' => $input['email'], 'password' => $input['password']))) {
  //       return ['user' => auth()->user(), 'status' => "OK"];
  //   }
  // }
}