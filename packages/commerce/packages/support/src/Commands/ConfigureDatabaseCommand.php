<?php

declare(strict_types=1);

namespace AIArmada\CommerceSupport\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Attribute\AsCommand;

use function Laravel\Prompts\note;
use function Laravel\Prompts\select;

#[AsCommand(name: 'commerce:configure-database')]
final class ConfigureDatabaseCommand extends Command
{
    protected $description = 'Configure database-related options for AIArmada Commerce (e.g., JSON vs JSONB).';

    public function __construct(protected Filesystem $files)
    {
        parent::__construct();
    }

    public function __invoke(): int
    {
        $this->components->info('AIArmada Commerce • Database Configuration');
        $this->newLine();

        $choice = select(
            label: 'Select default JSON column type for Commerce packages',
            options: [
                'json' => 'json (portable across MySQL/SQLite/PostgreSQL)',
                'jsonb' => 'jsonb (PostgreSQL only, supports GIN indexes)',
            ],
            default: env('COMMERCE_JSON_COLUMN_TYPE', 'json'),
        );

        $this->setEnv('COMMERCE_JSON_COLUMN_TYPE', $choice);

        note('Saved COMMERCE_JSON_COLUMN_TYPE='.$choice.' to your .env file.');
        note('You can override per-package with VOUCHERS_JSON_COLUMN_TYPE, etc.');

        $this->newLine();
        $this->components->info('Done. Run your migrations when ready: php artisan migrate');

        return self::SUCCESS;
    }

    private function setEnv(string $key, string $value): void
    {
        $path = base_path('.env');
        if (! $this->files->exists($path)) {
            return;
        }

        $content = $this->files->get($path);

        if (preg_match('/^'.preg_quote($key, '/').'=.*/m', $content)) {
            $content = (string) preg_replace(
                '/^'.preg_quote($key, '/').'=.*/m',
                $key.'='.$value,
                $content,
            );
        } else {
            $content = mb_rtrim($content).PHP_EOL.$key.'='.$value.PHP_EOL;
        }

        $this->files->put($path, $content);
    }
}
