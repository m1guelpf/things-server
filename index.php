<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as DB;

$capsule = new DB;

$capsule->addConnection([
    'driver'    => 'sqlite',
    'database'  => '/Users/m1guelpiedrafita/Library/Containers/com.culturedcode.ThingsMac/Data/Library/Application Support/Cultured Code/Things/Things.sqlite3',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();

$tasks = DB::table('TMTask')->where('status', 0)->whereNotNull('startDate')->orderByDesc('todayIndex')->get();

$relationship = DB::table('TMTaskTag')->select('tasks as taskId', 'title as tag')
  ->whereIn('tasks', $tasks->map->uuid)
  ->join('TMTag', 'TMTag.uuid', 'TMTaskTag.tags')
  ->get();

header('content-type: application/json');
header('Access-Control-Allow-Origin: chrome-extension://opkgffdmcihmjaobndfhmgfmgflichpg');

echo $tasks->map(function ($task) use ($relationship) {
    return [
        'id' => $task->uuid,
        'title' => $task->title,
        'description' => $task->notes,
        'tags' => $relationship->where('taskId', $task->uuid)->map(fn ($rel) => $rel->tag),
    ];
})->reverse()->values()->toJson();
