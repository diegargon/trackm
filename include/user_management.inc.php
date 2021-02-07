<?php

/**
 *
 *  @author diego@envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 Diego Garcia (diego@envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function user_management() {
    global $LNG, $filter, $db, $cfg, $user;

    $status_msg = $LNG['L_USERS_MNGT_HELP'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user['isAdmin']) {
        if (isset($_POST['new_user']) && !empty($new_user = $filter->postUsername('username'))) {
            if ($cfg['force_use_passwords']) {
                if (!empty($password = $filter->postPassword('password'))) {
                    $user_create['username'] = $new_user;
                    $user_create['password'] = encrypt_password($password);
                    $_POST['is_admin'] == 1 ? $user_create['isAdmin'] = 1 : $user_create['isAdmin'] = 0;
                    $_POST['disable'] == 1 ? $user_create['disable'] = 1 : $user_create['disable'] = 0;
                    $_POST['hide_login'] == 1 ? $user_create['hide_login'] = 1 : $user_create['hide_login'] = 0;
                    $db->upsertItemByField('users', $user_create, 'username');
                    $status_msg = $LNG['L_USER_CREATE_SUCCESS'];
                } else {
                    $status_msg = $LNG['L_USER_INCORRECT_PASSWORD'];
                }
            } else {
                if (!empty($password = $filter->postPassword('password'))) {
                    $user_create['password'] = encrypt_password($password);
                }
                $_POST['is_admin'] == 1 ? $user_create['isAdmin'] = 1 : $user_create['isAdmin'] = 0;
                $_POST['disable'] == 1 ? $user_create['disable'] = 1 : $user_create['disable'] = 0;
                $_POST['hide_login'] == 1 ? $user_create['hide_login'] = 1 : $user_create['hide_login'] = 0;
                $user_create['username'] = $new_user;
                $db->upsertItemByField('users', $user_create, 'username');
                $status_msg = $LNG['L_USER_CREATE_SUCCESS'];
            }
        } else if (isset($_POST['delete_user']) && !empty($delete_user_id = $filter->postInt('delete_user_id'))) {
            $db->delete('users', ['id' => ['value' => $delete_user_id]]);
            $status_msg = $LNG['L_USER_DELETED'];
        } else {
            $status_msg = $LNG['L_USER_INCORRECT_USERNAME'];
        }
    }

    $html['title'] = $LNG['L_USERS_MANAGEMENT'];
    $html['content'] = new_user();
    $html['content'] .= show_users();
    $html['content'] .= '<p>' . $status_msg . '</p>';

    return $html;
}

function new_user() {
    global $LNG;

    $html = '<div class="new_user_box">';
    $html .= '<form id="new_user" method="POST" >';
    $html .= '<span>' . $LNG['L_USERNAME'] . '<span><input size="8" type="text" name="username" value=""/>';
    $html .= '<span>' . $LNG['L_PASSWORD'] . '<span><input size="8" type="password" name="password" value=""/>';
    //Admin
    $html .= '<input type="hidden" name="is_admin" value="0">';
    $html .= '<label for="is_admin">' . $LNG['L_ADMIN'] . ' </label>';
    $html .= '<input id="is_admin" type="checkbox" name="is_admin" value="1">';
    //disable
    $html .= '<input type="hidden" name="disable" value = "0">';
    $html .= '<label for="disable">' . $LNG['L_DISABLED'] . ' </label>';
    $html .= '<input id="disable" type="checkbox" name="disable" value="1">';
    //hide login
    $html .= '<input type="hidden" name="hide_login" value="0">';
    $html .= '<label for="hide_login">' . $LNG['L_HIDE_LOGIN'] . ' </label>';
    $html .= '<input id="hide_login" type="checkbox" name="hide_login" value="1">';

    //Submit
    $html .= '<input class="submit_btn" type="submit" name="new_user" value="' . $LNG['L_CREATE'] . '/' . $LNG['L_MODIFY'] . '"/>';
    $html .= '</form>';
    $html .= '</div>';

    return $html;
}

function show_users() {
    global $LNG;

    $html = '<div class="delete_user_box">';
    $html .= '<form id = "delete_user" method = "POST">';
    $users = get_profiles();
    foreach ($users as $user) {
        if ($user['id'] > 1) {
            $html .= '<div class="delete_user"><input type="hidden" name="delete_user_id" value="' . $user['id'] . '"/>';
            $html .= '<input class="submit_btn" onclick="return confirm(\'Are you sure?\')" type="submit" name="delete_user" value="' . $LNG['L_DELETE'] . '"/>';
            $html .= '<span>' . $user['username'] . '<span></div>';
        }
    }
    $html .= '</form>';
    $html .= '</div>';

    return $html;
}

function encrypt_password($password) {

    return sha1($password);
}

function user_edit_profile() {
    global $LNG;

    $html = '<form method="POST" action="">';

    $html .= '<span>' . $LNG['L_PASSWORD'] . '<span><input size="8" type="text" name= "cur_password" value=""/>';
    $html .= '<span>' . $LNG['L_NEW_PASSWORD'] . '<span><input size="8" type="text" name= "new_password" value=""/>';
    $html .= '</form>';

    return $html . '<br/>';
}

function user_change_password() {
    global $cfg, $user, $LNG, $filter, $db;

    if (isset($_POST['cur_password'])) {
        !empty($filter->postPassword('cur_password')) ? $cur_password = $filter->postPassword('cur_password') : $cur_password = '';
    }
    if (isset($_POST['new_password'])) {
        !empty($filter->postPassword('new_password')) ? $new_password = $filter->postPassword('new_password') : $new_password = '';
    }

    if ($cfg['force_use_passwords'] && empty($new_password)) {
        return $LNG['L_PASSWORD_CANT_EMPTY'];
    }

    if (!empty($user['password']) && empty($cur_password)) {
        return $LNG['L_PASSWORD_INCORRECT'];
    }

    if (empty($user['password']) && empty($new_password)) {
        return $LNG['L_PASSWORD_EQUAL'];
    }
    if (!empty($user['password']) && (encrypt_password($new_password) == $user['password'])) {
        return $LNG['L_PASSWORD_EQUAL'];
    }
    if (empty($user['password']) && empty($cur_password) && !empty($new_password)) {
        !empty($new_password) ? $new_encrypted_password = encrypt_password($new_password) : $new_encrypted_password = '';
        $db->updateItemById('users', $user['id'], ['password' => $new_encrypted_password]);
        return $LNG['L_PASSWORD_CHANGE_SUCESS'];
    }
    if (!empty($user['password'])) {
        $old_encrypted_password = encrypt_password($cur_password);
        if ($user['password'] == $old_encrypted_password) {
            !empty($new_password) ? $new_encrypted_password = encrypt_password($new_password) : $new_encrypted_password = '';
            $db->updateItemById('users', $user['id'], ['password' => $new_encrypted_password]);
            return $LNG['L_PASSWORD_CHANGE_SUCESS'];
        } else {
            return $LNG['L_PASSWORD_INCORRECT'];
        }
    }

    return $LNG['L_PASSWORD_UNKNOWN_ERROR'];
}
