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
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;

#[AsCommand(name: 'commerce:install', aliases: ['install:commerce'])]
final class InstallCommand extends Command
{
    private const VERSION = '1.0.0';

    /**
     * @var array<string, string>
     */
    private const REQUIRED_PACKAGES = [];

    /**
     * @var array<string, string>
     */
    private const OPTIONAL_PACKAGES = [
        'cart' => 'Cart Management (Core)',
        'stock' => 'Stock Management',
        'vouchers' => 'Voucher System',
        'chip' => 'CHIP Payment Gateway',
        'jnt' => 'J&T Express Shipping',
        'docs' => 'Document Generation (Invoices, Receipts, PDFs)',
        'filament-cart' => 'Filament Cart Admin',
        'filament-chip' => 'Filament CHIP Admin',
        'filament-vouchers' => 'Filament Vouchers Admin',
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

    public function __invoke(): int
    {
        $this->displayWelcome();

        // If --cart is passed, mirror Filament's --panels: set up everything
        if ($this->option('cart')) {
            if (! class_exists(\AIArmada\Cart\CartServiceProvider::class)) {
                $this->components->error('Please require [aiarmada/commerce] (which includes [aiarmada/cart]) before attempting to install.');

                return self::FAILURE;
            }

            // Configure env defaults before installing packages
            $this->configureJsonColumnType();

            $packages = array_merge(self::REQUIRED_PACKAGES, self::OPTIONAL_PACKAGES);
        } else {
            // Ask JSON column type preference once and persist globally
            $this->configureJsonColumnType();

            $packages = $this->determinePackagesToInstall();
        }

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

    /**
     * @return array<InputOption>
     */
    protected function getOptions(): array
    {
        return [
            new InputOption('cart', null, InputOption::VALUE_NONE, 'Set up Cart management (core package)'),
            new InputOption('all', null, InputOption::VALUE_NONE, 'Install all optional packages'),
            new InputOption('stock', null, InputOption::VALUE_NONE, 'Install Stock management'),
            new InputOption('vouchers', null, InputOption::VALUE_NONE, 'Install Voucher system'),
            new InputOption('chip', null, InputOption::VALUE_NONE, 'Install CHIP payment gateway integrations'),
            new InputOption('jnt', null, InputOption::VALUE_NONE, 'Install J&T Express shipping integrations'),
            new InputOption('docs', null, InputOption::VALUE_NONE, 'Install Document generation (invoices, receipts, PDFs)'),
            new InputOption('filament-cart', null, InputOption::VALUE_NONE, 'Install Filament Cart admin'),
            new InputOption('filament-chip', null, InputOption::VALUE_NONE, 'Install Filament CHIP admin'),
            new InputOption('filament-vouchers', null, InputOption::VALUE_NONE, 'Install Filament Vouchers admin'),
            new InputOption('force', 'F', InputOption::VALUE_NONE, 'Overwrite existing configuration files'),
            new InputOption('no-star', null, InputOption::VALUE_NONE, 'Skip GitHub star prompt'),
        ];
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
                'stock' => $this->setupStock(),
                'vouchers' => $this->setupVouchers(),
                'chip' => $this->setupChip(),
                'jnt' => $this->setupJnt(),
                'docs' => $this->setupDocs(),
                'filament-cart' => $this->setupFilamentCart(),
                'filament-chip' => $this->setupFilamentChip(),
                'filament-vouchers' => $this->setupFilamentVouchers(),
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

        $exitCode = $this->callSilent('vendor:publish', $parameters);

        // If the first attempt fails (no matching tag), it might be because the package
        // hasn't been registered yet, so we'll try to force a second publish after boot
        if ($exitCode !== 0 && ! $this->option('force')) {
            $this->call('vendor:publish', array_merge($parameters, ['--force' => true]));
        }
    }

    protected function runMigrations(string $package): void
    {
        // Publish migrations for all packages that have them
        $parameters = [
            '--tag' => sprintf('%s-migrations', $package),
            '--ansi' => true,
        ];

        if ($this->option('force')) {
            $parameters['--force'] = true;
        }

        $this->callSilent('vendor:publish', $parameters);

        // For cart package, run migrations if using database storage
        if ($package === 'cart' && config('cart.storage') === 'database') {
            $path = base_path('vendor/aiarmada/cart/database/migrations');

            if ($this->files->isDirectory($path)) {
                $this->call('migrate', ['--path' => 'vendor/aiarmada/cart/database/migrations']);
            }
        }
    }

    protected function setupCart(): void
    {
        // Configuration published and migrations handled in runMigrations().
    }

    protected function setupStock(): void
    {
        // Stock package bootstraps itself through auto-discovery.
    }

    protected function setupVouchers(): void
    {
        // Vouchers package bootstraps itself through auto-discovery.
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

    protected function setupDocs(): void
    {
        // Document generation package bootstraps itself through auto-discovery.
    }

    protected function setupFilamentCart(): void
    {
        // Filament Cart package bootstraps itself through auto-discovery.
    }

    protected function setupFilamentChip(): void
    {
        // Filament CHIP package bootstraps itself through auto-discovery.
    }

    protected function setupFilamentVouchers(): void
    {
        // Filament Vouchers package bootstraps itself through auto-discovery.
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

            $content = mb_rtrim($content).PHP_EOL.sprintf('%s=%s', $key, $value);
            $updated = true;
        }

        if (! $updated) {
            return;
        }

        $this->files->put($path, mb_rtrim($content).PHP_EOL);
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
