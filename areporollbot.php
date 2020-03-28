<?php

$pdo = false;
$apikey = null;

if (file_exists(__DIR__."/config.php")) {
    include file_exists(__DIR__."/config.php");
    //now we possibly have a $pdo object with a mysql database and a $apikey;
}

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

$message       = null;
$directmessage = null;

$body = json_decode(file_get_contents("php://input"), true);
if (!isset($body['message']['text'])) {
    $body = $_REQUEST;
}
if (stripos($body['message']['text'], "/help") === 0) {
    //displays a help-message:
    $message = "This is a bot for the roleplaying game Arepo. And these are my commands you can use:\n\n";
    $message .= "*/help* : Get this info.\n\n";
    $message .= "*/roll 4* : Roll 4 six-sided dice, each 1 erases itself and the highest other die, and after that only the three highest dice get added together. This is a result between 0 and 18.";
}
if (stripos($body['message']['text'], "/roll") === 0) {
    //rolls dice in the arepo way:
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
if (stripos($body['message']['text'], "/mycards") === 0) {
    //displays my own cards in a private chat:
    if (!$pdo) {
        $message = "Sorry! It's no database connected. You have no cards.";
    } else {
        $statement = $pdo->prepare("
            SELECT cards.*, COUNT(*) AS number
            FROM playercards
                INNER JOIN cards ON (cards.card_id = playercards.card_id)
            WHERE chat_id = :chat_id
                AND player_id = :player_id
            GROUP BY cards.card_id
        ");
        $statement->execute([
            'chat_id' => $body['message']['chat']['id'],
            'player_id' => $body['message']['user']['id']
        ]);
        $cards = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $card) {
            $cardtext = "*";
            if ($card['number'] > 1) {
                $cardtext .= $card['number']." x ";
            }
            $cardtext .= $card['name']."*:".$card['description'];
            $cards[] = $cardtext;
        }
        if (count($cards)) {
            $directmessage = implode("\n", $cards);
        } else {
            $directmessage = "Bad karma! You have no cards.";
        }
    }
}
if (stripos($body['message']['text'], "/undrawcard") === 0) {
    if (!$pdo) {
        $message = "Sorry! It's no database connected.";
    } else {
        $statement = $pdo->prepare("
            DELETE
            FROM playercards
            WHERE chat_id = :chat_id
            ORDER BY mkdate DESC
            LIMIT 1
        ");
        $statement->execute([
            'chat_id' => $body['message']['chat']['id']
        ]);
        $success = $statement->rowCount();
        if ($success) {
            $message = "Alright, this is undone.";
        } else {
            $message = "There were no cards in play. Nothing to undraw.";
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
if ($directmessage !== null) {
    $directmessage = array(
        'chat_id' => "@channel".$body['message']['user']['username'],
        'text' => $directmessage,
        'parse_mode' => "Markdown"
    );
    $url = "https://api.telegram.org/bot".$apikey."/sendMessage";

    $r = curl_init();
    $header = ["Content-Type: application/json"];
    curl_setopt($r, CURLOPT_URL, "https://api.telegram.org/bot".$apikey."/sendMessage");
    curl_setopt($r, CURLOPT_POST, true);
    curl_setopt($r, CURLOPT_HTTPHEADER, $header);
    curl_setopt($r, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($r, CURLOPT_POSTFIELDS, json_encode($directmessage));

    curl_exec($r);
    die();
}
