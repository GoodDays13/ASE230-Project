<?php

// JSON file path, resolved relative to this file to avoid CWD issues
$databasePath = __DIR__ . "/raw_files/";

// ---------- Internal Helpers ----------
function ensureDatabaseReady($type)
{
  global $databasePath;
  $databaseFile = $databasePath . $type . ".json";
  if (!is_dir($databasePath)) {
    if (!@mkdir($databasePath, 0777, true) && !is_dir($databasePath)) {
      echo 'ensureDatabaseReady: failed to create dir <br>';
      return false;
    }
  }
  if (!is_writable($databasePath)) {
    echo 'ensureDatabaseReady: database unwritable <br>';
    return false;
  }
  if (!file_exists($databaseFile)) {
    if (@file_put_contents($databaseFile, json_encode([])) === false) {
      echo 'ensureDatabaseReady: failed to init file <br>';
      return false;
    }
  }
  if (file_exists($databaseFile) && !is_writable($databaseFile)) {
    echo 'ensureDatabaseReady: file unwritable <br>';
    return false;
  }
  return $databaseFile;
}

function loadData($type)
{
  $databaseFile = ensureDatabaseReady($type);
  if (!$databaseFile) {
    echo 'loadData: failed to get file <br>';
    return false;
  }
  $data = [];

  $json = @file_get_contents($databaseFile);

  if ($json === false) {
    echo 'loadData: failed to get json <br>';
    return false;
  }

  $data = json_decode($json, true);

  if (!is_array($data)) {
    echo 'loadData: is not array <br>';
    return false;
  }

  return $data;
}

function saveData($type, $data)
{
  $databaseFile = ensureDatabaseReady($type);
  if (!$databaseFile) {
    return false;
  }
  $json = json_encode($data, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT);
  if ($json === false) {
    return false;
  }
  return file_put_contents($databaseFile, $json);
}

/* function sortByIndex($type, $data) */
/* { */
/*   global $indexes; */
/*   $index = 'id'; */
/*   if (isset($indexes[$type])) { */
/*     $index = $indexes[$type]; */
/*   } */
/*   usort($data, function ($a, $b) use ($index) { */
/*     return strcmp($a[$index], $b[$index]); */
/*   }); */
/*   return $data; */
/* } */

// ---------- Public API ----------

// Create a new post. Returns the created post or false on conflict.
function create($type, $data)
{
  $database = loadData($type);
  if ($database === false) {
    echo "Create: Failed to load data";
    return false;
  }

  $data['created_at'] = date('Y-m-d H:i:s');

  $database[] = $data;

  if (!saveData($type, $database)) {
    return false;
  }
  return $data;
}

// List all posts.
function read($type, $id)
{
  return loadData($type)[$id] ?? null;
}

function readAll($type)
{
  return loadData($type);
}

function update($type, $id, $newData)
{
  $database = loadData($type);
  if ($database === false) {
    return false;
  }
  if (!isset($database[$id])) {
    return false;
  }
  $database[$id] = array_merge($database[$id], $newData);
  if (!saveData($type, $database)) {
    return false;
  }
  return true;
}

function delete($type, $id)
{
  $database = loadData($type);
  if ($database === false) {
    return false;
  }
  if (!isset($database[$id])) {
    return false;
  }
  unset($database[$id]);
  if (!saveData($type, $database)) {
    return false;
  }
  return true;
}
