<?php

$api->version('v1', ['namespace' => 'App\Http\Controllers\Api', 'middleware' => []], function ($api) {
    $api->any("api-status", 'ApiStatusController@status')->name('api.status');
});
