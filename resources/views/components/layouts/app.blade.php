<!DOCTYPE html>
    <html lang="en" class="h-full">
    <head>
      <meta charset="utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1" />
      <title>Kak Kay - Counsellor • Therapist • KKDI Creator</title>

      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link href="https://fonts.googleapis.com/css2?family=Caveat+Brush&family=Montserrat:wght@700;800;900&family=Poppins:wght@400;500;600;700&family=Playfair+Display:ital,wght@0,400;0,600;1,400&family=Shadows+Into+Light&display=swap" rel="stylesheet">
      @vite('resources/css/cart.css')
      @filamentStyles
    </head>
    <body class="h-full bg-[#1a0d1a] bg-[image:var(--grad-hero)] text-white font-serif leading-[1.5] antialiased">
        <div id="app-shell" class="min-h-full">
            {{ $slot }}
            @livewire('notifications')
        </div>
        @filamentScripts

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const originalScrollIntoView = Element.prototype.scrollIntoView;

                Element.prototype.scrollIntoView = function(options) {
                    // Only override if options is an object (modern browsers)
                    if (typeof options === 'object' && options !== null) {
                        // Force left to current scroll, but allow vertical scroll
                        const rect = this.getBoundingClientRect();
                        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                        window.scrollTo({
                            top: rect.top + scrollTop - 100, // adjust offset for header if needed
                            left: window.pageXOffset || document.documentElement.scrollLeft,
                            behavior: options.behavior || 'smooth'
                        });
                    } else {
                        // fallback to default
                        originalScrollIntoView.call(this, options);
                    }
                };

                // Restore original on page unload
                window.addEventListener('beforeunload', function() {
                    Element.prototype.scrollIntoView = originalScrollIntoView;
                });
            });
        </script>
    </body>
</html>
