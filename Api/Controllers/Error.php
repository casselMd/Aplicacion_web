<?php

class Errors extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->index();
    }

    public function index() {
        $this->view->getView($this, "error");
    }
}

