<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Library\FilterLibrary;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function create(Request $request): JsonResponse
    {
        // get input query
        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'max:100'],
            'password' => ['required', 'confirmed', Password::min(10)->letters()->mixedCase()->numbers()->symbols()],
            'email'    => ['required', 'email', 'unique:users,email'],
            'role_id'  => ['required', 'exists:roles,id'],
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'status'  => 'error',
                'message' => 'Permintaan gagal diproses',
                'errors'  => $validator->errors()
            ], 422);
        }

        // input
        $input = $validator->validated();

        // create
        $user = new User;
        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->password = $input['password'];
        $user->role_id = $input['role_id'];
        $user->save();

        // return
        return response()->json([
            'message' => 'Data berhasil dibuat',
            'data'    => [
                'id'       => $user->id,
                'name'     => $user->name,
                'email'    => $user->email,
                'password' => $user->password,
                'role_id'  => $user->role_id,
            ],
        ], 200);
    }

    //====================================================================================================

    public function delete(Request $request): JsonResponse
    {
        // find
        $id   = $request->input('id');
        $user = User::find($id);
        
        if (empty($user))
        {
            return response()->json([
                'status'  => 'error',
                'message' => 'Permintaan gagal diproses',
                'errors'  => [
                    'Data tidak ditemukan'
                ],
            ], 422);
        }

        // delete
        $user->delete();

        // return
        return response()->json([
            'status'  => 'success',
            'message' => "User {$user->name} berhasil dihapus",
        ], 200);
    }

    //====================================================================================================

    public function find(Request $request): JsonResponse
    {
        // input
        $input = $request->validate([
            'id' => ['numeric']
        ]);
        
        // get based on roles
        $result = User::select(['users.id', 'users.name', 'password', 'role_id', 'roles.name as role_name', 'email'])
                      ->join('roles', 'role_id', '=', 'roles.id')
                      ->first($input['id']);

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil ditarik',
            'data'    => $result,
        ], 200);
    }

    //====================================================================================================

    public function index(Request $request): JsonResponse
    {
        // columns
        $columns = [
            'users.id', 'users.name', 'password', 'role_id', 'roles.name as role_name', 'email'
        ];

        $allowedQuery = [
            'name', 'password', 'role_id', 'role_name', 'email'
        ];

        // get input query
        $validator = Validator::make($request->all(), [
            'limit'        => ['numeric', 'max:10'],
            'offset'       => ['numeric'],
            'order.column' => ['in:'.implode(',', $allowedQuery)],
            'order.dir'    => ['in:asc,desc'],
        ]);

        if ($validator->fails())
        {
            return response()->json([
                'status'  => 'error',
                'message' => 'Permintaan gagal diproses',
                'errors'  => $validator->errors()
            ], 422);
        }

        // input
        $input = $validator->validated();

        // query
        // format: ['query']['status']
        $query = $request->input('query', []);

        // orm
        $orm = User::select(...$columns)
                   ->join('roles', 'role_id', '=', 'roles.id');

        if (count($query) > 0)
        {
            // init library
            $filter = new FilterLibrary();
            $orm    = $filter->parse($orm, $query, ['name' => 'users.name', 'role_name' => 'roles.name']);
        }

        // set limit, order, etc
        $result = $orm->orderBy($input['order']['column'], $input['order']['dir'])
                      ->offset($input['offset'])
                      ->limit($input['limit'])
                      ->get();

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil ditarik',
            'data'    => empty($result) ? [] : $result
        ], 200);
    }

    //====================================================================================================

    public function update(Request $request): JsonResponse
    {
        // find
        $id   = $request->input('id');
        $user = User::find($id);
        
        if (empty($user))
        {
             return response()->json([
                'status'  => 'error',
                'message' => 'Permintaan gagal diproses',
                'errors'  => [
                    'Data tidak ditemukan'
                ],
            ], 422);
        }

        // set rules
        $rules = [
            'name'     => ['required', 'max:100'],
            'email'    => ['required', 'email', "unique:users,email,{$id},id"],
            'role_id'  => ['required', 'exists:roles,id'],
        ];

        // check if password is supplied, but optional
        $password = $request->input('password', null);
        if (!is_null($password))
        {
            $rules['password'] = ['required', 'confirmed', Password::min(10)->letters()->mixedCase()->numbers()->symbols()];
        }

        // get form
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails())
        {
            return response()->json([
                'status'  => 'error',
                'message' => 'Permintaan gagal diproses',
                'errors'  => $validator->errors()
            ], 422);
        }

        // input
        $input = $validator->validated();
        
        // create
        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->role_id = $input['role_id'];

        if (!is_null($password))
        {
            $user->password = $input['password'];
        }

        // save
        $user->save();

        // return
        return response()->json([
            'status'  => 'success',
            'message' => 'Data berhasil diperbaharui',
            'data'    => $user,
        ], 200);
    }

    //====================================================================================================
}
