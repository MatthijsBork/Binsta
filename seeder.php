<?php

require_once __DIR__ . '/helpers.php';

$posts = [
    [
        'snippet' => '
        <?php 
        foreach($array as $arr) {
            echo $arr;
        }',
        'caption'  => 'My php code',
        'user_id'  => 1,
        'language' => 'php'
    ],
    [
        'snippet' => '
        <div>
            Hello World!
        </div>',
        'caption'  => 'My html code',
        'user_id'  => 1,
        'language' => 'html'
    ],
    [
        'snippet' => '
        function myFunction($user) {
            return "Hello, " + $user;
        }',
        'caption'  => 'My JS code',
        'user_id'  => 1,
        'language' => 'javascript'
    ],
];

$users = [
    [
        'id' => 1,
        'image_name' => '',
        'username' => 'Admin',
        'password' => 'password1'
    ],
];

$likes = [
    [
        'user_id' => 1,
        'post_id' => 1
    ]
];

$comments = [
    [
        'content' => 'test comment',
        'user_id' => 1,
        'post_id' => 1
    ]
];

$langs = [
    'php',
    'javascript',
    'html',
    'css',
];

R::nuke();

foreach ($langs as $lang) {
    $newLang = R::dispense('languages');
    $newLang->language = $lang;
    R::store($newLang);
}

foreach ($users as $user) {
    $newUser = R::dispense('users');
    $newUser->username = $user['username'];
    $newUser->image_name = 'default.jpg';
    $newUser->biography = 'Binsta administrator';
    $newUser->password = password_hash($user['password'], PASSWORD_DEFAULT, []);
    R::store($newUser);
}

foreach ($posts as $post) {
    $beans['newPost'] = R::dispense('posts');
    $beans['user'] = R::load('users', $post['user_id']);

    $beans['newPost']->caption = $post['caption'];
    $beans['newPost']->snippet = $post['snippet'];
    $beans['newPost']->user = $beans['user'];
    $beans['newPost']->language = $post['language'];

    R::storeAll($beans);
}

foreach ($likes as $l) {
    $like = R::dispense('likes');

    $like->user = getBeanById('users', 1);
    $like->post = getBeanById('posts', 1);

    R::store($like);
}

foreach ($comments as $c) {
    $comment = R::dispense('comments');

    $comment->content = $c['content'];
    $comment->user = getBeanById('users', 1);
    $comment->post = getBeanById('posts', 1);

    R::store($comment);
}

echo 'seeded';

R::close();
