<?php
class games extends Main {
 
    public function __construct(DB\SQL $db) {
        parent::__construct($db,'mg_games', null, 600); 
    }
}