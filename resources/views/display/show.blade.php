<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display - {{ $organization }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .watermark {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: rgba(0, 0, 0, 0.4);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            backdrop-filter: blur(10px);
            z-index: 9999;
            pointer-events: none;
            user-select: none;
        }

        .fade-enter {
            animation: fadeIn 0.5s ease-in;
        }

        .fade-exit {
            animation: fadeOut 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>
</head>
<body class="bg-black overflow-hidden">
    <div x-data="{
        contents: [],
        currentIndex: 0,
        loading: true,
        orgSlug: '{{ $organization }}',
        playlistId: '{{ $playlist ?? '' }}',
        loop: true,
        providerName: 'Digital Content Hub',
        init() {
            this.loadContents();
        },
        loadContents() {
            const url = this.playlistId 
                ? `/api/display/${this.orgSlug}/playlist/${this.playlistId}`
                : `/api/display/${this.orgSlug}`;
            
            fetch(url)
            .then(r => r.json())
            .then(d => {
                this.contents = d.contents || [];
                this.loop = d.loop;
                this.loading = false;
                if (this.contents.length > 0) {
                    this.play();
                }
            })
            .catch(err => {
                console.error('Error loading contents:', err);
                this.loading = false;
            });
        },
        play() {
            if (this.contents.length === 0) return;
            
            const content = this.contents[this.currentIndex];
            const duration = content.duration * 1000;
            
            setTimeout(() => {
                this.next();
            }, duration);
        },
        next() {
            if (this.currentIndex < this.contents.length - 1) {
                this.currentIndex++;
                this.play();
            } else if (this.loop) {
                this.currentIndex = 0;
                this.play();
            }
        },
        getCurrentContent() {
            return this.contents[this.currentIndex] || null;
        }
    }" class="w-screen h-screen flex items-center justify-center relative">

        <!-- Watermark -->
        <div class="watermark">
            <span x-text="providerName"></span>
        </div>

        <template x-if="loading">
            <div class="text-white text-2xl">Loading...</div>
        </template>

        <template x-if="!loading && contents.length === 0">
            <div class="text-white text-2xl">No content available</div>
        </template>

        <template x-if="!loading && contents.length > 0">
            <div class="w-full h-full relative">
                <template x-for="(content, index) in contents" :key="content.id">
                    <div x-show="index === currentIndex"
                        class="absolute inset-0 flex items-center justify-center fade-enter"
                        x-transition:enter="transition ease-out duration-500"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-500"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0">

                        <!-- Image -->
                        <template x-if="content.type === 'image'">
                            <img :src="content.file_url"
                                :alt="content.title"
                                class="max-w-full max-h-full object-contain">
                        </template>

                        <!-- Video -->
                        <template x-if="content.type === 'video'">
                            <video :src="content.file_url"
                                class="max-w-full max-h-full object-contain"
                                autoplay muted loop playsinline>
                            </video>
                        </template>

                        <!-- PDF -->
                        <template x-if="content.type === 'pdf'">
                            <iframe :src="content.file_url"
                                class="w-full h-full border-0">
                            </iframe>
                        </template>
                    </div>
                </template>
            </div>
        </template>
    </div>
</body>
</html>
