<?php

class PostController extends Controller
{
    public function index(): void
    {
        $posts = R::findAll('posts');
        foreach ($posts as $post) {
            $item = new Post($post->id);
            $items[] = $item->getPost();
        }
        showTemplate('/posts/index.twig', ['posts' => $items]);
    }

    public function show(int $id): void
    {
        $post = new Post($id);
        $post = $post->getPost();
        $comments = Comment::getAllById($id);
        showTemplate(
            '/posts/show.twig',
            [
                'post' => $post,
                'comments' => $comments
            ]
        );
    }

    public function create(): void
    {
        $this->authorizeUser();
        $languages = R::findAll('languages');
        showTemplate('/posts/create.twig', ['languages' => $languages]);
    }

    public function createPost(array $req): void
    {
        $this->authorizeUser();
        if (!empty($req)) {
            $id = Post::create($req);
        } else {
            $_SESSION['message'] = 'Invalid request!';
        }
        redirect('/posts/show?param=' . $id);
    }

    /**
     * Shows post edit page
     */
    public function edit(int $id): void
    {
        $this->authorizeUser();
        showTemplate(
            '/posts/edit.twig',
            [
                'post' => getBeanById('posts', $id),
            ]
        );
    }

    public function commentPost(array $req): void
    {
        $this->authorizeUser();
        $post = new Post($req['post_id']);
        $post->placeComment($req['comment'], $_SESSION['user']->getId());
        redirect('/posts/show?param=' . $post->getId());
    }

    public function like($post_id)
    {
        $this->authorizeUser();
        $likes = R::dispense('likes');
        $likes->post = getBeanById('posts', $post_id);
        $likes->user = getBeanById('users', $_SESSION['user']->getId());
        R::store($likes);
        redirect('/posts/show?param=' . $post_id);
    }

    public function unlike($post_id)
    {
        $this->authorizeUser();
        $like = R::findOne('likes', 'user_id=? AND post_id=?', [$_SESSION['user']->getId(), $post_id]);
        R::trash('likes', $like->id);
        redirect('/posts/show?param=' . $post_id);
    }
}
