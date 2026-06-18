<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Container;

class HomeController extends Controller
{
    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function index()
    {
        return $this->view('home.index', [
            'isLanding' => true,
            'pageTitle' => 'Habitify — Трекер привычек для достижения целей',
        ]);
    }
}
