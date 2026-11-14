@php
    $detail = $message ?? (isset($exception) ? $exception->getMessage() : null);
@endphp

<x-app-layout
    title="Error del servidor"
    description="No pudimos completar la solicitud. Inténtalo de nuevo en unos momentos."
>
    <div class="pt-4">
        <x-picture
            base="vendor/images/500"
            fallback="vendor/images/500.png"
            :width="300"
            :height="162"
            class="img-fluid d-block mx-auto"
        />

        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="py-3 px-2 text-center my-5">
                    <div class="alert alert-danger lead" role="alert">
                        <h1 class="h5 fw-semibold mb-0">Error</h1>
                        <hr>
                        No pudimos completar la solicitud. Inténtalo de nuevo en unos momentos.

                        @if (config('app.debug') && $detail)
                            <hr>
                            <small class="d-block text-start text-muted">{{ $detail }}</small>
                        @endif
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a class="btn btn-pokemon-red text-white" href="{{ url()->current() }}">
                            Reintentar
                        </a>
                        <a class="btn btn-secondary text-white" href="{{ route('pokemons.index') }}">
                            Ir al inicio
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
