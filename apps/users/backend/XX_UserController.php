<?php
declare(strict_types=1);

namespace App\Http\Controllers;

// use App\DTOs\CreateUserDto;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class UserController extends Controller {
  public function __construct(
    public readonly UserService $userService
  ) {}

  public function store(CreateUserRequest $request) {  Log::debug("store user", $request->toArray());
    $result = ['status' => 200];
    $result['data'] = $this->userService->create($request);
    return response()->json($result, $result['status']);
  }
  // public function store(CreateUserRequest $request) {  Log::debug("store user", $request->toArray());
  //   $result = ['status' => 200];
  //   try {
  //     $result['data'] = $this->userService->create($request);
  //   } catch (Exception $e) {
  //     $result = [
  //       'status' => 500,
  //       'error' => $e->getMessage()
  //     ];
  //   }

  //   return response()->json($result, $result['status']);
  // }
  public function updateUser(UpdateUserRequest $request) { Log::debug("Update user", $request->toArray());
    $result = ['status' => 200];
    $result['data'] = $this->userService->update($request);
    return response()->json($result, $result['status']);
  }
  // public function updateUser(UpdateUserRequest $request) { Log::debug("Update user", $request->toArray());
  //   $result = ['status' => 200];
  //   try {
  //     $result['data'] = $this->userService->update($request);
  //   } catch (Exception $e) {
  //     $result = [
  //       'status' => 500,
  //       'error' => $e->getMessage()
  //     ];
  //   }
  //   // return UserResource::make($user);
  //   return response()->json($result, $result['status']);
  // }
  public function getUserList() {
    $result = ['status' => 200];
    $result['data'] = $this->userService->getAll();
    return response()->json($result, $result['status']);
  }
  // public function getUserList() {
  //   $result = ['status' => 200];
  //   try {
  //     $result['data'] = $this->userService->getAll();
  //   } catch (Exception $e) {
  //     $result = [
  //       'status' => 500,
  //       'error' => $e->getMessage()
  //     ];
  //   }
  //   return response()->json($result, $result['status']);
  // }
  public function deleteById($rId) { //Log::debug("UserController::deleteById=$rId");
    $result = ['status' => 200];
    $result['id'] = $this->userService->deleteById($rId);
    return response()->json($result, $result['status']);
  }
  // public function deleteById($rId) { //Log::debug("UserController::deleteById=$rId");
  //   $result = ['status' => 200];
  //   try {
  //     $result['id'] = $this->userService->deleteById($rId);
  //   } catch (Exception $e) {
  //     $result = [
  //       'status' => 500,
  //       'error' => $e->getMessage()
  //     ];
  //   }
  //   return response()->json($result, $result['status']);
  // }
}
