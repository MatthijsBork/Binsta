<?php

class UserController extends Controller
{
    public function login(): void
    {
        showTemplate('/user/login.twig', []);
    }

    public function register(): void
    {
        showTemplate('/user/register.twig', []);
    }

    public function loginPost($req): void
    {
        // Sanitize request
        preg_replace('/[^-a-zA-Z0-9_]/', '', [$req['username'], $req['password']]);

        $user = User::findByUsername($req['username']);

        if (!$user || !$user->verifyPassword($req['password'])) {
            $_SESSION['message'] = 'Incorrect username or password';
            redirect('/user/login');
        } else {
            $_SESSION['user'] = $user;
            redirect('/');
        }
    }

    public function registerPost($req): void
    {
        $username = filter_var($req['username']);
        $password = filter_var($req['password']);
        $password_confirm = filter_var($req['password_confirm']);

        if ($password != $password_confirm) {
            $_SESSION['message'] = 'Passwords do not match';
            redirect('/user/register');
        } elseif (preg_match('^(?=.*\d).{4,16}$^', $password) != '1') {
            $_SESSION['message'] = 'Password must contain at least eight characters, and a maximum of 16. At least one letter and one number';
            redirect('/user/register');
        } elseif (User::findByUsername($username)) {
            $_SESSION['message'] = 'Username already taken!';
            redirect('/user/register');
        } else {
            User::create($username, password_hash($password, PASSWORD_DEFAULT, []));
            redirect('/user/login');
        }
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        session_destroy();
        redirect('/');
    }

    public function show($id): void
    {
        $user = new User($id);
        showTemplate(
            '/user/show.twig',
            ['user' =>
            [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'biography' => $user->getBiography(),
                'image_name' => $user->getImageName(),
                'posts' => $user->getPosts($id)
            ]]
        );
    }

    public function edit(): void
    {
        $_SESSION['user'] = new User($_SESSION['user']->getId());
        $this->authorizeUser();
        if ($_SESSION['user']) {
            showTemplate('/user/edit.twig', []);
        }
    }

    /**
     * Updates user data
     *
     * @param  array $req data to update user with
     * @return void
     */
    public function editPost($req): void
    {
        if (!$_SESSION['user']->verifyPassword($req['password'])) {
            message('danger', 'Incorrect password!');
            redirect('/user/edit');
        } elseif (!empty($req['new_password']) && preg_match('^(?=.*\d).{4,16}$^', $req['new_password']) != '1') {
            $_SESSION['message'] = 'Password must contain at least eight characters, and a maximum of 16. At least one letter and one number';
            redirect('/user/edit');
        } elseif ($req['new_password'] != $req['password_confirm']) {
            message('danger', 'New passwords do not match!');
            redirect('/user/edit');
        } elseif (User::findByUsername($req['username']) && $req['username'] != $_SESSION['user']->getUsername()) {
            message('danger', 'Username taken!');
            redirect('/user/edit');
        } else {
            if ($req['new_password']) {
                $req['new_password'] = password_hash($req['new_password'], PASSWORD_DEFAULT, []);
            }

            // Bio
            $req['biography'] = filter_var($req['biography']);
            // Image
            $req['image_name'] = $_SESSION['user']->getImageName() ?? 'default.jpg';

            if (file_exists($_FILES['profile_image']['tmp_name']) || is_uploaded_file($_FILES['profile_image']['tmp_name'])) {
                $errors = [];
                $req['image_name'] = uploadImage('../public/images/users', 'profile_image', $errors);
            }
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    message('danger', $error);
                    redirect('/user/edit');
                }
            } else {
                if ($_SESSION['user']->getImageName() != 'default.jpg') {
                    unlink('../public/images/users/' . $_SESSION['user']->getImageName());
                }
                $_SESSION['user']->update($req);
                message('success', 'Saved!');
                redirect('/user/edit');
            }
        }
    }

    public function search($req)
    {
        if ($req['search']) {
            $users = R::find('users', 'username LIKE ?', [$req['search']]);
            showTemplate('/user/search.twig', $users);
        }
    }
}
