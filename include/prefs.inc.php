<?php

/**
 *
 *  @author diego/@/envigo.net
 *  @package
 *  @subpackage
 *  @copyright Copyright @ 2020 - 2021 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

function loadUserPrefs() {
    global $cfg, $db, $user;

    if (empty($user) || empty($user['id'])) {
        return false;
    }
    $where['uid'] = ['value' => $user['id']];
    $results = $db->select('preferences', null, $where);

    if (($user_prefs = $db->fetchAll($results))) {
        foreach ($user_prefs as $pref) {
            if (!empty($pref['pref_name']) && isset($pref['pref_value'])) {
                $cfg[$pref['pref_name']] = $pref['pref_value'];
            }
        }
    }
}

function getPrefsItem($r_key, $system = false) {
    global $db, $user;

    if ($system) {
        $where['uid'] = ['value' => 0];
    } else {
        $where['uid'] = ['value' => $user['id']];
    }
    $results = $db->select('preferences', null, $where);
    $user_prefs = $db->fetchAll($results);

    foreach ($user_prefs as $pref) {
        if ($pref['pref_name'] == $r_key) {
            return $pref['pref_value'];
        }
    }
    return false;
}

function setPrefsItem($key, $value, $system = false) {
    global $db, $user;

    if ($system) {
        $uid = 0;
    } else {
        $uid = $user['id'];
    }

    $newitem = [
        'uid' => $uid,
        'pref_name' => $key,
        'pref_value' => $value,
    ];

    $where['uid'] = ['value' => $uid];
    $where['pref_name'] = ['value' => $key];

    $result = $db->select('preferences', null, $where, 'LIMIT 1');
    $db->finalize($result);
    $prefs = $db->fetch($result);

    if ($prefs) {

        if ($prefs['pref_value'] != $value) {
            $set['pref_value'] = $value;
            $db->update('preferences', $set, $where, 'LIMIT 1');
        }
    } else {
        $db->addItem('preferences', $newitem);
    }
}
