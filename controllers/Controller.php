<?php

class Controller
{
    public function authorizeUser(): void
    {
        if (!$_SESSION['user']) {
            redirect('/user/login');
        }
    }
}
