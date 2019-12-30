<?php

namespace App\Validation\Rules;

use App\Models\User;
use Respect\Validation\Rules\AbstractRule;

/**
 * EmailAvailable
 *
 * @author    Haven Shen <havenshen@gmail.com>
 * @copyright    Copyright (c) Haven Shen
 */
class LoginAvailable extends AbstractRule {

    public function validate($input) {
        return User::where('login', $input)->count() === 0;
    }

}
