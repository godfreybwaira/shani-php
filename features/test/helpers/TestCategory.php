<?php

/**
 * Description of TestCategory
 * @author goddy
 *
 * Created on: Sep 12, 2025 at 8:41:30 AM
 */

namespace features\test\helpers {

    enum TestCategory
    {

        /**
         * Does the software do what it's supposed to do? Each feature or function
         * (e.g., buttons, forms, calculations) is checked against requirements
         * or user stories.
         */
        case FUNCTIONALITY;

        /**
         * How well does the software handle load, speed, and resource usage?
         * This includes response times, scalability, and stability under stress.
         */
        case PERFORMANCE;

        /**
         * Are there vulnerabilities? Testing checks for protection against
         * unauthorized access, data breaches, or other threats.
         */
        case SECURITY;

        /**
         * Does the software perform consistently without crashes or errors under
         * normal and edge-case conditions?
         */
        case RELIABILITY;

        /**
         * Does the software meet industry standards, regulations, or legal
         * requirements (e.g. accessibility standards)?
         */
        case COMPLIANCE;

        /**
         * Other category not mentioned above
         */
        case OTHER;
    }

}
