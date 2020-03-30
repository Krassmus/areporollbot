<?php

$pdo = false;
$apikey = null;
$debug = null;

if (file_exists(__DIR__."/config.php")) {
    include __DIR__."/config.php";
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
$reply_markup  = null;

$body = json_decode(file_get_contents("php://input"), true);
if (!isset($body['message']['text'])) {
    $body = $_REQUEST;
}
if ($pdo && isset($body['message']['chat']['type']) && $body['message']['chat']['type'] === "private") {
    //this is a private chat, save the id:
    $statement = $pdo->prepare("
        INSERT IGNORE INTO privatechats
        SET player_id = :player_id,
            chat_id = :chat_id
        ON DUPLICATE KEY UPDATE
            chat_id = :chat_id
    ");
    $statement->execute([
        'chat_id' => $body['message']['chat']['id'],
        'player_id' => $body['message']['from']['id']
    ]);
}
if ($pdo && isset($body['message']['chat']['type']) && $body['message']['chat']['type'] !== "private") {
    //this is a group chat, save the title/name:
    $statement = $pdo->prepare("
        INSERT IGNORE INTO groupchats
        SET chat_id = :chat_id,
            title = :title
        ON DUPLICATE KEY UPDATE
            title = :title
    ");
    $statement->execute([
        'chat_id' => $body['message']['chat']['id'],
        'title' => $body['message']['chat']['title']
    ]);
}
if (stripos($body['message']['text'], "/help") === 0) {
    //displays a help-message:
    $message = "This is a bot for the roleplaying game Arepo. And these are my commands you can use:\n";
    $message .= "*/help* : Get this info.\n";
    $message .= "*/roll 4* : Roll 4 six-sided dice, each 1 erases itself and the highest other die, and after that only the three highest dice get added together. This is a result between 0 and 18.\n";
    if ($pdo) {
        $message .= "*/mycards* : I will you write your cards in a private chat. But you need to start the private chat first by writing me a private message.\n";
        $message .= "*/drawcard* : Draw a card. I will write you which card you got in a private channel. Please write me first.\n";
        $message .= "*/undrawcard* : Undo the last drawing of a card. Sometimes a player drew a card by mistake. This can be undone by this command.\n";
        $message .= "*/playcard CardName* : If you own this card, you can play it in the group chat.\n";
    }
    $message .= "*/simulate* : /simulate 4 would roll thousand of times 4 dice and calculate the propabilities for each result from 0 - 18. Or by /simulate 4 versus 5 you get the probabilities that 4 dice would win against 5 dice.\n";
}
if (stripos($body['message']['text'], "/roll") === 0) {
    //rolls dice in the arepo way:
    preg_match("/^\/roll\s+(\d+)/", $body['message']['text'], $matches);
    $dice = $matches[1];
    if ($dice) {
        $rand = rand(1, 40);
        if ($rand === 1) {
            $message = "Oh, ".$body['message']['from']['first_name']." carled the dice under the couch.";
        } else {
            $result = roll($dice);
            if ($dice <= 1000) {
                $message = $body['message']['from']['first_name'] . " rolled: \n" . implode(" + ", $result['rolled']) . " => *" . $result['result'] . "*";
            } else {
                $message = $body['message']['from']['first_name'] . " rolled: *" . $result['result'] . "*";
            }
            $reply_markup = [
                'remove_keyboard' => true
            ];
        }
    } else {
        //display inline keyboard
        $message = "@".$body['message']['from']['username']." , how many dice should I roll?";
        $reply_markup = [
            'keyboard' => [
                ["/roll 3", "/roll 4", "/roll 5"],
                ["/roll 6", "/roll 7", "/roll 8"],
                ["/roll 9", "/roll 10", "/roll 11"],
                ["/roll 12", "/roll 13", "/roll 14"]
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
            'selective' => true
        ];
    }
}
if (stripos($body['message']['text'], "/mycards") === 0) {
    //displays my own cards in a private chat:
    if (!$pdo) {
        $message = "Sorry! It's no database connected. You have no cards.";
    } else {
        if ($body['message']['chat']['type'] !== "private") {
            $statement = $pdo->prepare("
                SELECT cards.*, COUNT(*) AS number
                FROM playercards
                    INNER JOIN cards ON (cards.card_id = playercards.card_id)
                WHERE chat_id = :chat_id
                    AND player_id = :player_id
                GROUP BY cards.card_id
                ORDER BY cards.name ASC
            ");
            $statement->execute([
                'chat_id' => $body['message']['chat']['id'],
                'player_id' => $body['message']['from']['id']
            ]);
            $cards = [];
            foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $card) {
                $cardtext = "*";
                if ($card['number'] > 1) {
                    $cardtext .= $card['number']." x ";
                }
                $cardtext .= $card['name']."*: ".$card['description'];
                $cards[] = $cardtext;
            }
            if (count($cards)) {
                $directmessage = "*Your cards in ".$body['message']['chat']['title']."*:\n";
                $directmessage .= implode("\n", $cards);
            } else {
                $directmessage = "Bad karma! You have no cards in *".$body['message']['chat']['title']."*.";
            }
        } else {
            $statement = $pdo->prepare("
                SELECT playercards.chat_id, groupchats.title
                FROM playercards
                    LEFT JOIN groupchats ON (groupchats.chat_id = playercards.chat_id)
                WHERE playercards.player_id = :player_id
                GROUP BY playercards.chat_id
                ORDER BY MAX(playercards.mkdate) DESC
            ");
            $statement->execute([
                'player_id' => $body['message']['from']['id']
            ]);
            $message = "";
            foreach ($statement->fetchAll() as $chat) {
                $message .= "*".($chat['title'] ?: "Untitled group")."*\n";
                $statement = $pdo->prepare("
                    SELECT cards.*, COUNT(*) AS number
                    FROM playercards
                        INNER JOIN cards ON (cards.card_id = playercards.card_id)
                    WHERE chat_id = :chat_id
                        AND player_id = :player_id
                    GROUP BY cards.card_id
                    ORDER BY cards.name ASC
                ");
                $statement->execute([
                    'chat_id' => $chat['chat_id'],
                    'player_id' => $body['message']['from']['id']
                ]);
                $cards = [];
                foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $card) {
                    $cardtext = "*";
                    if ($card['number'] > 1) {
                        $cardtext .= $card['number']." x ";
                    }
                    $cardtext .= $card['name']."*: ".$card['description'];
                    $cards[] = $cardtext;
                }
                $message .= implode("\n", $cards)."\n\n";
            }
            if (!$message) {
                $message = "Bad karma! You have no cards.";
            }
        }
    }
}
if (stripos($body['message']['text'], "/drawcard") === 0) {
    if (!$pdo) {
        $message = "Sorry! It's no database connected.";
    } else {
        if ($body['message']['chat']['type'] !== "private") {
            $purecards = $pdo->query("
                SELECT * FROM cards
            ")->fetchAll(PDO::FETCH_ASSOC);
            $cards = [];
            foreach ($purecards as $card) {
                for ($i = 1; $i <= $card['times']; $i++) {
                    $cards[] = $card;
                }
            }
            $rand = rand(0, count($cards) - 1);
            $card = $cards[$rand];
            $statement = $pdo->prepare("
                INSERT INTO playercards
                SET player_id = :player_id,
                    card_id = :card_id,
                    chat_id = :chat_id,
                    mkdate = UNIX_TIMESTAMP()
            ");
            $statement->execute([
                'chat_id' => $body['message']['chat']['id'],
                'player_id' => $body['message']['from']['id'],
                'card_id' => $card['card_id']
            ]);
            $directmessage = "You just drew:\n\n";
            $directmessage .= "*".$card['name']."* : ".$card['description']."\n\n";
            $directmessage .= "In the group chat type */play ".$card['name']."* to reveal and play this card.";

            $message = $body['message']['from']['first_name'] . " draws a card.";
        } else {
            $message = "You can only draw cards in a group chat.";
        }
    }
}
if (stripos($body['message']['text'], "/undrawcard") === 0) {
    if (!$pdo) {
        $message = "Sorry! It's no database connected.";
    } else {
        if ($body['message']['chat']['type'] !== "private") {
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
        } else {
            $message = "This command is only available in group chats.";
        }
    }
}
if (stripos($body['message']['text'], "/play") === 0) {
    if (!$pdo) {
        $message = "Sorry! It's no database connected.";
    } else {
        if ($body['message']['chat']['type'] !== "private") {
            preg_match("/^\/play(card)?\s+(.+)/", $body['message']['text'], $matches);
            $cardname = $matches[2];
            if ($cardname) {
                $statement = $pdo->prepare("
                    SELECT cards.*
                    FROM playercards
                        INNER JOIN cards ON (cards.card_id = playercards.card_id)
                    WHERE playercards.chat_id = :chat_id
                        AND playercards.player_id = :player_id
                        AND cards.name = :cardname
                ");
                $statement->execute([
                    'cardname' => $cardname,
                    'player_id' => $body['message']['from']['id'],
                    'chat_id' => $body['message']['chat']['id']
                ]);
                $card = $statement->fetch(PDO::FETCH_ASSOC);
                if (!$card) {
                    $message = "Sorry, ".$body['message']['from']['first_name'].", but you don't have that card. Maybe you type /mycards and look what you got?";
                } else {
                    $message = $body['message']['from']['first_name']." plays *".$card['name']."*:\n";
                    $message .= $card['description'];

                    $statement = $pdo->prepare("
                        DELETE FROM playercards
                        WHERE chat_id = :chat_id
                            AND player_id = :player_id
                            AND card_id = :card_id
                        LIMIT 1
                    ");
                    $statement->execute([
                        'card_id' => $card['card_id'],
                        'player_id' => $body['message']['from']['id'],
                        'chat_id' => $body['message']['chat']['id']
                    ]);
                }
                $reply_markup = [
                    'remove_keyboard' => true
                ];
            } else {
                //ask for cardname
                //display inline keyboard
                $statement = $pdo->prepare("
                    SELECT cards.*, COUNT(*) AS number
                    FROM playercards
                        INNER JOIN cards ON (cards.card_id = playercards.card_id)
                    WHERE chat_id = :chat_id
                        AND player_id = :player_id
                    GROUP BY cards.card_id
                    ORDER BY cards.name ASC
                ");
                $statement->execute([
                    'chat_id' => $body['message']['chat']['id'],
                    'player_id' => $body['message']['from']['id']
                ]);
                $cards = $statement->fetchAll(PDO::FETCH_ASSOC);
                if (count($cards)) {
                    $message = "@".$body['message']['from']['username']." , what card do you want to play?";
                    $reply_markup = [
                        'keyboard' => [],
                        'resize_keyboard' => true,
                        'one_time_keyboard' => true,
                        'selective' => true
                    ];
                    while (count($cards)) {
                        $i = count($reply_markup['keyboard']);
                        if ($i === 0 || count($reply_markup['keyboard'][$i]) === 3) {
                            $reply_markup['keyboard'][] = [];
                            $i++;
                        }
                        $card = array_shift($cards);
                        $reply_markup['keyboard'][$i][] = "/play ".$card['name'];
                    }
                } else {
                    $message = "Sorry, ".$body['message']['from']['first_name']." , you have no cards to play.";
                }

            }
        } else {
            $message = "You can only play cards in a group chat and only if you have some.";
        }
    }
}
if (stripos($body['message']['text'], "/simulate") === 0) {
    preg_match("/^\/simulate\s+(\d+)(\s+\w+)?(\s+(\d+))?/", $body['message']['text'], $matches);
    $dice_a = $matches[1];
    $dice_b = $matches[4];
    $rounds = 10000;
    if ($dice_b) {
        $wins_a = 0;
        $wins_b = 0;
        for ($i = 0; $i < $rounds; $i++) {
            $result_a = roll($dice_a);
            $result_b = roll($dice_b);
            if ($result_a['result'] > $result_b['result']) {
                $wins_a++;
            }
            if ($result_a['result'] < $result_b['result']) {
                $wins_b++;
            }
        }
        $message = "Player A wins: ".round($wins_a / $rounds * 100, 2)."%\n";
        $message .= "Player B wins: ".round($wins_b / $rounds * 100, 2)."%";
    } else {
        $results = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];// 0 - 18
        for ($i = 0; $i < $rounds; $i++) {
            $result = roll($dice_a);
            $results[$result['result']]++;
        }
        $message = "Propabilities for ".$dice_a." rolled dice:\n";
        foreach ($results as $res => $rolled) {
            $message .= $res.": ".round($rolled / $rounds * 100, 2)."%\n";
        }
    }
}

//now send some messages:
if ($directmessage !== null) {
    $statement = $pdo->prepare("
        SELECT chat_id FROM privatechats WHERE player_id = ?
    ");
    $statement->execute([$body['message']['from']['id']]);
    $chat_id = $statement->fetch(PDO::FETCH_COLUMN, 0);
    if ($chat_id) {
        $directmessage = array(
            'chat_id' => $chat_id,
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
        curl_setopt($r, CURLOPT_FAILONERROR, true);
        $response = curl_exec($r);
        if ($response === false) {
            $message = curl_error($r);
        } else {
            $response = json_decode($response, true);
            if (!$response['ok']) {
                $message =  $response['description'];
            }
        }
        $debug = json_encode($directmessage);
    } else {
        $message = "Okay. But couldn't answer you. Write me a private message first.";
    }
}
if ($message !== null) {
    $message = array(
        'method' => "sendMessage",
        'chat_id' => $body['message']['chat']['id'],
        'text' => $message,
        'parse_mode' => "Markdown"
    );
    if ($reply_markup !== null) {
        $message['reply_markup'] = $reply_markup;
    }
    $message = json_encode($message);
    header("Status-Code: 200");
    header("Version: HTTP/1.1");
    header("Content-Type: application/json");
    echo $message;
}
if ($debug !== null) {
    file_put_contents(__DIR__."/debuglast.txt", $debug);
} else {
    @unlink(__DIR__."/debuglast.txt");
}
