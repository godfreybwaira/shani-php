<?php

/**
 * Description of Stepper
 * @author coder
 *
 * Created on: Jun 5, 2025 at 1:06:27 PM
 */

namespace gui\v2\components {

    use gui\v2\Component;
    use gui\v2\decorators\StepperStatus;

    final class Step extends Component
    {

        public function __construct()
        {
            parent::__construct('ul');
            $this->classList->addAll(['step', 'step-dir-h']);
        }

        public function addDescription(Component $description, StepperStatus $status = null): self
        {
            $li = new Component('li');
            $li->appendChild($description);
            if ($status !== null) {
                $li->classList->addOne('step-' . $status->value);
            }
            return $this->appendChild($li);
        }
    }

}
