<?php

namespace Modules\Task\routes;

use Illuminate\Support\Facades\Route;

/**
 * Helper class để quản lý routes trong module Task với JWT và phân quyền
 * 
 * Class này cung cấp các method để đăng ký routes một cách gọn gàng và có tổ chức
 */
class RouteHelper
{
    /**
     * Đăng ký nhóm routes với middleware và phân quyền
     * 
     * @param array $config Cấu hình routes
     * @return void
     */
    public static function registerRoutesGroup(array $config)
    {
        $middleware = $config['middleware'] ?? [];
        $prefix = $config['prefix'] ?? '';
        
        Route::middleware($middleware)->prefix($prefix)->group(function () use ($config) {
            // Đăng ký routes cho Tasks
            if (isset($config['tasks'])) {
                self::registerTaskRoutes($config['tasks']);
            }
            
            // Đăng ký routes cho Calendar
            if (isset($config['calendar'])) {
                self::registerCalendarRoutes($config['calendar']);
            }
        });
    }

    /**
     * Đăng ký routes cho Tasks
     * 
     * @param array $taskConfig Cấu hình task routes
     * @return void
     */
    protected static function registerTaskRoutes(array $taskConfig)
    {
        $prefix = $taskConfig['prefix'];
        $controller = $taskConfig['controller'];
        $name = $taskConfig['name'];
        $additionalRoutes = $taskConfig['additional_routes'] ?? [];
        $resourceOnly = $taskConfig['resource_only'] ?? [];
        $resourceActions = $taskConfig['resource_actions'] ?? [];

        Route::prefix($prefix)->name($name . '.')->group(function () use ($controller, $additionalRoutes, $resourceOnly, $resourceActions) {
            // Đăng ký các routes bổ sung trước để tránh xung đột với resource routes
            foreach ($additionalRoutes as $route) {
                $methods = $route['methods'] ?? ['GET'];
                $uri = $route['uri'];
                $action = $route['action'];
                $routeName = $route['name'];

                Route::match($methods, $uri, [$controller, $action])->name($routeName);
            }

            // Đăng ký resource routes nếu được chỉ định
            if (!empty($resourceOnly)) {
                Route::apiResource('', $controller)
                    ->only($resourceOnly)
                    ->parameters(['' => 'task']);
            }
            
            if (!empty($resourceActions)) {
                Route::apiResource('', $controller)
                    ->only($resourceActions)
                    ->parameters(['' => 'task']);
            }
        });
    }

    /**
     * Đăng ký routes cho Calendar
     * 
     * @param array $calendarConfig Cấu hình calendar routes
     * @return void
     */
    protected static function registerCalendarRoutes(array $calendarConfig)
    {
        $prefix = $calendarConfig['prefix'];
        $controller = $calendarConfig['controller'];
        $name = $calendarConfig['name'];
        $routes = $calendarConfig['routes'] ?? [];

        Route::prefix($prefix)->name($name . '.')->group(function () use ($controller, $routes) {
            foreach ($routes as $route) {
                $methods = $route['methods'] ?? ['GET'];
                $uri = $route['uri'];
                $action = $route['action'];
                $routeName = $route['name'];

                Route::match($methods, $uri, [$controller, $action])->name($routeName);
            }
        });
    }

    /**
     * Đăng ký resource routes với các routes bổ sung (legacy method)
     * 
     * @param string $prefix Prefix cho routes
     * @param string $controller Controller class
     * @param string $name Tên cho route names
     * @param array $additionalRoutes Các routes bổ sung
     * @return void
     */
    public static function registerResourceRoutes($prefix, $controller, $name, $additionalRoutes = [])
    {
        // Đăng ký các routes bổ sung trước để tránh xung đột với {task}
        foreach ($additionalRoutes as $route) {
            $methods = $route['methods'] ?? ['GET'];
            $uri = trim($route['uri'], '/');
            $action = $route['action'];
            $routeName = $route['name'];

            $fullUri = $prefix . ($uri !== '' ? '/' . $uri : '');
            Route::match($methods, $fullUri, [$controller, $action])->name($name . '.' . $routeName);
        }

        // Đăng ký resource trực tiếp với prefix để tham số có tên hợp lệ (api/tasks/{task})
        Route::apiResource($prefix, $controller)
            ->names($name)
            ->parameters([$prefix => 'task']);
    }

    /**
     * Đăng ký nested routes
     * 
     * @param string $prefix Prefix cho routes
     * @param string $name Tên cho route names
     * @param array $routes Các routes con
     * @return void
     */
    public static function registerNestedRoutes($prefix, $name, $routes)
    {
        Route::prefix($prefix)->name($name . '.')->group(function () use ($routes) {
            foreach ($routes as $route) {
                $methods = $route['methods'] ?? ['GET'];
                $uri = $route['uri'];
                $controller = $route['controller'];
                $action = $route['action'];
                $routeName = $route['name'];
                
                Route::match($methods, $uri, [$controller, $action])->name($routeName);
            }
        });
    }



}
