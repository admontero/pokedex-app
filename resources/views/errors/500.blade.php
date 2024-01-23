<x-app-layout>
    <div class="pt-4">
        <img src="{{ asset('vendor/images/500.png') }}" class="img-fluid d-block mx-auto" style="max-width: 300px;" />

        <div class="col-md-8 mx-auto">
            <div class="py-3 px-2 text-center my-5">
                <div class="alert alert-danger lead" role="alert">
                    <span class="fw-semibold">Error</span>
                    <hr>
                    {{ $error ?? 'Error al consultar la API, inténtelo de nuevo más tarde...' }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
