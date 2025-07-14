<?php

namespace App\Providers;

use App\Models\Scheduler;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule; // ✅ correct
use Illuminate\Support\Facades\Artisan;

class ScheduleServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $pending = Scheduler::where(function ($q) {
            $q->whereDate("next_run_date", "<=", now())
                ->orWhereNull("next_run_date");
        })->where("status", 1)->get();

        foreach ($pending as $rec) {
            $type = $rec->type; // 1:daily, 2:weekly, 3:monthly, 4:yearly
            $method = $rec->method; // GenerateMonthlyRents.handle, InvoiceController.update, etc

            if (str_contains($method, '@')) {
                // For controller@method syntax
                [$controller, $action] = explode('@', $method);
                if (class_exists($controller)) {
                    $instance = app($controller);
                    if (method_exists($instance, $action)) {
                        $instance->$action($rec);
                    }
                }
            } elseif (str_contains($method, '.')) {
                // For class.method syntax
                [$class, $action] = explode('.', $method);
                if (class_exists($class)) {
                    $instance = app($class);
                    if (method_exists($instance, $action)) {
                        $instance->$action($rec);
                    }
                }
            } elseif (class_exists($method)) {
                // If method is just a class name, call handle
                $instance = app($method);
                if (method_exists($instance, 'handle')) {
                    $instance->handle($rec);
                }
            } elseif (str_contains($method, ':')) {
                // Artisan command
                Artisan::call($method);
            }

            switch ($type) {
                case 1: // daily
                    $rec->next_run_date = now()->addDay();
                    break;
                case 2: // weekly
                    $rec->next_run_date = now()->addWeek();
                    break;
                case 3: // monthly
                    $rec->next_run_date = now()->addMonth();
                    break;
                case 4: // yearly
                    $rec->next_run_date = now()->addYear();
                    break;
                default:
                    $rec->next_run_date = null;
            }
            $rec->last_run_date = now();
            $rec->save();
        }
    }
}
