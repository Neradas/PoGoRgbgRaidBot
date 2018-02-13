<?php
/**
 * Send message.
 * @param $chat_id
 * @param array $text
 */
function sendMessage($chat_id, $text = array())
{
    // Create response content array.
    $reply_content = [
        'method'     => 'sendMessage',
        'chat_id'    => $chat_id,
        'parse_mode' => 'HTML',
        'text'       => $text
    ];

    if (isset($inline_keyboard)) {
        $reply_content['reply_markup'] = ['inline_keyboard' => $inline_keyboard];
    }

    // Encode data to json.
    $reply_json = json_encode($reply_content);

    // Set header to json.
    header('Content-Type: application/json');

    // Write to log.
    debug_log($reply_json, '>');

    // Send request to telegram api.
    curl_json_request($reply_json);
}

/**
 * Send message.
 * @param $chat_id
 * @param array $text
 * @param mixed $inline_keyboard
 * @param array $merge_args
 */
function send_message($chat_id, $text = array(), $inline_keyboard = false, $merge_args = [])
{
    // Create response content array.
    $reply_content = [
        'method'     => 'sendMessage',
        'chat_id'    => $chat_id,
        'parse_mode' => 'HTML',
        'text'       => $text
    ];

    // Write to log.
    debug_log('KEYS');
    debug_log($inline_keyboard);

    if (isset($inline_keyboard)) {
        $reply_content['reply_markup'] = ['inline_keyboard' => $inline_keyboard];
    }

    if (is_array($merge_args) && count($merge_args)) {
        $reply_content = array_merge_recursive($reply_content, $merge_args);
    }

    // Encode data to json.
    $reply_json = json_encode($reply_content);

    // Set header to json.
    header('Content-Type: application/json');

    // Write to log.
    debug_log($reply_json, '>');

    // Send request to telegram api.
    return curl_json_request($reply_json);
}

/**
 * Send location.
 * @param $chat_id
 * @param $lat
 * @param $lon
 * @param bool $inline_keyboard
 * @return mixed
 */
function send_location($chat_id, $lat, $lon, $inline_keyboard = false)
{
    // Create reply content array.
    $reply_content = [
        'method'    => 'sendLocation',
        'chat_id'   => $chat_id,
        'latitude'  => $lat,
        'longitude' => $lon
    ];

    // Write to log.
    debug_log('KEYS');
    debug_log($inline_keyboard);

    if (is_array($inline_keyboard)) {
        $reply_content['reply_markup'] = ['inline_keyboard' => $inline_keyboard];
    }

    // Encode data to json.
    $reply_json = json_encode($reply_content);

    // Set header to json.
    header('Content-Type: application/json');

    // Write to log.
    debug_log($reply_json, '>');

    // Send request to telegram api and return response.
    return curl_json_request($reply_json);
}

/**
 * Echo message.
 * @param $chat_id
 * @param $text
 */
function sendMessageEcho($chat_id, $text)
{
    // Create reply content array.
    $reply_content = [
        'method'     => 'sendMessage',
        'chat_id'    => $chat_id,
        'parse_mode' => 'HTML',
        'text'       => $text
    ];

    // Encode data to json.
    $reply_json = json_encode($reply_content);

    // Set header to json.
    header('Content-Type: application/json');

    // Write to log.
    debug_log($reply_json, '>');

    // Echo json.
    echo($reply_json);
}

/**
 * Answer callback query.
 * @param $query_id
 * @param $text
 */
function answerCallbackQuery($query_id, $text)
{
    // Create response array.
    $response = [
        'method'            => 'answerCallbackQuery',
        'callback_query_id' => $query_id,
        'text'              => $text
    ];

    // Encode response to json format.
    $json_response = json_encode($response);

    // Set header to json.
    header('Content-Type: application/json');

    // Write to log.
    debug_log($json_response, '>');

    // Send request to telegram api.
    curl_json_request($json_response);
}

/**
 * Answer inline query.
 * @param $query_id
 * @param $contents
 */
function answerInlineQuery($query_id, $contents)
{
    // Init empty result array.
    $results = array();

    // For each content.
    foreach ($contents as $key => $row) {
        // Get raid poll.
        $text = show_raid_poll($row);

        // Get inline keyboard.
        $inline_keyboard = keys_vote($row);

        // Create input message content array.
        $input_message_content = [
            'parse_mode'                => 'HTML',
            'message_text'              => $text,
            'disable_web_page_preview'  => true
        ];

        // Fill results array.
        $results[] = [
            'type'                  => 'article',
            'id'                    => $query_id . $key,
            'title'                 => ucfirst($row['pokemon']) . ' ' . unix2tz($row['ts_end'], $row['timezone']),
            'description'           => strval($row['gym_name']),
            'input_message_content' => $input_message_content,
            'reply_markup'          => [
                'inline_keyboard' => $inline_keyboard
            ]
        ];
    }

    // Create reply content array.
    $reply_content = [
        'method'          => 'answerInlineQuery',
        'inline_query_id' => $query_id,
        'is_personal'     => true,
        'cache_time'      => 10,
        'results'         => $results
    ];

    // Encode to json and send request to telegram api.
    curl_json_request(json_encode($reply_content));
}

/**
 * Edit message text.
 * @param $id_val
 * @param $text_val
 * @param $markup_val
 * @param null $chat_id
 * @param mixed $merge_args
 */
function editMessageText($id_val, $text_val, $markup_val, $chat_id = NULL, $merge_args = false)
{
    // Create response array.
    $response = [
        'method'        => 'editMessageText',
        'text'          => $text_val,
        'parse_mode'    => 'HTML',
        'reply_markup'  => [
            'inline_keyboard' => $markup_val
        ]
    ];

    if ($markup_val == false) {
        unset($response['reply_markup']);
        $response['remove_keyboard'] = true;
    }

    // Valid chat id.
    if ($chat_id != null) {
        $response['chat_id']    = $chat_id;
        $response['message_id'] = $id_val;
    } else {
        $response['inline_message_id'] = $id_val;
    }

    // Write to log.
    debug_log($merge_args, 'K');
    debug_log($response, 'K');

    if (is_array($merge_args) && count($merge_args)) {
        $response = array_merge_recursive($response, $merge_args);
    }

    // Write to log.
    debug_log($response, 'K');

    // Encode response to json format.
    $json_response = json_encode($response);

    // Write to log.
    debug_log($response, '<-');

    // Send request to telegram api.
    curl_json_request($json_response);
}

/**
 * Edit message reply markup.
 * @param $id_val
 * @param $markup_val
 * @param $chat_id
 */
function editMessageReplyMarkup($id_val, $markup_val, $chat_id)
{
    // Create response array.
    $response = [
        'method' => 'editMessageReplyMarkup',
        'reply_markup' => [
            'inline_keyboard' => $markup_val
        ]
    ];

    // Valid chat id.
    if ($chat_id != null) {
        $response['chat_id'] = $chat_id;
        $response['message_id'] = $id_val;

    } else {
        $response['inline_message_id'] = $id_val;
    }

    // Encode response to json format.
    $json_response = json_encode($response);

    // Write to log.
    debug_log($response, '->');

    // Send request to telegram api.
    curl_json_request($json_response);
}

/**
 * Edit message keyboard.
 * @param $id_val
 * @param $markup_val
 * @param $chat_id
 */
function edit_message_keyboard($id_val, $markup_val, $chat_id)
{
    // Create response array.
    $response = [
        'method' => 'editMessageReplyMarkup',
        'reply_markup' => [
            'inline_keyboard' => $markup_val
        ]
    ];

    // Valid chat id.
    if ($chat_id != null) {
        $response['chat_id'] = $chat_id;
        $response['message_id'] = $id_val;

    } else {
        $response['inline_message_id'] = $id_val;
    }

    // Encode response to json format.
    $json_response = json_encode($response);

    // Write to log.
    debug_log($response, '->');

    // Send request to telegram api.
    curl_json_request($json_response);
}

/**
 * Edit message.
 * @param $update
 * @param $message
 * @param $keys
 * @param bool $merge_args
 */
function edit_message($update, $message, $keys, $merge_args = false)
{
    if (isset($update['callback_query']['inline_message_id'])) {
        editMessageText($update['callback_query']['inline_message_id'], $message, $keys, NULL, $merge_args);
    } else {
        editMessageText($update['callback_query']['message']['message_id'], $message, $keys, $update['callback_query']['message']['chat']['id'], $merge_args);
    }
}

/**
 * Delete message
 * @param $chat_id
 * @param $message_id
 */
function delete_message($chat_id, $message_id)
{
    // Create response content array.
    $reply_content = [
        'method'     => 'deleteMessage',
        'chat_id'    => $chat_id,
        'message_id' => $message_id,
        'parse_mode' => 'HTML',
    ];

    // Encode data to json.
    $reply_json = json_encode($reply_content);

    // Set header to json.
    header('Content-Type: application/json');

    // Write to log.
    debug_log($reply_json, '>');

    // Send request to telegram api.
    return curl_json_request($reply_json);
}

/**
 * GetChat
 * @param $chatid
 */
function get_chat($chat_id)
{
    // Create response content array.
    $reply_content = [
        'method'     => 'getChat',
        'chat_id'    => $chat_id,
        'parse_mode' => 'HTML',
    ];

    // Encode data to json.
    $reply_json = json_encode($reply_content);

    // Set header to json.
    header('Content-Type: application/json');

    // Write to log.
    debug_log($reply_json, '>');

    // Send request to telegram api.
    return curl_json_request($reply_json);
}

/**
 * GetChatAdministrators
 * @param $chatid
 */
function get_admins($chat_id)
{
    // Create response content array.
    $reply_content = [
        'method'     => 'getChatAdministrators',
        'chat_id'    => $chat_id,
        'parse_mode' => 'HTML',
    ];

    // Encode data to json.
    $reply_json = json_encode($reply_content);

    // Set header to json.
    header('Content-Type: application/json');

    // Write to log.
    debug_log($reply_json, '>');

    // Send request to telegram api.
    return curl_json_request($reply_json);
}

/**
 * Send request to telegram api.
 * @param $json
 * @return mixed
 */
function curl_json_request($json)
{
    $curl = curl_init('https://api.telegram.org/bot' . API_KEY . '/');

    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $json);

    // Use Proxyserver for curl if configured
    if (CURL_USEPROXY == true) {
    	curl_setopt($curl, CURLOPT_PROXY, CURL_PROXYSERVER);
    }

    // Write to log.
    debug_log($json, '->');

    // Execute curl request.
    $json_response = curl_exec($curl);

    // Write to log.
    debug_log($json_response, '<-');

    // Decode json response.
    $response = json_decode($json_response, true);

    // Validate response.
    if ($response['ok'] != true || isset($response['update_id'])) {
        // Write error to log.
        debug_log('ERROR: ' . $json . "\n\n" . $json_response . "\n\n");
    } else {
	// Result seems ok, get message_id and chat_id if supergroup or channel message
	if ($response['result']['chat']['type'] == "channel" || $response['result']['chat']['type'] == "supergroup") {
            // Init raid_id
            $raid_id = 0;

	    // Set chat and message_id
            $chat_id = $response['result']['chat']['id'];
            $message_id = $response['result']['message_id'];

            // Get raid id from $json
            $json_message = json_decode($json, true);

            // Write to log that message was shared with channel or supergroup
            debug_log('Message was shared with ' . $response['result']['chat']['type'] . ' ' . $response['result']['chat']['title']);
            debug_log('Checking input for cleanup info now...');

	    // Check if callback_data is present to get the raid_id and reply_to_message_id is set to filter only raid messages
            if (!empty($json_message['reply_markup']['inline_keyboard']['0']['0']['callback_data']) && !empty($json_message['reply_to_message_id'])) {
                $split_callback_data = explode(':', $json_message['reply_markup']['inline_keyboard']['0']['0']['callback_data']);
                $raid_id = $split_callback_data[0];
                debug_log('Found Raid_ID for cleanup preparation from callback_data!');
                debug_log('Raid_ID: ' . $raid_id);
                debug_log('Chat_ID: ' . $chat_id);
                debug_log('Message_ID: ' . $message_id);

	        // Trigger cleanup preparation process when necessary id's are not empty and numeric
	        if (!empty($chat_id) && !empty($message_id) && !empty($raid_id)) {
		    debug_log('Calling cleanup preparation now!');
		    insert_cleanup($chat_id, $message_id, $raid_id);
	        } else {
		    debug_log('Missing input! Cannot call cleanup preparation!');
		}
            } else {
                debug_log('No cleanup info found! Skipping cleanup preparation!');
            }

            // Check if text starts with getTranslation('raid_overview_for_chat') and inline keyboard is empty
            $translation = getTranslation('raid_overview_for_chat');
            $translation_length = strlen($translation);
            $text = substr($response['result']['text'], 0, $translation_length);
            if ($text == $translation && empty($json_message['reply_markup']['inline_keyboard'])) {
                debug_log('Detected overview message!');
                debug_log('Chat_ID: ' . $chat_id);
                debug_log('Message_ID: ' . $message_id);

                // Write raid overview data to database
                debug_log('Adding overview info to database now!');
                insert_overview($chat_id, $message_id);
            }
	}
    }

    // Return response.
    return $response;
}

/**
 * Gets a table translation out of the json file.
 * @param $text
 * @return translation
 */
function getTranslation($text)
{
	debug_log($text);
	
	$str = file_get_contents('./language.json');
	
	$json = json_decode($str, true);
	$translation = $json[$text][LANGUAGE];	
	
	return $translation;
}
