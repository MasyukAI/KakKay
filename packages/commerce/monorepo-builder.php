<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\PushNextDevReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\PushTagReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetCurrentMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\SetNextMutualDependenciesReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\TagVersionReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateBranchAliasReleaseWorker;
use Symplify\MonorepoBuilder\Release\ReleaseWorker\UpdateReplaceReleaseWorker;
use Symplify\MonorepoBuilder\ValueObject\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    // Directories to scan for packages
    $parameters->set(Option::PACKAGE_DIRECTORIES, [
        __DIR__.'/packages',
    ]);

    // Directories to exclude from scanning
    $parameters->set(Option::PACKAGE_DIRECTORIES_EXCLUDES, [
        'vendor',
        'node_modules',
        'tests',
    ]);

    // Data to append to each package composer.json
    $parameters->set(Option::DATA_TO_APPEND, [
        'require-dev' => [
            'pestphp/pest' => '^4.0',
            'pestphp/pest-plugin-laravel' => '^4.0',
            'orchestra/testbench' => '^10.0',
        ],
        'autoload-dev' => [
            'psr-4' => [
                'Tests\\' => 'tests/',
            ],
        ],
        'minimum-stability' => 'dev',
        'prefer-stable' => true,
    ]);

    $services = $containerConfigurator->services();

    // Release workers - in order to execute
    $services->set(UpdateReplaceReleaseWorker::class);
    $services->set(SetCurrentMutualDependenciesReleaseWorker::class);
    $services->set(TagVersionReleaseWorker::class);
    $services->set(PushTagReleaseWorker::class);
    $services->set(SetNextMutualDependenciesReleaseWorker::class);
    $services->set(UpdateBranchAliasReleaseWorker::class);
    $services->set(PushNextDevReleaseWorker::class);
};
