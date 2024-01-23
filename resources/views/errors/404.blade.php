<x-app-layout>
    <div class="pt-4">
        <img src="{{ asset('vendor/images/404.png') }}" class="img-fluid d-block mx-auto" style="max-width: 300px;" />

        <div class="col-md-8 mx-auto">
            <div class="py-3 px-2 bg-white shadow-sm border rounded text-center my-5">
                <h2>Pokémon no encontrado</h2>
                <p class="lead">
                    Al parecer no contamos con los registros de ese pokémon. Para regresar
                    a donde estabas da click en el link de abajo:
                </p>
                <a class="lead text-decoration-none text-pokemon-red" href="{{ route('pokemons.index') }}">
                    Volver
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
