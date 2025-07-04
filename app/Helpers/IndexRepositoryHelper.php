<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
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
    protected $defaultOrderBy = null;
    protected $defaultOrderDir = 'desc';
    protected $template;
    protected $customFilters = [];

    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->defaultOrderBy = 'id';
        $this->defaultOrderDir = 'desc';
    }
    public function addFilter(string $name, string $label, string $type = 'select', array $options = [])
    {
        $this->customFilters[] = compact('name', 'label', 'type', 'options');
        return $this;
    }
    protected function applyCustomFilters(Builder $query)
    {
        foreach ($this->customFilters as $filter) {
            $name = $filter['name'];
            $value = request($name);
            $type = $filter['type'] ?? 'select';

            if ($value === null || $value === '') continue;

            // Range filters
            if (Str::endsWith($name, '_from')) {
                $column = Str::replaceLast('_from', '', $name);
                $query->whereDate($column, '>=', $value);
            } elseif (Str::endsWith($name, '_to')) {
                $column = Str::replaceLast('_to', '', $name);
                $query->whereDate($column, '<=', $value);
            }

            // Dot-notation handling
            elseif (Str::contains($name, '.')) {
                $parts = explode('.', $name);
                $field = array_pop($parts);
                $relation = implode('.', $parts);

                $query->whereHas($relation, function ($q) use ($field, $value, $type) {
                    if ($type === 'text') {
                        $q->where($field, 'like', "%{$value}%");
                    } else {
                        $q->where($field, $value);
                    }
                });
            }

            // Direct field
            else {
                if ($type === 'text') {
                    $query->where($name, 'like', "%{$value}%");
                } else {
                    $query->where($name, $value);
                }
            }
        }

        return $query;
    }
    public function displayStatusAs($value, $statuses = [], $defaultLabel = '', $showChip = true)
    {
        // $matched = collect($statuses)->firstWhere('id', $value); dont use firstWhere because when value is 0, its show null
        $matched = null;
        foreach ($statuses as $status) {
            if (array_key_exists('id', $status) && $status['id'] === $value) {
                $matched = $status;
                break;
            }
        }

        if (!$showChip) {
            return $matched['label'];
        }

        return '<span class="badge badge-' . $matched['class'] . '">' . $matched['label'] . '</span>';
    }

    public function setColumns(...$columns)
    {
        $this->columns = array_map(function ($col) {
            if (is_array($col)) return $col;

            return [
                'key' => $col,
                'label' => Str::headline(Str::afterLast($col, '.')),
            ];
        }, $columns);

        return $this;
    }

    public function setColumnLabel($column = "", $label = "", $customFilter = false)
    {
        if ($column !== "" && $label !== "") {
            $this->columns = array_map(function ($col) use ($column, $label) {
                if ((is_array($col) && $col['key'] === $column) || $col === $column) {
                    return [
                        'key' => is_array($col) ? $col['key'] : $col,
                        'label' => $label,
                    ];
                }
                return $col;
            }, $this->columns);
        }

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

    public function displayCreatedAtAs($value, $showTime = false)
    {
        return $showTime
            ? $value->format('d M Y H:i')
            : $value->format('d M Y');
    }

    public function index(Builder $query)
    {
        $query = $this->applyCustomFilters($query);

        if (request()->ajax()) {
            return $this->buildAjaxDataTable($query);
        }

        $records = $query->paginate(10);

        return view('base.index', [
            'items' => $records,
            'columns' => $this->columns,
            'columnDisplays' => $this->columnDisplayCallbacks,
            'columnSearch' => $this->columnSearchability,
            'pageTitle' => $this->pageTitle,
            'tableTitle' => $this->tableTitle,
            'viewData' => $this->viewData,
            'model' => $this->model,
            'customFilters' => $this->customFilters, // 👈 pass to blade
            "orderByDir" => $this->defaultOrderDir
        ]);
    }

    public function buildAjaxDataTable(Builder $query)
    {
        $query = $this->applyCustomFilters($query);
       
        if ($this->defaultOrderBy && !request()->has('order')) {
            $query->orderBy($this->defaultOrderBy, $this->defaultOrderDir);
        }

        $datatable = DataTables::of($query);

        foreach ($this->columns as $col) {
            $colKey = is_array($col) ? $col['key'] : $col;

            if (isset($this->columnDisplayCallbacks[$colKey])) {
                $datatable->editColumn($colKey, function ($item) use ($colKey) {
                    $callbackData = $this->columnDisplayCallbacks[$colKey];
                    return call_user_func_array($callbackData['callback'], [
                        data_get($item, $colKey),
                        ...$callbackData['args'],
                    ]);
                });
                continue;
            }

            if (Str::contains($colKey, '.')) {
                $datatable->addColumn($colKey, fn($item) => data_get($item, $colKey));
            }
        }

        $datatable->addColumn('actions', function ($item) {
            $model = class_basename($item);
            $routePrefix = Str::kebab($model);
            $buttons = '';

            if ($this->viewData['edit']) {
                $buttons .= '<a href="' . route($routePrefix . '.edit', $item->id) . '" class="text-indigo-600 text-sm">✏️</a> ';
            }

            if ($this->viewData['trash']) {
                $route = route($routePrefix . '.trash', $item->id);
                $buttons .= '<button class="btn btn-sm btn-link text-danger trashButton" data-url="' . $route . '">🗑️</button>';
            }

            if ($this->viewData['restore']) {
                $route = route($routePrefix . '.restore', $item->id);
                $buttons .= '<button class="btn btn-sm btn-link text-success restoreButton" data-url="' . $route . '">♻️</button>';
            }

            return $buttons;
        });

        foreach ($this->columns as $col) {
            $colKey = is_array($col) ? $col['key'] : $col;

            if (isset($this->columnSearchability[$colKey]) && !$this->columnSearchability[$colKey]) {
                continue;
            }

            if (Str::contains($colKey, '.')) {
                [$relation, $field] = explode('.', $colKey, 2);
                $datatable->filterColumn($colKey, function ($query, $keyword) use ($relation, $field) {
                    $query->whereHas($relation, fn($q) => $q->where($field, 'like', "%{$keyword}%"));
                });
            } else {
                $datatable->filterColumn($colKey, function ($query, $keyword) use ($colKey) {
                    $query->where($colKey, 'like', "%{$keyword}%");
                });
            }
        }

        return $datatable->rawColumns(['actions', 'status', 'approval_status', "monthly_loop"])->make(true);
    }

    public function setDefaultOrder(string $column, string $direction = 'desc')
    {
        $this->defaultOrderBy = $column;
        $this->defaultOrderDir = $direction;
        return $this;
    }
}
