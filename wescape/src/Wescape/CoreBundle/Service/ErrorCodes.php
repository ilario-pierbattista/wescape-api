<?php

namespace Wescape\CoreBundle\Service;


class ErrorCodes
{
    // Signup
    const SIGNUP_DUPLICATED_EMAIL = 510;

    // Password reset
    const PASSWORD_RESET_WRONG_EMAIL = 520;
    const PASSWORD_RESET_WRONG_SECRET_CODE = 521;
    const PASSWORD_RESET_EXPIRED_SECRET = 522;
    
    // Gestione della posizione dell'utente
    const POSITION_ALREADY_CREATED = 430;
    const POSITION_NOT_FOUND = 431;
}