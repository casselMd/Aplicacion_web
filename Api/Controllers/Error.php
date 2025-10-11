<?php

class Errors extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function notFound() {
        $this->view->getView($this, "error");
    }
}


$error = new Errors();
$error->notFound();