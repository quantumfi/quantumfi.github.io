<?php
class user_game extends Main {
 
    public function __construct(DB\SQL $db) {
        parent::__construct($db,'mg_user_game', null, 600); 
    }
}