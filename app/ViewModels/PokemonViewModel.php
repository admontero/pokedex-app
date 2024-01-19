<?php

namespace App\ViewModels;

use Spatie\ViewModels\ViewModel;

class PokemonViewModel extends ViewModel
{
    public function __construct(
        public array $pokemon
    ){}
}
