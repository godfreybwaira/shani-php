<?php

/**
 * Description of ProjectMetaData
 * @author goddy
 *
 * Created on: May 13, 2026 at 11:32:06 AM
 */

namespace features\console\helpers {

    use shani\launcher\Framework;

    final class ProjectMetaData
    {

        public readonly string $projectName;
        public readonly string $hostName;
        public readonly string $projectDirectory;
        public readonly string $hostDirectory;
        public readonly string $hostPath;

        public function __construct(string $projectName, string $hostName)
        {
            $this->projectName = $projectName;
            $this->hostName = $hostName;
            $this->hostDirectory = Framework::DIR_HOSTS . DIRECTORY_SEPARATOR . $hostName;
            $this->projectDirectory = Framework::DIR_APPS . DIRECTORY_SEPARATOR . $projectName;
            $this->hostPath = $this->hostDirectory . '.yml';
        }

        public function projectExists(): bool
        {
            return is_dir($this->projectDirectory);
        }

        public function hostExists(): bool
        {
            return is_file($this->hostPath);
        }
    }

}
