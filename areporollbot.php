<?php

function roll($dice) {
    $output = [];
    $rolled = [];
    for ($i = 1; $i <= $dice; $i++) {
        $rolled[] = rand(1, 6);
    }
    $output['rolled'] = $rolled;
    sort($rolled);
    while (count($rolled) && $rolled[0] == 1) {
        array_pop($rolled);
        array_shift($rolled);
    }
    $output['result'] = 0;
    for ($i = 0; $i < 3; $i++) {
        if ($rolled[count($rolled) - 1 - $i]) {
            $output['result'] += $rolled[count($rolled) - 1 - $i];
        }
    }
    return $output;
}

$message = null;

$body = json_decode(file_get_contents("php://input"), true);
if (!isset($body['message']['text'])) {
    $body = $_REQUEST;
}
if (stripos($body['message']['text'], "/help") === 0) {
    $message = "This is a bot for the roleplaying game Arepo. And these are my commands you can use:\n\n";
    $message .= "*/help* : Get this info.\n\n";
    $message .= "*/roll 4* : Roll 4 six-sided dice, each 1 erases itself and the highest other die, and after that only the three highest dice get added together. This is a result between 0 and 18.";
}
if (stripos($body['message']['text'], "/roll") === 0) {
    preg_match("/^\/roll\s+(\d+)/", $body['message']['text'], $matches);
    $dice = $matches[1];
    if ($dice) {
        $result = roll($dice);
        if ($dice <= 1000) {
            $message = $body['message']['from']['first_name'] . " rolled: \n" . implode(" + ", $result['rolled']) . " => *" . $result['result'] . "*";
        } else {
            $message = $body['message']['from']['first_name'] . " rolled: *" . $result['result'] . "*";
        }
    }
}
if ($message !== null) {
    $message = array(
        'method' => "sendMessage",
        'chat_id' => $body['message']['chat']['id'],
        'text' => $message,
        'parse_mode' => "Markdown"
    );
    $message = json_encode($message);
    header("Status-Code: 200");
    header("Version: HTTP/1.1");
    header("Content-Type: application/json");
    echo $message;
    die();
}
