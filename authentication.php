<?php
require_once 'database.php';

$roles = [
  'user',
  'admin',
];

session_start();

function isLoggedIn()
{
  return isset($_SESSION['user_id']);
}

function getLevelOfRole($role)
{
  global $roles;
  $level = array_search($role, $roles);
  return $level === false ? -1 : $level;
}

function validate($user)
{
  $user['username'] = trim($user['username'] ?? '');
  $user['email'] = trim($user['email'] ?? '');
  $user['password'] = $user['password'] ?? '';
  return preg_match('/^[a-zA-Z0-9_]{3,}$/', $user['username']) // Alphanumeric + underscores, min 3 chars
    && filter_var($user['email'], FILTER_VALIDATE_EMAIL) // Valid email format
    && strlen($user['password']) >= 8; // Min 8 chars
}

function register($user)
{
  if (validate($user)) {
    // Hash the password before storing
    $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
    $user['role'] = 'user'; // Default role
    if (create('users', $user) !== false) {
      return true; // Registration successful
    } else {
      return "Error: Could not register user.";
    }
  } else {
    return "Invalid input. Please check your details.";
  }
}

function login($username, $password)
{
  $userID = find('users', 'username', $username);
  if ($userID !== false) {
    $storedUser = read('users', $userID);
    if (password_verify($password, $storedUser['password'])) {
      $_SESSION['user_id'] = $userID;
      $_SESSION['username'] = $storedUser['username'];
      $_SESSION['role'] = $storedUser['role'];
      return true;
    }
  }
  return false;
}

function logout()
{
  session_unset();
  session_destroy();
}
