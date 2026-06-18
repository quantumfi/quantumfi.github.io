;<?php die(); ?>
[routes]
//MGFrontPageController
GET /=MGFrontPageController->index
GET /logout=MGFrontPageController->logout

GET|POST /login=MGFrontPageController->login
GET|POST /register=MGFrontPageController->register
GET|POST /consentForm=MGFrontPageController->consentForm

GET /raw/@gameId=MGFrontPageController->getRawData
GET /priceRound/@gameId=MGFrontPageController->priceRoundJson
GET /capitalMCountPlayer/@gameId=MGFrontPageController->capitalMCountPlayerJson
GET /graph/@gameId=MGFrontPageController->graph
GET /rank/@hackGameId=MGFrontPageController->playerRank

//MGAccountController
GET|POST /createGame=MGAccountController->saveGame
GET|POST /gameList=MGAccountController->gameList

POST /saveUserGame=MGAccountController->saveUserGame
GET /gameData=MGAccountController->gameData
GET /gamespace=MGAccountController->gamespace
GET /getHistory/@format=MGAccountController->getHistory
GET /logoutGame=MGAccountController->logoutGame

GET /rank=MGAccountController->playerRank

GET /submittedPlayers=MGAccountController->getSubmittedActionPlayers

//MGForgotPasswordController
GET /resetPassword/@id=MGForgotPasswordController->resetPassword
GET|POST /resetPassword=MGForgotPasswordController->resetPassword
GET|POST /forgotPassword=MGForgotPasswordController->processForgot
