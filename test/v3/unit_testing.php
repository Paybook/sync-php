<?php















    _print(PHP_EOL.' -> ATTACHMENT ENDPOINTS '.PHP_EOL);

    print_step('Getting user attachments count');
    $attachments_count = paybook\Attachment::get_count($session);
    _print('Attachment count: '.strval($attachments_count), 2);

    print_step('Getting user attachments');
    $attachments = paybook\Attachment::get($session);
    _print('Attachments: '.strval(count($attachments)), 2);

    if ($attachments_count > 0) {
        print_step('Getting a user attachment');
        $url = $attachments[0]->url;
        $id_attachment = substr($url, 1, strlen($url));
        $attachment = paybook\Attachment::get($session, null, $id_attachment);
        _print('Attachment content substring: '.substr($attachment, 0, 8), 2);

        print_step('Getting a user attachment extra');
        $extra = paybook\Attachment::get($session, $id_user = null, $id_attachment = $id_attachment, $extra = true);
        _print('Extra attr mime: '.$extra['mime'], 2);
    }//End of if

    _print(PHP_EOL.' -> DELETING UNIT TESTING DATA '.PHP_EOL);

    print_step('Deletes the user created for unit testing');
    $user_deleted = paybook\User::delete($user->id_user);
    _print('User '.$user->name.' with id '.$user->id_user.' deleted '.strval($user_deleted), 2);

    print_step('Get user lists');
    $users = paybook\User::get();
    $users_final_count = count($users);
    if ($users_count == $users_final_count) {
        _print('Final users: '.strval($users_final_count), 2);
        _print('Everythings Ok. Unit testin data created was deleted.', 2);
    }//End of if

    _print(PHP_EOL.PHP_EOL);
    _print('Unit testing completed successfully with '.$step.' steps \\0/', 1);
    _print(PHP_EOL.PHP_EOL);
} catch (paybook\Error $e) {
    _print(PHP_EOL.PHP_EOL);
    _print('Unit testing uncompleted at step '.$step.' :(', 1);
    _print($e->get_code().' '.$e->get_message(), 1);
    _print(PHP_EOL.PHP_EOL);
}//End of try
