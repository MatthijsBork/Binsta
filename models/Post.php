<?php

class Post extends Model
{
    private int $id;
    private string $caption;
    private string $snippet;
    private string $language;
    private int $user_id;
    private object $user;
    private bool $liked = false;

    public function __construct($id)
    {
        $post = getBeanById('posts', $id);
        $this->id = $post->id;
        $this->caption = $post->caption;
        $this->snippet = $post->snippet;
        $this->language = $post->language;
        $this->user_id = $post->user_id;
        $this->user = new User($this->user_id);

        if (isset($_SESSION['user'])) {
            if (R::findOne('likes', 'user_id=? AND post_id=?', [$_SESSION['user']->getId(), $id])) {
                $this->liked = true;
            }
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getPost(): array
    {
        $post = [
            'id' => $this->id,
            'caption' => $this->caption,
            'snippet' => $this->snippet,
            'language' => $this->language,
            'user' => $this->user->getData(),
            'liked' => $this->liked
        ];
        return $post;
    }

    public function placeComment(string $content, int $user_id): void
    {
        Comment::create($this->id, $user_id, $content);
    }

    public static function create(array $req): int
    {
        $beans['newPost'] = R::dispense('posts');
        $beans['user'] = R::load('users', $_SESSION['user']->getId());
        $beans['likes'] = R::dispense('likes');

        $beans['newPost']->caption = $req['caption'];
        $beans['newPost']->snippet = $req['snippet'];
        $beans['newPost']->language = $req['language'];
        $beans['newPost']->user = $beans['user'];

        $beans['likes']->user = $beans['user'];
        $beans['likes']->post = $beans['newPost'];
        R::storeAll($beans);
        return $beans['newPost']->id;
    }
}
