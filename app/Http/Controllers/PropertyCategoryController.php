<?php

namespace App\Http\Controllers;

use App\Models\PropertyCategory;
use App\Helpers\IndexRepositoryHelper;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class PropertyCategoryController extends BaseController
{
    protected $repository;
    protected $trash;

    public function __construct()
    {
        $this->trash = request()->has('trash');
        $this->repository = new IndexRepositoryHelper(new PropertyCategory());
    }

    public function index()
    {
        if ($this->trash) {
            $this->repository->setPageTitle("Property Categories - Trashed");
        } else {
            $this->repository->setPageTitle("Property Categories");
        }

        $this->repository
            ->setColumns("id", "name", "created_at")
            ->setColumnDisplay("created_at", [$this->repository, 'displayCreatedAtAs'], [false])
            ->setColumnSearchability("created_at", false);

        $query = PropertyCategory::query();

        if ($this->trash) {
            $query = $query->onlyTrashed();

            $this->repository->setTableTitle("Property Categories - Trashed")
                ->disableViewData("view")
                ->enableViewData("export", "restore", "edit", "add", "list");
        } else {
            $this->repository->setTableTitle("Property Categories")
                ->disableViewData("view")
                ->enableViewData("export", "trash", "edit", "add", "trashList");
        }

        return $this->repository->render("layouts.master")->index($query);
    }
}
