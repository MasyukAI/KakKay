<?php

declare(strict_types=1);

namespace AIArmada\Cart\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\spin;

class InstallCommerceCommand extends Command
{
    protected $signature = 'commerce:install
                            {--all : Install all optional packages}
                            {--chip : Install CHIP payment gateway}
                            {--jnt : Install J&T Express shipping}
                            {--filament : Install Filament UI packages}
                            {--force : Overwrite existing config files}';

    protected $description = 'Install and configure the Commerce package suite';

    /** @var array<string, string> */
    protected array $requiredPackages = [
        'cart' => 'Cart Management',
        'stock' => 'Stock Management',
        'vouchers' => 'Voucher System',
    ];

    /** @var array<string, string> */
    protected array $optionalPackages = [
        'chip' => 'CHIP Payment Gateway',
        'jnt' => 'J&T Express Shipping',
        'filament' => 'Filament UI Components',
    ];

    public function handle(): int
    {
        $this->displayWelcome();

        $packagesToInstall = $this->determinePackagesToInstall();

        if (empty($packagesToInstall)) {
            $this->components->error('No packages selected for installation.');

            return self::FAILURE;
        }

        $this->components->info('Installing selected packages...');
        $this->newLine();

        foreach ($packagesToInstall as $package => $name) {
            $this->installPackage($package, $name);
        }

        $this->displayCompletion($packagesToInstall);

        return self::SUCCESS;
    }

    protected function displayWelcome(): void
    {
        $this->components->twoColumnDetail(
            '<fg=cyan;options=bold>Commerce Package Installer</>',
            '<fg=gray>v1.0</>'
        );
        $this->newLine();

        note('This installer will set up the Commerce package suite for your application.');
        $this->newLine();
    }

    /**
     * @return array<string, string>
     */
    protected function determinePackagesToInstall(): array
    {
        // Required packages are always included
        $packages = $this->requiredPackages;

        // Check if --all flag is provided
        if ($this->option('all')) {
            return array_merge($packages, $this->optionalPackages);
        }

        // Check for specific optional package flags
        foreach ($this->optionalPackages as $key => $name) {
            if ($this->option($key)) {
                $packages[$key] = $name;
            }
        }

        // If no options provided, prompt user
        if (count($packages) === count($this->requiredPackages)) {
            $selected = multiselect(
                label: 'Select optional packages to install',
                options: $this->optionalPackages,
                default: [],
                hint: 'Use space to select, enter to continue'
            );

            foreach ($selected as $key) {
                $packages[$key] = $this->optionalPackages[$key];
            }
        }

        return $packages;
    }

    protected function installPackage(string $package, string $name): void
    {
        spin(
            callback: function () use ($package) {
                // Publish configuration
                $this->publishConfiguration($package);

                // Run migrations
                $this->runMigrations($package);

                // Package-specific setup
                match ($package) {
                    'cart' => $this->setupCart(),
                    'chip' => $this->setupChip(),
                    'jnt' => $this->setupJnt(),
                    'filament' => $this->setupFilament(),
                    default => null,
                };
            },
            message: "Installing {$name}..."
        );

        $this->components->task($name);
    }

    protected function publishConfiguration(string $package): void
    {
        $force = $this->option('force') ? ['--force' => true] : [];

        $this->callSilently('vendor:publish', array_merge([
            '--tag' => "{$package}-config",
            '--ansi' => true,
        ], $force));
    }

    protected function runMigrations(string $package): void
    {
        // Migrations are auto-discovered and run via package service providers
        // This is just a placeholder if we need package-specific migration logic
    }

    protected function setupCart(): void
    {
        // Create carts table if using database storage
        if (config('cart.storage') === 'database') {
            $this->call('migrate', ['--path' => 'vendor/aiarmada/cart/database/migrations']);
        }
    }

    protected function setupChip(): void
    {
        // Create .env entries for CHIP if they don't exist
        $this->ensureEnvVariables([
            'CHIP_COLLECT_API_KEY' => 'your_chip_collect_api_key',
            'CHIP_COLLECT_BRAND_ID' => 'your_chip_brand_id',
            'CHIP_SEND_API_KEY' => 'your_chip_send_api_key',
            'CHIP_SEND_API_SECRET' => 'your_chip_send_api_secret',
        ]);
    }

    protected function setupJnt(): void
    {
        // Create .env entries for J&T Express if they don't exist
        $this->ensureEnvVariables([
            'JNT_CUSTOMER_CODE' => 'your_jnt_customer_code',
            'JNT_PASSWORD' => 'your_jnt_password',
            'JNT_PRIVATE_KEY' => 'your_jnt_private_key',
        ]);
    }

    protected function setupFilament(): void
    {
        // Filament packages (filament-cart, filament-chip) are automatically loaded
        // This is just a placeholder for any specific setup needed
    }

    /**
     * @param  array<string, string>  $variables
     */
    protected function ensureEnvVariables(array $variables): void
    {
        $envPath = base_path('.env');

        if (! File::exists($envPath)) {
            return;
        }

        $envContent = File::get($envPath);
        $changes = false;

        foreach ($variables as $key => $defaultValue) {
            // Check if variable already exists
            if (preg_match("/^{$key}=/m", $envContent)) {
                continue;
            }

            // Add to .env file
            $envContent .= "\n{$key}={$defaultValue}";
            $changes = true;
        }

        if ($changes) {
            File::put($envPath, $envContent);
        }
    }

    /**
     * @param  array<string, string>  $installedPackages
     */
    protected function displayCompletion(array $installedPackages): void
    {
        $this->newLine(2);
        $this->components->info('Commerce packages installed successfully!');
        $this->newLine();

        $this->components->bulletList(
            array_values($installedPackages)
        );

        $this->newLine();

        note('Next steps:');
        $this->components->bulletList([
            'Configure environment variables in your .env file',
            'Run php artisan migrate to create database tables',
            'Review published configuration files in config/',
            'Read the documentation at https://docs.your-domain.com',
        ]);

        $this->newLine();
    }
}
