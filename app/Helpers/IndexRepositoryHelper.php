<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
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
    public $extraListButtonUrl = '';
    public $extraListButtonLabel = '';
    public $refId = '';
    public $viewData = [
        'view' => true,
        'export' => false,
        'restore' => false,
        'edit' => true,
        'add' => true,
        'trash' => false,
        'trashList' => false,
        'download' => false
    ];
    protected $defaultOrderBy = null;
    protected $defaultOrderDir = 'desc';
    protected $template;
    protected $customFilters = [];
    protected $rawColumnKeys = ['actions', 'status', 'approval_status', 'monthly_loop'];
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->defaultOrderBy = 'id';
        $this->defaultOrderDir = 'desc';
    }

    public function addRawColumns(...$columns)
    {
        $this->rawColumnKeys = array_unique(array_merge($this->rawColumnKeys, $columns));
        return $this;
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
        $matched = null;
        foreach ($statuses as $status) {
            if (array_key_exists('id', $status) && $status['id'] === $value) {
                $matched = $status;
                break;
            }
        }

        if (!$matched) {
            return $showChip
                ? '<button type="button" disabled class="btn btn-secondary btn-sm">' . ($defaultLabel ?: '-') . '</button>'
                : ($defaultLabel ?: '-');
        }

        if (!$showChip) {
            return $matched['label'];
        }

        return '<button type="button" disabled class="btn btn-sm btn-' . $matched['class'] . '">' . $matched['label'] . '</button>';
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

    public function setColumnDBField($column = "", $dbField = "", $customFilter = false)
    {
        if ($customFilter) {

            $columns = $this->customFilters;
        } else {

            $columns = $this->columns;
        }

        if ($column !== "" && $dbField !== "" && isset($columns[$column])) {
            //set field and field's label
            $columns[$column]["dbField"] = $dbField;

            if ($columns[$column]["fKeyField"] == $column) {

                $columns[$column]["fKeyField"] = $dbField;
            }
        }

        if ($customFilter) {

            $this->customFilters = $columns;
        } else {

            $this->columns = $columns;
        }

        return $this;
    }

    public function displayListButtonAs($value, $routeName = "")
    {
        if (!$value || !$routeName) return "-";

        $url = route($routeName, ['id' => $value]);

        return "<a href='{$url}' class='btn btn-sm btn-info'>
                <i class='fa fa-list'></i>View List</a>";
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

    public function setExtraListButtonLabel($label)
    {
        $this->extraListButtonLabel = $label;
        return $this;
    }

    public function setExtraListButtonUrl($url)
    {
        $this->extraListButtonUrl = $url;
        return $this;
    }

    public function setRefferanceId($rfId)
    {
        $this->refId = $rfId;
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
        $response = $showTime
            ? $value->format('Y-m-d   H:i:A')
            : $value->format('Y-m-d');

            return $value;
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
            'customFilters' => $this->customFilters,
            "orderByDir" => $this->defaultOrderDir,
            "refId" => $this->refId,
            "extraListButtonUrl" => $this->extraListButtonUrl,
            "extraListButtonLabel" => $this->extraListButtonLabel
        ]);
    }

    public function buildAjaxDataTable(Builder $query)
    {
        $query = $this->applyCustomFilters($query);

        // Fully dynamic ordering: backend if no frontend specified
        if (!request()->has('order') || empty(request('order')[0]['column'])) {
            if ($this->defaultOrderBy) {
                $query->getQuery()->orders = null; // clear old ones
                $query->orderBy($this->defaultOrderBy, $this->defaultOrderDir);
            }
        }

        $datatable = DataTables::of($query);

        foreach ($this->columns as $col) {
            $colKey = is_array($col) ? $col['key'] : $col;

            if (isset($this->columnDisplayCallbacks[$colKey])) {
                $datatable->editColumn($colKey, function ($item) use ($colKey) {
                    $callbackData = $this->columnDisplayCallbacks[$colKey];
                    $value = data_get($item, $colKey) ?? $item->id ?? null;
                    $args = is_array($callbackData['args']) ? $callbackData['args'] : [];
                    return call_user_func_array($callbackData['callback'], [$value, ...$args]);
                });
            } else {
                $datatable->addColumn($colKey, function ($item) use ($colKey) {
                    return data_get($item, $colKey, '-');
                });
            }
        }

        $datatable->addColumn('actions', function ($item) {
            $model = class_basename($item);
            $routePrefix = Str::kebab($model);
            $buttons = '<div class="index-actions pull-right d-flex justify-content-center">';

            if ($this->viewData['edit']) {
                $buttons .= '<a href="' . route($routePrefix . '.edit', $item->id) . '" class="btn btn-sm" style="color:#282862"><span class="fa fa-edit"></span> Edit</a> ';
            }

            if ($this->viewData['trash']) {
                $route = route($routePrefix . '.trash', $item->id);
                $buttons .= '<button class="btn btn-sm trashButton" data-url="' . $route . '" style="color:#e43344"><span class="fa fa-trash"></span> Trash</button> ';
            }

            if ($this->viewData['restore']) {
                $route = route($routePrefix . '.restore', $item->id);
                $buttons .= '<button class="btn btn-sm restoreButton" data-url="' . $route . '" style="color:green"><span class="fa fa-window-restore"></span> Restore</button> ';
            }

            if ($this->viewData['download']) {
                $route = route($routePrefix . '.download', $item->id);
                $buttons .= '<a href="' . $route . '" class="btn btn-sm"><span class="fa fa-download" style="color:green"></span> Download</a> ';
            }

            $buttons .= '</div>';

            return $buttons;
        });

        $datatable->filter(function ($query) {
            $search = request()->get('search')['value'] ?? null;

            if ($search) {
                $query->where(function ($q) use ($search) {
                    foreach ($this->columns as $col) {
                        $colKey = is_array($col) ? $col['key'] : $col;

                        if (Str::contains($colKey, '.')) continue;
                        if (!Schema::hasColumn($this->model->getTable(), $colKey)) continue;

                        $q->orWhere($colKey, 'like', "%{$search}%");
                    }
                });
            }
        });

        return $datatable->rawColumns($this->rawColumnKeys)->make(true);
    }


    public function setDefaultOrder(string $column, string $direction = 'desc')
    {
        $this->defaultOrderBy = $column;
        $this->defaultOrderDir = $direction;
        return $this;
    }
}
