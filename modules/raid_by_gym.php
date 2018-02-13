<?php
// Get Userid, chatid and chattype from message 
if (isset($update['message']['from']['id'])) {
    $userid = $update['message']['from']['id'];
}
if (isset($update['message']['chat']['id'])) {
    $chatid = $update['message']['chat']['id'];
}
if (isset($update['message']['chat']['type'])) {
    $chattype = $update['message']['chat']['type'];
}

// Get the userid, chat id and type
$id_type = $data['id'];
$first = $data['arg'];

// Back key id, action and arg
$back_id = $data['id'];
$back_action = "raid_by_gym_letter";
$back_arg = 0;

// Create data array (max. 2)
$userdata = explode(',', $id_type, 2);

// Set userid, chat id and type
$userid = $userdata[0];
$chatid = $userid;
$chattype = $userdata[1];

// Debug
debug_log('User ID=' . $userid);
debug_log('Chat type=' . $chatid);
debug_log('Chat type=' . $chattype);
debug_log('First letter=' . $first);

// Init id to 0
$id = 0;

// Get the keys.
$keys = raid_edit_gym_keys($chatid, $chattype, $first);

// No keys found.
if (!$keys) {
    // Create the keys.
    $keys = [
        [
            [
                'text'          => getTranslation('not_supported'),
                'callback_data' => 'edit:not_supported'
            ]
        ]
    ];
} else {
    $keys = back_key($keys, $back_id, $back_action, $back_arg);
}

// Edit the message.
edit_message($update, getTranslation('select_gym_name'), $keys);

// Build callback message string.
$callback_response = getTranslation('here_we_go');

// Answer callback.
answerCallbackQuery($update['callback_query']['id'], $callback_response);

exit();
