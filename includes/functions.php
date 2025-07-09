<?php

function dd($var)
{
    echo "<pre>";
    print_r($var);
    echo "</pre>";
    die("showing results");
}
function show($var)
{
    echo "<pre>";
    print_r($var);
    echo "</pre>";
}

function sanitize_post($data)
{
    foreach ($data as $key => $value) {
        $value = htmlspecialchars(strip_tags(stripslashes(trim($value ?? ""))), ENT_QUOTES, 'UTF-8');
        $data[$key] = $value;
    }
    return $data;
}

function sanitize($value)
{
    return htmlspecialchars(strip_tags(stripslashes(trim($value ?? ""))), ENT_QUOTES, 'UTF-8');
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input()
{
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_validate($token)
{
    if (!isset($_SESSION['csrf_token']) || !$token || $_SESSION['csrf_token'] !== $token) {
        return false;
    }
    unset($_SESSION['csrf_token']);
    return true;
}


function beautify_number($num)
{
    if (fmod($num, 1) != 0) {
        return number_format($num, 2);
    }
    return intval($num);
}
