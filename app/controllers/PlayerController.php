<?php

class PlayerController {

    public function __construct(){

    }

    public function index(){
        $list = ["Neymar", "Bebeto", "Gabigol", "Cebola"];

        require_once __DIR__ . '/views/players/players_list.php';
    }
}