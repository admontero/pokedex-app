<x-app-layout
    title="Pokémon no encontrado"
    description="No encontramos el Pokémon solicitado. Vuelve al inicio o realiza otra búsqueda."
>
    <div class="pt-4">
        <img
            src="{{ asset('vendor/images/404.png') }}"
            class="img-fluid d-block mx-auto"
            style="max-width: 300px;"
            alt=""
            width="784"
            height="486"
            decoding="async"
        />

        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="py-3 px-2 bg-white shadow-sm border rounded text-center my-5">
                    <h1 class="h2">Pokémon no encontrado</h1>
                    <p class="lead">
                        Al parecer no contamos con los registros de ese Pokémon. Puedes volver
                        al inicio o probar con otra búsqueda.
                    </p>
                    <a class="btn btn-pokemon-red text-white" href="{{ route('pokemons.index') }}">
                        Volver al inicio
                    </a>

                    <div class="mt-4 text-start">
                        <x-pokemon.form-search />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
