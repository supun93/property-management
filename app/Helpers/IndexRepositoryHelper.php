<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class IndexRepositoryHelper
{
    public $model;
    public $columns = [];
    public $columnDisplayCallbacks = [];
    public $columnSearchability = [];
    public $pageTitle = '';
    public $tableTitle = '';
    public $viewData = [
        'view' => true,
        'export' => false,
        'restore' => false,
        'edit' => true,
        'add' => true,
        'trash' => false,
        'trashList' => false,
    ];

    protected $template;

    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    public function setPageTitle($title)
    {
        $this->pageTitle = $title;
        return $this;
    }

    public function setTableTitle($title)
    {
        $this->tableTitle = $title;
        return $this;
    }

    public function setColumns(...$columns)
    {
        $this->columns = $columns;
        return $this;
    }

    public function setColumnDisplay($column, $callback, $args = [])
    {
        $this->columnDisplayCallbacks[$column] = ['callback' => $callback, 'args' => $args];
        return $this;
    }

    public function setColumnSearchability($column, $isSearchable)
    {
        $this->columnSearchability[$column] = $isSearchable;
        return $this;
    }

    public function enableViewData(...$keys)
    {
        foreach ($keys as $key) {
            $this->viewData[$key] = true;
        }
        return $this;
    }

    public function disableViewData(...$keys)
    {
        foreach ($keys as $key) {
            $this->viewData[$key] = false;
        }
        return $this;
    }

    public function render($template)
    {
        $this->template = $template;
        return $this;
    }

    public function index(Builder $query)
    {
        $records = $query->paginate(10);

        return view('base.index', [
            'items' => $records,
            'columns' => $this->columns,
            'columnDisplays' => $this->columnDisplayCallbacks,
            'columnSearch' => $this->columnSearchability,
            'pageTitle' => $this->pageTitle,
            'tableTitle' => $this->tableTitle,
            'viewData' => $this->viewData,
            'model' => $this->model, // 🔥 ADD THIS LINE
        ]);
    }

    public function displayCreatedAtAs($value, $showTime = false)
    {
        return $showTime
            ? $value->format('d M Y H:i')
            : $value->format('d M Y');
    }
}
