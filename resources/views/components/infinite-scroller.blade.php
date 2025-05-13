<div class="page-load-status">
    <div class="d-flex justify-content-center">
        <div class="infinite-scroll-request spinner-border text-pokemon-red" role="status">
            <span class="sr-only"></span>
        </div>
        <div class="infinite-scroll-last">
            <img src="{{ asset('/vendor/images/pikachu.jpg') }}"
                class="d-block mx-auto rounded rounded-3 shadow border border-light" width="200"
                height="200" alt="pikachu llorando" />
            <p class="fst-italic mt-4">
                ¡Ooops...! Estos son todos los registros hasta ahora.
            </p>
        </div>
        <div class="infinite-scroll-error">
            <img class="d-block mx-auto rounded rounded-3 shadow border border-light"
                src="{{ asset('/vendor/images/rocket.png') }}" width="200" height="200"
                alt="equipo rocket" />
            <p class="fst-italic mt-4">
                ¡Oh no, el Equipo Rocket! tenemos un problema...
            </p>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://unpkg.com/infinite-scroll@4/dist/infinite-scroll.pkgd.min.js"></script>
@endpush
