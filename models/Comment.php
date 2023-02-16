<?php

class Comment extends Model
{
    private int $id;
    private int $user_id;
    private int $post_id;
    private string $content;
    private object $user;

    public function getId()
    {
        return $this->id;
    }

    public function __construct($id)
    {
        $comment = getBeanById('comments', $id);
        $this->id = $id;
        $this->content = $comment->content;
        $this->user_id = $comment->user_id;
        $this->post_id = $comment->post_id;
        $this->user = new User($this->user_id);
    }

    public static function create($post_id, $user_id, $content)
    {
        $comment = R::dispense('comments');

        $comment->content = $content;
        $comment->user = getBeanById('users', $user_id);
        $comment->post = getBeanById('posts', $post_id);

        R::store($comment);
    }

    public static function getAllById($id)
    {
        $comments = [];
        $commentsdb = R::getAll('SELECT * FROM comments WHERE post_id = :post_id', [':post_id' => $id]) ?? null;
        if ($commentsdb) {
            foreach ($commentsdb as $c) {
                $comment = new self($c['id']);
                $comments[] = $comment->getData();
            }
        }
        return $comments;
    }

    public function getData()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'post_id' => $this->post_id,
            'content' => $this->content,
            'username' => $this->user->getUsername(),
            'image_name' => $this->user->getImageName()
        ];
    }
}
