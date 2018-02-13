<?php
// Get gym name.
$gym_name = trim(substr($update['message']['text'], 4));

// Write to log.
debug_log('SET gym name to ' . $gym_name);

// Private chat type.
if ($update['message']['chat']['type'] == 'private' || $update['callback_query']['message']['chat']['type'] == 'private') {
    // Update gym name in raid table.
    my_query(
        "
        UPDATE    raids
        SET       gym_name = '{$db->real_escape_string($gym_name)}'
          WHERE   user_id = {$update['message']['from']['id']}
        ORDER BY  id DESC LIMIT 1
        "
    );

    // Send the message.
    sendMessage($update['message']['chat']['id'], getTranslation('gym_name_updated'));

} else {
    if ($update['message']['reply_to_message']['text']) {

        $lines = explode(CR, $update['message']['reply_to_message']['text']);
        $last_line = array_pop($lines);
        $pos = strpos($last_line, 'ID = ');
        $id = intval(trim(substr($last_line, $pos + 5)));

        // Write to log.
        debug_log('Gym ID=' . $id . ' name=' . $gym_name);

        // Build query.
        $rs = my_query(
            "
            SELECT    COUNT(*)
            FROM      users
              WHERE   user_id = {$update['message']['from']['id']}
                AND   moderator = 1
            "
        );

        $row = $rs->fetch_row();


        if ($row[0]) {
            // Build query.
            my_query(
                "
                UPDATE    raids
                SET       gym_name = '{$db->real_escape_string($gym_name)}'
                  WHERE   id = {$id}
                "
            );

        } else {
            // Build query.
            my_query(
                "
                UPDATE    raids
                SET       gym_name = '{$db->real_escape_string($gym_name)}'
                  WHERE   id = {$id}
                    AND   user_id = {$update['message']['from']['id']}
                "
            );
        }

        $rs = my_query(
            "
            SELECT  *,
                    UNIX_TIMESTAMP(end_time)                        AS ts_end,
                    UNIX_TIMESTAMP(NOW())                           AS ts_now,
                    UNIX_TIMESTAMP(end_time)-UNIX_TIMESTAMP(NOW())  AS t_left
            FROM    raids
              WHERE id = {$id}
            "
        );

        $raid = $rs->fetch_assoc();

        $text = show_raid_poll($raid);
        $keys = keys_vote($raid);

        editMessageText($update['message']['reply_to_message']['message_id'], $text, $keys, $update['message']['chat']['id']);
    }
}

exit;
