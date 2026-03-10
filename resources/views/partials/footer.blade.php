<footer class="footer-area bg-white text-center rounded-top-7">
    <div class="d-flex flex-wrap justify-content-center align-items-center gap-2">
        <p class="fs-14 mb-0">
            {{ setting('footer_text', '© ' . date('Y') . ' School of Basic and Remedial Studies, University of Maiduguri. All Rights Reserved.') }}
        </p>
        @if (setting('footer_powered_by'))
            <span class="text-muted mx-2">|</span>
            <p class="fs-14 mb-0">
                Powered by
                @if (setting('footer_powered_by_url'))
                    <a href="{{ setting('footer_powered_by_url') }}" target="_blank" class="text-decoration-none text-primary">
                        {{ setting('footer_powered_by') }}
                    </a>
                @else
                    <span class="text-primary">{{ setting('footer_powered_by') }}</span>
                @endif
            </p>
        @endif
    </div>
</footer>
