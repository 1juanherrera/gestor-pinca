<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('welcome_message');
    }

    public function about(){

       echo json_encode([
           'title' => 'About Us',
           'content' => 'This is the about page of our application.'
       ]);

    }
}