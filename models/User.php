<?php

class User extends Model
{
    private int $id;
    private string $username;
    private string $image_name;
    private string $biography;
    private string $password;
    private array $likes;

    public function __construct($id)
    {
        $user = getBeanById('users', $id);
        $this->id = $user->id;
        $this->username = $user->username;
        $this->image_name = $user->image_name;
        $this->biography = $user->biography;
        $this->password = $user->password;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getImageName(): string
    {
        return $this->image_name;
    }

    public function getBiography(): string
    {
        return $this->biography;
    }

    public function getLikes(): array  
    {
        return $this->likes;
    }



    /**
     * Returns array with user's username, bio, image_name, and id
     *
     * @return array
     */
    public function getData()
    {
        return [
            'username' => $this->username,
            'biography' => $this->biography,
            'image_name' => $this->image_name,
            'id' => $this->id
        ];
    }

    /**
     * Returns true if given password matches with user's password
     *
     * @param  string $password
     * @return bool
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->password);
    }

    /**
     * updates user data in database with data given
     *
     * @param  string $data
     * @return void
     */
    public function update($data): void
    {
        $user = getBeanById('users', $this->id);
        $user->username = htmlspecialchars($data['username']);
        if (!empty($data['new_password'])) {
            $user->password = $data['new_password'];
        }
        $user->biography = htmlspecialchars($data['biography']);
        $user->image_name = $data['image_name'];
        R::store($user);
    }

    /**
     * Creates a new user with a username and password
     *
     * @param  string $username
     * @param  string $password
     * @return void
     */
    public static function create($username, $password): void
    {
        $newUser = R::dispense('users');
        $newUser->username = htmlspecialchars($username);
        $newUser->password = $password;
        $newUser->image_name = 'default.jpg';
        $newUser->biography = 'A new Binsta user';
        R::store($newUser);
    }

    /**
     * Returns a user by its username if user exists
     *
     * @param  string $username
     * @return bean
     */
    public static function findByUsername($username)
    {
        $user = R::findOne('users', ' username = ? ', [$username]) ?? null;
        return isset($user) ? new self($user['id']) : null;
    }

    public function getPosts(): array
    {
        $posts = [];
        $postsdb = R::getAll('SELECT * FROM posts WHERE user_id = :user_id', [':user_id' => $this->id]) ?? null;
        if ($postsdb) {
            foreach ($postsdb as $p) {
                $post = new Post($p['id']);
                $posts[] = $post->getPost();
            }
        }
        return $posts;
    }
}
