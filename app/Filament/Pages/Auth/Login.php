<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;

class Login extends BaseLogin
{
    public function getHeading(): string
    {
        return ' Topal sa xalis';
    }

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('login')->label('Email ou Pseudo')->required()->autofocus()->autocomplete('username'),
            TextInput::make('password')->password()->label("Mot de passe")->required()->autocomplete('current-password')->revealable(),
            Checkbox::make('remember')->label('Se souvenir de moi'),
        ]);
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        return [$field => $data['login'], 'password' => $data['password']];
    }
}
