<?php

/**
 * Description of AccessPolicy
 * @author coder
 *
 * Created on: Mar 25, 2025 at 10:09:50 AM
 */
/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPEnum.php to edit this template
 */

namespace shani\advisors\web {

    enum AccessPolicy: string
    {

        /**
         *  allows resource access on this application from this domain only
         */
        case THIS_DOMAIN = 'same-origin';

        /**
         *  allows resource access on this application from this domain and it's subdomain
         */
        case THIS_DOMAIN_AND_SUBDOMAIN = 'same-site';

        /**
         *  allows resource access on this application from any domain (Not recommended)
         */
        case ANY_DOMAIN = 'cross-origin';

        /**
         *  Do not use resource access policy (Not recommended)
         */
        case DISABLED = '';
    }

}