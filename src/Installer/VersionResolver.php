<?php
/**
 * Copyright © Vaimo Group. All rights reserved.
 * See LICENSE_VAIMO.txt for license details.
 */
namespace Vaimo\ChromeDriver\Installer;

class VersionResolver
{
    /**
     * @var \Composer\Package\Version\VersionParser 
     */
    private $versionParser;
    
    /**
     * @var \Vaimo\ChromeDriver\Installer\Utils
     */
    private $installerUtils;

    public function __construct() 
    {
        $this->versionParser = new \Composer\Package\Version\VersionParser();
        $this->installerUtils = new \Vaimo\ChromeDriver\Installer\Utils();
    }
    
    public function pollForVersion(array $binaryPaths, array $versionPollingConfig)
    {
        $processExecutor = new \Composer\Util\ProcessExecutor();
        
        $processExecutor::setTimeout(10);

        foreach ($binaryPaths as $path) {
            if (!is_executable($path)) {
                continue;
            }

            foreach ($versionPollingConfig as $callTemplate => $resultPatterns) {
                $output = '';

                $processExecutor->execute(sprintf($callTemplate, $path), $output);

                $output = trim($output);

                foreach ($resultPatterns as $pattern) {
                    $matches = sscanf($output, $pattern);

                    if (!is_array($matches) || !$matches) {
                        continue;
                    }

                    $result = reset($matches);

                    try {
                        $this->versionParser->parseConstraints($result);
                    } catch (\UnexpectedValueException $exception) {
                        continue;
                    }

                    return $result;
                }
            }
        }

        return '';
    }
}
