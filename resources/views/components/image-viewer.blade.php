@props(['url', 'alt' => 'Gambar lampiran', 'compact' => false])

@once
<style>
    /* USC Theme Override for Viewer.js */
    .viewer-container {
        background-color: rgba(0, 0, 0, 0.2) !important;
        backdrop-filter: blur(4px);
    }
    /* Full circle close button */
    .viewer-button {
        background-color: #ffffffff !important; /* bg-usc-50 */
        width: 44px !important;
        height: 44px !important;
        right: 24px !important;
        top: 24px !important;
        border-radius: 50% !important;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1) !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .viewer-button:hover {
        background-color: #ecfdf5 !important; /* bg-usc-100 */
        transform: scale(1.1) rotate(90deg) !important;
    }
    .viewer-button::before, .viewer-button::after {
        content: '' !important;
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        width: 20px !important;
        height: 3px !important;
        background-color: #64748b !important; /* Slate color */
        border-radius: 2px !important;
        background-image: none !important;
        filter: none !important;
    }
    .viewer-button::before {
        transform: translate(-50%, -50%) rotate(45deg) !important;
    }
    .viewer-button::after {
        transform: translate(-50%, -50%) rotate(-45deg) !important;
    }
    /* Toolbar buttons */
    .viewer-toolbar > ul > li {
        background-color: #ffffff !important; /* bg-usc-50 */
        width: 40px !important;
        height: 40px !important;
        border-radius: 50% !important;
        margin: 0 6px !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1) !important;
    }
    .viewer-toolbar > ul > li:hover {
        background-color: #ecfdf5 !important;
        transform: translateY(-4px) scale(1.05) !important;
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15) !important;
    }
    .viewer-toolbar > ul > li::before {
        margin: 10px !important;
        filter: invert(1) opacity(0.6) !important; /* Make white icon slate/gray */
    }
</style>
@endonce

<div x-data="{
        viewer: null,
        openViewer() {
            if (typeof Viewer === 'undefined') {
                let script = document.createElement('script');
                script.src = 'https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.js';
                
                let link = document.createElement('link');
                link.rel = 'stylesheet';
                link.href = 'https://cdnjs.cloudflare.com/ajax/libs/viewerjs/1.11.6/viewer.min.css';
                
                script.onload = () => {
                    this.initViewer();
                };
                
                document.head.appendChild(link);
                document.head.appendChild(script);
            } else {
                this.initViewer();
            }
        },
        initViewer() {
            if (!this.viewer) {
                this.viewer = new Viewer(this.$refs.imageWrapper, {
                    inline: false,
                    button: true,
                    navbar: false,
                    title: false,
                    toolbar: {
                        zoomIn: 1, zoomOut: 1, oneToOne: 1, reset: 1, rotateLeft: 1, rotateRight: 1,
                    },
                    tooltip: true, movable: true, zoomable: true, rotatable: true, scalable: true, transition: true, fullscreen: true, keyboard: true,
                });
            }
            this.viewer.show();
        }
    }" 
    @click="openViewer()"
    class="group relative overflow-hidden rounded-xl border border-gray-200 bg-white p-1 shadow-sm transition-all duration-300 hover:border-usc-400 hover:shadow-md hover:shadow-usc-100/50 cursor-zoom-in"
>
    <div x-ref="imageWrapper" class="overflow-hidden rounded-lg">
        <img src="{{ $url }}"
             alt="{{ $alt }}" 
             class="w-full object-cover transition-transform duration-500 ease-out group-hover:scale-105">
    </div>
    
    <!-- Overlay hint -->
    <div class="pointer-events-none absolute inset-0 flex items-center justify-center transition-colors duration-300 group-hover:bg-usc-900/5">
        <div class="translate-y-4 opacity-0 transition-all duration-300 group-hover:translate-y-0 group-hover:opacity-100 rounded-full bg-white/95 {{ $compact ? 'p-2' : 'px-4 py-2 gap-2' }} text-sm font-semibold text-usc-700 shadow-sm backdrop-blur flex items-center border border-usc-100">
            <x-nav-icon name="zoom-in" class="{{ $compact ? 'h-4 w-4' : 'h-4 w-4' }}" />
            @if(!$compact)
                Klik untuk perbesar
            @endif
        </div>
    </div>
</div>
