<?php
// エラー詳細を表示する場合Onにする: 本番ではコメントアウトすること
ini_set('display_errors', 'On');

$router = json_decode(file_get_contents(__DIR__ . '/router.json'), true);
$currentPath = trim(str_replace(dirname($_SERVER['SCRIPT_NAME']), '', $_SERVER["REQUEST_URI"]), '/');

// 該当するactionがあるかチェック
foreach ($router as $content => $info) {
    // *または配列でない場合default
    if (empty($info) || $info === '*' || !is_array($info)) {
        $info = array('validate' => 'validate', 'send' => 'send');
    }

    // matchするか
    foreach ($info as $type => $path) {
        if ($currentPath === "{$content}/{$path}") {
            $type = ucfirst($type);
            require_once "action/{$type}Action.php";
            $className = "Action\\{$type}Action";
            $clazz = new $className($content);
            echo $clazz->execute();
            exit;
        }
    }
}

header("HTTP/1.0 404 Not Found");
exit;
