<?php

/**
 * Represent events supported by Task class
 * @author coder
 *
 * Created on: Apr 14, 2025 at 10:46:38 AM
 */

namespace lib\tasks {

    enum TaskEvent
    {

        /**
         * An event circle is starting
         */
        case START;

        /**
         * An event is executing
         */
        case RUNNING;

        /**
         * An event is paused
         */
        case PAUSE;

        /**
         * An event is resumed
         */
        case RESUME;

        /**
         * An event emits an error
         */
        case ERROR;

        /**
         * An event circle is completed
         */
        case COMPLETE;

        /**
         * An event is cancelled
         */
        case CANCEL;

        /**
         * An event circle is repeating
         */
        case REPEAT;
    }

}
