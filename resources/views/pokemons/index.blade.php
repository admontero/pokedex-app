<x-app-layout>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xxl-5 g-2" id="main-content">
        @foreach ($pokemons as $pokemon)
            <x-pokemon-card :$pokemon />
        @endforeach
    </div>

    <div class="my-5">
        <div class="page-load-status">
            <div class="d-flex justify-content-center">
                <div class="infinite-scroll-request spinner-border text-pokemon-red" role="status">
                    <span class="sr-only"></span>
                </div>
                <div class="infinite-scroll-last">
                    <img
                        src="{{ asset('/vendor/images/pikachu.jpg') }}"
                        class="d-block mx-auto rounded rounded-3 shadow border border-light"
                        width="200"
                        height="200"
                        alt="pikachu llorando"
                    />
                    <p class="fst-italic mt-4">
                        ¡Ooops...! Al parecer hasta aquí llegan mis registros.
                    </p>
                </div>
                <div class="infinite-scroll-error">
                    <img
                        class="d-block mx-auto rounded rounded-3 shadow border border-light"
                        src="{{ asset('/vendor/images/rocket.png') }}"
                        width="200"
                        height="200"
                        alt="equipo rocket"
                    />
                    <p class="fst-italic mt-4">
                        ¡Houston tenemos un problema!
                    </p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/infinite-scroll@4/dist/infinite-scroll.pkgd.min.js"></script>

        <script>
            let elem = document.querySelector('#main-content');

            let infScroll = new InfiniteScroll(elem, {
                path: '/?page=@{{#}}',
                append: '.col',
                status: '.page-load-status',
            });

            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.card').forEach(function (el) {
                    el.addEventListener('click', function () {
                        el.querySelector('a').click()
                    })
                })
            })
        </script>
    @endpush
</x-app-layout>
