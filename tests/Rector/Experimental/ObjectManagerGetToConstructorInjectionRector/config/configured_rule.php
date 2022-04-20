<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Ssch\TYPO3Rector\Rector\Experimental\ObjectManagerGetToConstructorInjectionRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->phpVersion(PhpVersion::PHP_74);

    $rectorConfig->import(__DIR__ . '/../../../../../config/config_test.php');
    $rectorConfig->rule(ObjectManagerGetToConstructorInjectionRector::class);
};
