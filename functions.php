<?php
session_start();
require_once 'config.php';

// Escape output
function e($val){ return htmlspecialchars($val); }

// CSRF
function csrf(){
    if(empty($_SESSION['csrf'])){
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

// Auth
function isLoggedIn(){ return isset($_SESSION['user_id']); }
function isAdmin(){ return isset($_SESSION['role']) && $_SESSION['role']==='admin'; }
function redirect($url){ header("Location:$url"); exit; }

// Login
function loginUser($user){
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
}

// Logout
function logoutUser(){
    if(isLoggedIn()){
        global $pdo;
        $stmt = $pdo->prepare("SELECT id FROM users WHERE id=?");
        $stmt->execute([$_SESSION['user_id']]);
        if($stmt->fetch()) logActivity($_SESSION['user_id'], 'Logout');
    }
    session_destroy();
}

// Log activity
function logActivity($user_id,$action){
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO logs(user_id,action,created_at) VALUES(?,?,NOW())");
    $stmt->execute([$user_id,$action]);
}
