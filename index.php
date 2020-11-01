<?php
// подключаем конфиг и функции
require_once('config.php');
require_once('functions.php');
// получаем и обрабатываем данные
$data = json_decode(file_get_contents('php://input'), true);
$button = @$data['callback_query']['data'];
$data = @$data['callback_query']['message'] ?: $data['message'];
// пишем лог последнего сообщения боту
file_put_contents('bot.txt', print_r($data, true));
// получаем значение кнопки, нажатой пользователем
// получаем текст сообщения
$text = strtolower($data['text']);
// получаем id чата
$chat_id = $data['chat']['id'];

// получаем информацию об отправителе
$user_name = $data['from']['first_name'];

// формируем кнопки с колбеками
$keyboard = [
  "resize_keyboard" => true,
  "inline_keyboard" => [
    [
      [
        'text' => 'Кнопка',
        'callback_data' => 'левую'
      ],
      [
        'text' => 'Кнопка',
        'callback_data' => 'правую'
      ]
    ],
    [
      [
        'text' => 'Кнопка',
        'callback_data' => 'нижнюю'
      ]
    ]
  ]
];

// обрабатываем сообщение
$method = false;
if($text == '/start') {
  $method = 'sendMessage';
  $sendData = [
    'text' => 'Нажмите на любую кнопку',
    'reply_markup' => $keyboard
  ];
} elseif($text == '/stop'){
  $method = 'sendMessage';
  $sendData = [
    'text' => "Бот остановлен",
  ];
}
elseif($button) {
  $method = 'sendMessage';
  $sendData = [
    'text' => "Вы нажали $button кнопку",
    'reply_markup' => $keyboard
  ];
}
  
$sendData['chat_id'] = $chat_id;
if($method) sendTelegram($method, $sendData);

// если пользователя запустил бота, а чата с ним не в базе - добавляем в базу
if($text == '/start') saveUser($chat_id, $user_name);
// если пользователь остановил бота - удаляем чат с ним из базы
if($text == '/stop') removeUser($chat_id);
