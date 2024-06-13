<?php

/**
 * Description of Module
 * @author coder
 *
 * Created on: Feb 12, 2024 at 1:42:54 PM
 */

namespace apps\test\codes\v1\config {

    interface Module
    {

        public const FALLBACK = '/fallback/0/handlers/0';
        public const HOME_GUEST = '/users/0/profile/1/daily.html?from=Mwz&to=Geita?&time=13:00&dep=12:22&from=other';
//        public const HOME_GUEST = '/guests/0/accounts';
        public const HOME_AUTH = '/users/0/accounts/0/profile';
    }

}
