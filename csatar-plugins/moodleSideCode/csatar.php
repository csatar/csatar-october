<?php

// This file should be copied to the moodle root directory.
// Moodle config should have a $CFG->csatarEncryptionKey property with the correct encryption key set.

require('config.php');

$receivedData = prepareReceivedData();

$userDataFromDb = getUserData($receivedData);

$userId = null;

if ($userDataFromDb === false) {
    $userId = createNewUser($receivedData['basic']);
} else {
    $userId = intval($userDataFromDb->id);
}

$user = get_complete_user_data('id', $userId);

// require_once($CFG->dirroot."/user/lib.php");
// user_delete_user($user); die;

if (!empty($user)) {
    // update user data
    updateBasicData($user, $receivedData['basic']);

    // update additional data
    updateAdditionalData($user->id, $receivedData['profile']);
}
loginAndRedirect($user);

// functions
function createNewUser($receivedData) {
    // check if user with same e-mail address exists
    if (\core_user::get_user_by_email($receivedData['email']) !== false) {
        echo "User already exists with the same e-mail address, please contact system administrator!";
        die;
    }

    global $CFG;
    require_once($CFG->dirroot . "/user/lib.php");

    // create user object
    $newUser            = (object) $receivedData;
    $newUser->username  = generateUsername($receivedData);
    $newUser->auth      = 'manual';
    $newUser->confirmed = 1;

    return $userId = user_create_user($newUser, false, false);
}

function generateUsername($receivedData, $num = 1) {
    if ($num > 999) {
        return '';
    }

    $username = strtolower($receivedData['lastname'] . $receivedData['firstname'] . ($num != 1 ? $num : ''));

    $charMap = [
        'á' => 'a',
        'é' => 'e',
        'í' => 'i',
        'ó' => 'o',
        'ö' => 'o',
        'ő' => 'o',
        'ú' => 'u',
        'ü' => 'u',
        'ű' => 'u',
        'Á' => 'a',
        'É' => 'e',
        'Í' => 'i',
        'Ó' => 'o',
        'Ö' => 'o',
        'Ő' => 'o',
        'Ú' => 'u',
        'Ü' => 'u',
        'Ű' => 'u',
        ' ' => '',
        'ș' => 's',
        'Ș' => 's',
        'ț' => 't',
        'Ț' => 't',
        'ă' => 'a',
        'Ă' => 'a',
        'î' => 'i',
        'Î' => 'i',
        'â' => 'a',
        'Â' => 'a'

    ];

    $username = str_replace(array_keys($charMap), array_values($charMap), $username);


    $username = iconv('UTF-8','ASCII//TRANSLIT',$username);
    global $DB;
    if ($DB->get_record('user', array('username' => $username), '*') !== false) {
        return generateUsername($receivedData, $num + 1);
    }

    return $username;
}

function updateBasicData($object, $dataArray) {
    global $DB;

    $updateNeeded = false;

    foreach ($dataArray as $key => $value) {
        if ($object->$key != $value) {
            $object->$key = $value;
            $updateNeeded = true;
        }
    }

    if ($updateNeeded) {
        $DB->update_record('user', $object);
    }
}

function updateAdditionalData($userId, $infoData) {
    foreach ($infoData as $shortName => $value) {
        $infoFieldId = getUserInfoFieldId($shortName);
        updateOrSetUserInfoData($userId, $infoFieldId, $value);
    }
}

function getUserInfoFieldId(string $shortName) {
    global $DB;
    $record = $DB->get_record('user_info_field', ['shortname' => $shortName]);

    return $record ? $record->id : false;
}

function updateOrSetUserInfoData($userId, $infoFieldId, $value) {
    if (empty($infoFieldId)) {
        return;
    }

    global $DB;
    $record = $DB->get_record(
        'user_info_data',
        [
            'userid'  => $userId,
            'fieldid' => $infoFieldId,
        ]
    );

    if ($record !== false && $record->data != $value) {
        $record->data = $value;
        $DB->update_record('user_info_data', $record);
    } elseif ($record == false) {
        $DB->insert_record(
            'user_info_data',
            [
                'userid'     => $userId,
                'fieldid'    => $infoFieldId,
                'data'       => $value,
                'dataformat' => 0,
            ]
        );
    }
}

function prepareReceivedData() {
    global $CFG;
    $unserializedData = [];

    if (isset($_REQUEST['data'])) {
        $receivedData = json_decode(str_replace(' ', '+', $_REQUEST['data']));

        if (empty($receivedData) || strlen($receivedData[1]) < 16) {
            echo "Something went wrong, please go back to the previous page, refresh it, and try again!";
            die;
        }

        $decryptedData    = openssl_decrypt(base64_decode($receivedData[0]), 'aes-256-cbc', $CFG->csatarEncryptionKey, 0, base64_decode($receivedData[1]));
        $unserializedData = unserialize($decryptedData);
    }

    if (!isset($unserializedData['profile']['ECSK'])) {
        echo 'Empty ECSK';
        die;
    }

    return $unserializedData;
}

function getUserData($data) {
    global $DB;

    $sql = "SELECT u.id FROM mdl_user u JOIN mdl_user_info_data uid ON u.id = uid.userid WHERE uid.fieldid = 8 AND uid.data = :ecsk;";

    return $DB->get_record_sql($sql, ['ecsk' => $data['profile']['ECSK']]);
}

function loginAndRedirect($user) {
    global $CFG;
    if (complete_user_login($user)) {
        \core\session\manager::apply_concurrent_login_limit($user->id, session_id());
        redirect( $CFG->wwwroot . '/');
    } else {
        echo "Could not login, please contact system administrator!";
        die;
    }
}
