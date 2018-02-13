<?php
// Write to log.
debug_log('OVERVIEW_REFRESH');
debug_log($data);

// Get chat ID from data
$chat_id = 0;
$chat_id = $data['arg'];

// $update present?
if (isset($update['callback_query']['from']['id'])) {
    // Build query.
    $rs = my_query(
        "
        SELECT    timezone
        FROM      raids
          WHERE   id = (
                      SELECT    raid_id
                      FROM      attendance
                        WHERE   user_id = {$update['callback_query']['from']['id']}
                      ORDER BY  id DESC LIMIT 1
                  )
        "
    );

    // Get row.
    $row = $rs->fetch_assoc();

    // No data found.
    if (!$row) {
        //sendMessage($update['message']['from']['id'], 'Can\'t determine your location, please participate in at least 1 raid');
        //exit;
        $tz = TIMEZONE;
    } else {
        $tz = $row['timezone'];
    }
} else {
    $tz = TIMEZONE;
}

// Get active raids.
$request_active_raids = my_query(
    "
    SELECT    *,
              UNIX_TIMESTAMP(end_time)                        AS ts_end,
              UNIX_TIMESTAMP(start_time)                      AS ts_start,
              UNIX_TIMESTAMP(NOW())                           AS ts_now,
              UNIX_TIMESTAMP(end_time)-UNIX_TIMESTAMP(NOW())  AS t_left
    FROM      raids
      WHERE   end_time>NOW()
        AND   timezone='{$tz}'
    ORDER BY  end_time ASC
    "
);

// Count active raids.
$count_active_raids = 0;

// Init empty active raids and raid_ids array.
$raids_active = array();
$raid_ids_active = array();

// Get all active raids into array.
while ($rowRaids = $request_active_raids->fetch_assoc()) {
    // Use current raid_id as key for raids array
    $current_raid_id = $rowRaids['id'];
    $raids_active[$current_raid_id] = $rowRaids;

    // Build array with raid_ids to query cleanup table later
    $raid_ids_active[] = $rowRaids['id'];

    // Counter for active raids
    $count_active_raids = $count_active_raids + 1;
}

// Write to log.
debug_log('Active raids:');
debug_log($raids_active);

// Init empty active chats array.
$chats_active = array();

// Make sure we have active raids.
if ($count_active_raids > 0) {
    // Implode raid_id's of all active raids.
    $raid_ids_active = implode(',',$raid_ids_active);

    // Write to log.
    debug_log('IDs of active raids:');
    debug_log($raid_ids_active);

    // Get all or specific overview
    if ($chat_id == 0) {
        $request_overviews = my_query(
            "
            SELECT    chat_id
            FROM      overview
            "
        );
    } else {
        $request_overviews = my_query(
            "
            SELECT    chat_id
            FROM      overview
            WHERE     chat_id = '{$chat_id}'
            "
        );
    }

    while ($rowOverviews = $request_overviews->fetch_assoc()) {
        // Set chat_id.
        $chat_id = $rowOverviews['chat_id'];

        // Get chats. 
        $request_active_chats = my_query(
            "
            SELECT    *
            FROM      cleanup
              WHERE   raid_id IN ({$raid_ids_active})
	      AND     chat_id = '{$chat_id}'
              ORDER BY chat_id, FIELD(raid_id, {$raid_ids_active})
            "
        );

        // Get all chats.    
        while ($rowChats = $request_active_chats->fetch_assoc()) {
            $chats_active[] = $rowChats;
        }
    }
}

// Write to log.
debug_log('Active chats:');
debug_log($chats_active);

// Get raid overviews
get_overview($update, $chats_active, $raids_active, $action = 'refresh', $chat_id);
exit;
