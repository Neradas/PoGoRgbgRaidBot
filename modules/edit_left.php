<?php
// Check raid access.
raid_access_check($update, $data);

// Write to log.
debug_log('raid_edit_left()');
debug_log($update);
debug_log($data);

// Set the id.
$id = $data['id'];

// Set the user id.
$userid = $update['callback_query']['from']['id'];

// Build query.
my_query(
    "
    UPDATE    raids
    SET       user_id = {$userid},
              end_time = DATE_ADD(start_time, INTERVAL {$data['arg']} MINUTE)
      WHERE   id = {$id}
    "
);

if ($update['message']['chat']['type'] == 'private' || $update['callback_query']['message']['chat']['type'] == 'private') {
    // Init keys.
    $keys = array();

    // Add delete to keys.
    $keys = [
        [
            [
                'text'          => getTranslation('delete'),
                'callback_data' => $id . ':raids_delete:0'
            ]
        ]
    ];

    // Add keys to share.
    $keys_share = share_keys($id, $userid);
    $keys = array_merge($keys, $keys_share);

    // Get raid times.
    $rs = my_query(
        "
        SELECT    *, 
                              UNIX_TIMESTAMP(start_time)                      AS ts_start,
                              UNIX_TIMESTAMP(end_time)                        AS ts_end,
                              UNIX_TIMESTAMP(NOW())                           AS ts_now,
                              UNIX_TIMESTAMP(end_time)-UNIX_TIMESTAMP(NOW())  AS t_left
                FROM      raids
                  WHERE   id = {$data['id']}
        "
    );

    // Fetch the row.
    $raid = $rs->fetch_assoc();

    // Build message string.
    $msg = '';
    $msg .= getTranslation('raid_saved') . CR;
    $msg .= show_raid_poll_small($raid) . CR;

    // Gym Name
    if(!empty($raid['gym_name'])) {
	$msg .= getTranslation('set_gym_team') . CR2;
    } else {
        $msg .= getTranslation('set_gym_name_and_team') . CR2;
        $msg .= getTranslation('set_gym_name_command') . CR;
    }
    $msg .= getTranslation('set_gym_team_command');

    // Edit message.
    edit_message($update, $msg, $keys, false);

    // Build callback message string.
    $callback_response = getTranslation('end_time') . $data['arg'] . ' ' . getTranslation('minutes');

    // Answer callback.
    answerCallbackQuery($update['callback_query']['id'], $callback_response);

} else {
    // Get raid times.
    $rs = my_query(
        "
        SELECT    *,
			      UNIX_TIMESTAMP(start_time)                      AS ts_start,
			      UNIX_TIMESTAMP(end_time)                        AS ts_end,
			      UNIX_TIMESTAMP(NOW())                           AS ts_now,
			      UNIX_TIMESTAMP(end_time)-UNIX_TIMESTAMP(NOW())  AS t_left
		FROM      raids
		  WHERE   id = {$data['id']}
        "
    );

    // Fetch the row.
    $raid = $rs->fetch_assoc();

    // Get text and keys.
    $text = show_raid_poll($raid);
    $keys = keys_vote($raid);

    // Edit message.
    edit_message($update, $text, $keys, false);

    // Build callback message string.
    $callback_response = 'End time set to ' . $data['arg'] . ' minutes';

    // Answer callback.
    answerCallbackQuery($update['callback_query']['id'], $callback_response);
}

