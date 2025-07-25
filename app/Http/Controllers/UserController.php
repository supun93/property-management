<?php

namespace App\Http\Controllers;

use App\Helpers\IndexRepositoryHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $repository;
    protected $trash;

    public function __construct()
    {
        $this->trash = request()->has('trash');
        $this->repository = new IndexRepositoryHelper(new User());
    }

    public function index(Request $request)
    {
        if ($this->trash) {
            $this->repository->setPageTitle("Users - Trashed");
        } else {
            $this->repository->setPageTitle("Users");
        }

        $this->repository
            ->setColumns("id", "name", "email", "role", "created_at")
            ->setColumnDisplay("created_at", [$this->repository, 'displayCreatedAtAs'], [false])
            ->setColumnSearchability("created_at", false);


        $query = User::query();

        if ($this->trash) {
            $query = $query->onlyTrashed();

            $this->repository->setTableTitle("Users - Trashed")
                ->disableViewData("view")
                ->enableViewData("export", "restore", "edit", "add", "list");
        } else {
            $this->repository->setTableTitle("Users")
                ->disableViewData("view")
                ->enableViewData("export", "trash", "edit", "add", "trashList");
        }

        return $this->repository->render("layouts.master")->index($query);
    }

    public function trash(Request $request)
    {
        $this->trash = true;
        return $this->index($request);
    }

    public function create()
    {
        return view('user.create');
    }
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('user.edit', compact('user'));
    }

    public function save(Request $request)
    {
        $data = request()->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|string|max:50',
            'password' => 'required|string|min:6',
        ]);
        $record = new User();
        $record->name = $request->name;
        $record->email = $request->email;
        $record->role = $request->role;
        $record->password = Hash::make($request->password);
        $record->save();

        return response()->json("success");
    }
    public function update($id, Request $request)
    {
        $record = User::findOrFail($id);
        $data = request()->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'role' => 'required|string|max:50',
            'password' => 'required|string|min:6',
        ]);

        $record->name = $request->name;
        $record->email = $request->email;
        $record->role = $request->role;
        $record->password = Hash::make($request->password);
        $record->save();

        return response()->json("success");
    }

    public function delete($id)
    {
        $record = User::findOrFail($id);
        $record->delete();

        return response()->json("success");
    }

    public function restore($id)
    {
        $record = User::withTrashed()->findOrFail($id);
        $record->restore();

        return response()->json("success");
    }
    public function searchData()
    {
        $search = request()->get('query');
        $query = User::query();

        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $records = $query->limit(10)->get(['id', 'name']);

        return response()->json($records);
    }
}
