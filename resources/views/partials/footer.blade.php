<footer class="site-footer">
    <div class="container">
        <div class="row">

            <div class="col-6">
                <p class="copyright-text mb-0">
                    <strong>{{ config('piko.name') }}</strong>
                    <br>
                    <a href="mailto:{{ config('piko.email') }}">{{ config('piko.email') }}</a>
                    <br>
                    @foreach (config('piko.phones') as $phone)
                        <a href="tel:+{{ $phone['e164'] }}">{{ $phone['display'] }}</a>@if (! $loop->last) / @endif
                    @endforeach
                    <br>
                    {{ config('piko.location') }}
                </p>
            </div>

            <div class="col-6">
                <p class="copyright-text mb-0">
                    &copy; {{ now()->year }} {{ config('piko.name') }}<br><br>
                    @include('partials.legal-links')<br><br>
                    Design Template by <a href="https://templatemo.com/page/1" target="_blank" rel="noopener">TemplateMo</a><br>
                    Distributed by <a href="https://themewagon.com/" target="_blank" rel="noopener">ThemeWagon</a>
                </p>
            </div>

            <div class="col-lg-3 col-5 ms-auto">
                <ul class="social-icon">
                    @foreach (config('piko.social') as $network => $url)
                        @if ($url)
                            <li><a href="{{ $url }}" target="_blank" rel="noopener" class="social-icon-link bi-{{ $network }}"
                                    aria-label="{{ ucfirst($network) }}"></a></li>
                        @endif
                    @endforeach
                </ul>
            </div>

        </div>
    </div>
</footer>
