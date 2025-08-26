<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $operation, $state, $set) {
                        if ($operation !== 'create') {
                            return;
                        }
                        $set('slug', \Illuminate\Support\Str::slug($state));
                    }),
                TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->helperText('The slug will be used in the page URL. Only lowercase letters, numbers, and hyphens are allowed.')
                    ->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            $set('slug', \Illuminate\Support\Str::slug($state));
                        }
                    })
                // Toggle::make('is_published')
                //     ->label('Published')
                //     ->helperText('Published pages are visible to visitors.')
                //     ->default(false),
            ]);
    }
}
