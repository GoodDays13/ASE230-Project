<?php

global $database;

// ---------- Internal Helpers ----------
function ensureDatabaseReady()
{
  global $database;
  if ($database) {
    return $database;;
  }
  $database = new PDO('mysql:host=localhost;dbname=publicsquare;charset=utf8', 'root');
  return $database;
}

// ---------- Public API ----------

// Create a new post. Returns the created post or false on conflict.
function create($type, $data)
{
  $database = ensureDatabaseReady();
  if ($database === false) {
    return false;
  }
  $placeholders = implode(", ", array_fill(0, count($data), "?"));
  $columns = implode(", ", array_keys($data));
  $stmt = $database->prepare("INSERT INTO $type ($columns) VALUES ($placeholders)");
  if ($stmt->execute(array_values($data))) {
    $data['id'] = $database->lastInsertId();
    return $data;
  } else {
    return false;
  }
}

// List all posts.
function read($type, $id)
{
  $database = ensureDatabaseReady();
  if ($database === false) {
    return false;
  }
  $stmt = $database->prepare("SELECT * FROM $type WHERE id = ?");
  if ($stmt->execute([$id])) {
    return $stmt->fetch(PDO::FETCH_ASSOC);
  } else {
    return false;
  }
}

function readAll($type)
{
  $database = ensureDatabaseReady();
  if ($database === false) {
    return false;
  }
  $stmt = $database->prepare("SELECT * FROM $type");
  if ($stmt->execute()) {
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  } else {
    return false;
  }
}

// Find a post by a specific key/value pair. Returns the row found or false.
function find($type, $key, $value)
{
  $database = ensureDatabaseReady();
  if ($database === false) {
    return false;
  }
  $stmt = $database->prepare("SELECT * FROM $type WHERE $key = ? LIMIT 1");
  if ($stmt->execute([$value])) {
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?? false;
  } else {
    return false;
  }
}

// Find a post by multiple criteria. Returns the row found or false.
function findArray($type, $criteria)
{
  $database = ensureDatabaseReady();
  if ($database === false) {
    return false;
  }
  $whereClause = implode(" AND ", array_map(function ($key) {
    return "$key = ?";
  }, array_keys($criteria)));
  $stmt = $database->prepare("SELECT * FROM $type WHERE $whereClause LIMIT 1");
  if ($stmt->execute(array_values($criteria))) {
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?? false;
  } else {
    return false;
  }
}

function update($type, $id, $newData)
{
  $database = ensureDatabaseReady();
  if ($database === false) {
    return false;
  }
  $setClause = implode(", ", array_map(function ($key) {
    return "$key = ?";
  }, array_keys($newData)));
  $stmt = $database->prepare("UPDATE $type SET $setClause WHERE id = ?");
  $values = array_values($newData);
  $values[] = $id;
  if ($stmt->execute($values)) {
    return true;
  } else {
    return false;
  }
}

function delete($type, $id)
{
  $database = ensureDatabaseReady();
  if ($database === false) {
    return false;
  }
  $stmt = $database->prepare("DELETE FROM $type WHERE id = ?");
  if ($stmt->execute([$id])) {
    return true;
  } else {
    return false;
  }
}
