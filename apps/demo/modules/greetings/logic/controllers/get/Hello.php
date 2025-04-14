<?php

/**
 * Description of Hello
 * @author coder
 *
 * Created on: Feb 13, 2024 at 1:50:50 PM
 */

namespace apps\demo\modules\greetings\logic\controllers\get {

    use apps\demo\modules\greetings\data\dto\Student;
    use apps\demo\modules\greetings\data\dto\Subject;
    use shani\http\App;

    final class Hello
    {

        private readonly App $app;

        public function __construct(App &$app)
        {
            $this->app = $app;
        }

        /**
         * Display greetings from Shani.
         */
        public function world()
        {
            //using global style
//            $this->app->ui()->style('/css/styles.css');
//            passing data to current view (greetings/presentation/views/hello/world.php)
            $student = new Student('Godfrey', 'Bwaira', 20);
            $student->addSubject(new Subject('English', 'A', 89))
                    ->addSubject(new Subject('Kiswahili', 'B', 79))
                    ->addSubject(new Subject('Maths', 'C', 70.6));
            $this->app->render($student);
        }
    }

}
