<?php

function sendTelegram($method, $data, $headers = []) {
    $ch = curl_init();
    curl_setopt_array($ch, [
      CURLOPT_POST => 1,
      CURLOPT_HEADER => 0,
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => 'https://api.telegram.org/bot'. TOKEN .'/'.$method,
      CURLOPT_POSTFIELDS => json_encode($data),
      CURLOPT_HTTPHEADER => array_merge(["Content-Type: application/json"], $headers)
    ]);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
  }
  
  function getDB() {
    if (!file_exists(DB_FILE_PATH)) return [];
    $str = file_get_contents(DB_FILE_PATH);
    if ($str) {
      return unserialize($str);
    } else {
      return [];
    }
  }
  
  function setDB($db) {
    file_put_contents(DB_FILE_PATH, serialize($db));
    return false;
  }
  
  function saveUser($chat_id, $user_name = '') {
    $db = getDB();
    $db[$chat_id] = $user_name;
    setDB($db);
    return false;
  }
  
  function removeUser($chat_id) {
    $db = getDB();
    unset($db[$chat_id]);
    setDB($db);
    return false;
  }
  
  function sendMessage($chat_id, $message) {
    $method = 'sendMessage';
    $sendData = [
      'text' => $message,
      'chat_id' => $chat_id
    ];
    sendTelegram($method, $sendData);
    return false;
  }
  
  function massSend() {
    $db = getDB();
    if(!count($db)) return false;
    $k = 0;
    foreach($db as $chat_id => $user_name) {
      // учитываем ограничение рассылки - не более 30 сообщение в секунду  
      $k++;
      if ($k == 30) {
          sleep(1);
          $k = 0;
      }
      if($user_name) {
        $message = str_replace('#name#', $user_name, MESSAGE);
      } else {
        $message = MESSAGE_WITHOUT_NAME;
      }
      sendMessage($chat_id, $message);
    }
  }
  