<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;
// use Illuminate\Http\Request;

class UserController extends Controller {
  public function __construct(
    public readonly UserService $userService
  ) {}

  public function store(CreateUserRequest $request) {  Log::debug("store user", $request->toArray());
    $result = ['status' => 200];  // C-CRUD -- Create new user
    $result['data'] = $this->userService->create($request);
    return response()->json($result, $result['status']);
  }
  public function getUserList() { Log::debug("getUserList");
    $result = ['status' => 200]; // R-CRUD -- Retrieve all users
    $result['data'] = $this->userService->getAll();
    return response()->json($result, $result['status']);
  }
  public function updateUser(UpdateUserRequest $request) { Log::debug("-fn-updateUser", $request->toArray());
  // public function updateUser(Request $request) { Log::info("-fn-updateUser", $request->toArray());
    $result = ['status' => 200]; // U-CRUD -- Update user
    $result['data'] = $this->userService->update($request);
    return response()->json($result, $result['status']);
  }
  public function deleteById($rId) { //Log::debug("UserController::deleteById=$rId");
    $result = ['status' => 200]; // D-CRUD -- Delete user by id
    $result['id'] = $this->userService->deleteById($rId);
    return response()->json($result, $result['status']);
  }
}
