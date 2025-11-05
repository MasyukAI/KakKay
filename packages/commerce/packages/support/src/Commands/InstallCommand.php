<?php

declare(strict_types=1);

namespace AIArmada\CommerceSupport\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\select;

#[AsCommand(name: 'commerce:install', aliases: ['install:commerce'])]
final class InstallCommand extends Command
{
    private const VERSION = '1.0.0';

    /**
     * @var array<string, string>
     */
    private const REQUIRED_PACKAGES = [
        'cart' => 'Cart Management',
        'stock' => 'Stock Management',
        'vouchers' => 'Voucher System',
    ];

    /**
     * @var array<string, string>
     */
    private const OPTIONAL_PACKAGES = [
        'chip' => 'CHIP Payment Gateway',
        'jnt' => 'J&T Express Shipping',
        'filament' => 'Filament Admin Panels',
    ];

    protected Filesystem $files;

    protected $description = 'Install and configure the AIArmada Commerce suite.';

    protected $name = 'commerce:install';

    /**
     * @var array<string>
     */
    protected $aliases = ['install:commerce'];

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * @return array<InputOption>
     */
    protected function getOptions(): array
    {
        return [
            new InputOption('all', null, InputOption::VALUE_NONE, 'Install all optional packages'),
            new InputOption('chip', null, InputOption::VALUE_NONE, 'Install CHIP payment gateway integrations'),
            new InputOption('jnt', null, InputOption::VALUE_NONE, 'Install J&T Express shipping integrations'),
            new InputOption('filament', null, InputOption::VALUE_NONE, 'Install Filament admin packages'),
            new InputOption('force', 'F', InputOption::VALUE_NONE, 'Overwrite existing configuration files'),
            new InputOption('no-star', null, InputOption::VALUE_NONE, 'Skip GitHub star prompt'),
        ];
    }

    public function __invoke(): int
    {
        $this->displayWelcome();

        // Ask JSON column type preference once and persist globally
        $this->configureJsonColumnType();

        $packages = $this->determinePackagesToInstall();

        if (empty($packages)) {
            $this->components->error('No packages selected for installation.');

            return self::FAILURE;
        }

        $this->components->info('Installing selected packages...');
        $this->newLine();

        foreach ($packages as $package => $name) {
            $this->installPackage($package, $name);
        }

        $this->displayCompletion($packages);

        $this->askToStar();

        return self::SUCCESS;
    }

    protected function configureJsonColumnType(): void
    {
        if (! $this->canPrompt()) {
            return;
        }

        $choice = select(
            label: 'Default JSON column type for Commerce packages',
            options: [
                'json' => 'json (portable across MySQL/SQLite/PostgreSQL)',
                'jsonb' => 'jsonb (PostgreSQL only, supports GIN indexes)',
            ],
            default: env('COMMERCE_JSON_COLUMN_TYPE', 'json'),
        );

        $this->ensureEnvVariables([
            'COMMERCE_JSON_COLUMN_TYPE' => $choice,
        ]);
    }

    protected function displayWelcome(): void
    {
        $this->components->twoColumnDetail(
            '<fg=cyan;options=bold>AIArmada Commerce Installer</>',
            sprintf('<fg=gray>v%s</>', self::VERSION),
        );

        $this->newLine();

        note('This installer will set up the Commerce suite for your Laravel application.');
        $this->newLine();
    }

    /**
     * @return array<string, string>
     */
    protected function determinePackagesToInstall(): array
    {
        $packages = self::REQUIRED_PACKAGES;

        if ($this->option('all')) {
            return array_merge($packages, self::OPTIONAL_PACKAGES);
        }

        foreach (self::OPTIONAL_PACKAGES as $key => $label) {
            if ($this->option($key)) {
                $packages[$key] = $label;
            }
        }

        if (count($packages) === count(self::REQUIRED_PACKAGES) && $this->canPrompt()) {
            $selected = multiselect(
                label: 'Select optional packages to install',
                options: self::OPTIONAL_PACKAGES,
                hint: 'Use space to toggle packages, then press enter to continue',
            );

            foreach ($selected as $key) {
                if (isset(self::OPTIONAL_PACKAGES[$key])) {
                    $packages[$key] = self::OPTIONAL_PACKAGES[$key];
                }
            }
        }

        return $packages;
    }

    /**
     * @param  array<string, string>  $packages
     */
    protected function displayCompletion(array $packages): void
    {
        $this->newLine(2);
        $this->components->info('Commerce packages installed successfully.');
        $this->newLine();

        $this->components->bulletList(array_values($packages));
        $this->newLine();

        note('Next steps:');
        $this->components->bulletList([
            'Configure environment variables in your .env file',
            'Review published configuration files under config/',
            'Run php artisan migrate to create database tables',
            'Browse the documentation at https://github.com/aiarmada/commerce',
        ]);

        $this->newLine();
    }

    protected function installPackage(string $package, string $label): void
    {
        spin(function () use ($package): void {
            $this->publishConfiguration($package);
            $this->runMigrations($package);

            match ($package) {
                'cart' => $this->setupCart(),
                'chip' => $this->setupChip(),
                'jnt' => $this->setupJnt(),
                'filament' => $this->setupFilament(),
                default => null,
            };
        }, sprintf('Installing %s...', $label));

        $this->components->task($label);
    }

    protected function publishConfiguration(string $package): void
    {
        $parameters = [
            '--tag' => sprintf('%s-config', $package),
            '--ansi' => true,
        ];

        if ($this->option('force')) {
            $parameters['--force'] = true;
        }

        $this->callSilently('vendor:publish', $parameters);
    }

    protected function runMigrations(string $package): void
    {
        if ($package !== 'cart') {
            return;
        }

        if (config('cart.storage') !== 'database') {
            return;
        }

        $path = base_path('vendor/aiarmada/cart/database/migrations');

        if (! $this->files->isDirectory($path)) {
            return;
        }

        $this->call('migrate', ['--path' => 'vendor/aiarmada/cart/database/migrations']);
    }

    protected function setupCart(): void
    {
        // Configuration published and migrations handled in runMigrations().
    }

    protected function setupChip(): void
    {
        $this->ensureEnvVariables([
            'CHIP_COLLECT_API_KEY' => 'your-chip-collect-api-key',
            'CHIP_COLLECT_BRAND_ID' => 'your-chip-brand-id',
            'CHIP_SEND_API_KEY' => 'your-chip-send-api-key',
            'CHIP_SEND_API_SECRET' => 'your-chip-send-api-secret',
        ]);
    }

    protected function setupJnt(): void
    {
        $this->ensureEnvVariables([
            'JNT_CUSTOMER_CODE' => 'your-jnt-customer-code',
            'JNT_PASSWORD' => 'your-jnt-password',
            'JNT_PRIVATE_KEY' => 'your-jnt-private-key',
        ]);
    }

    protected function setupFilament(): void
    {
        // Filament packages bootstrap themselves through auto-discovery.
    }

    /**
     * @param  array<string, string>  $variables
     */
    protected function ensureEnvVariables(array $variables): void
    {
        $path = base_path('.env');

        if (! $this->files->exists($path)) {
            return;
        }

        $content = $this->files->get($path);
        $updated = false;

        foreach ($variables as $key => $value) {
            if (preg_match(sprintf('/^%s=.*/m', preg_quote($key, '/')), $content)) {
                continue;
            }

            $content = rtrim($content) . PHP_EOL . sprintf('%s=%s', $key, $value);
            $updated = true;
        }

        if (! $updated) {
            return;
        }

        $this->files->put($path, rtrim($content) . PHP_EOL);
    }

    protected function askToStar(): void
    {
        if (! $this->canPrompt() || $this->option('no-star')) {
            return;
        }

        if (! confirm(
            label: 'All done! Would you like to star the aiarmada/commerce repository on GitHub?',
            default: true,
        )) {
            return;
        }

        $this->components->info('Open https://github.com/aiarmada/commerce to show your support.');
    }

    protected function canPrompt(): bool
    {
        return ! $this->option('no-interaction');
    }
}
