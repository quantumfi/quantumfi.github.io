<?php
class user_consent extends Main {
 
    public function __construct(DB\SQL $db) {
        parent::__construct($db,'mg_user_consent', null); 
    } 
}