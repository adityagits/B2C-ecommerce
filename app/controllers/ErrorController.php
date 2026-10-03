<?php

class ErrorController extends Controller
{
    public function show(): void
    {
        $this->view('errors/404');
    }
}
