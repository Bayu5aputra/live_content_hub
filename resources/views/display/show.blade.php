<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display - {{ $organization }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-black">
    <div x-data="{
        contents: [],
        currentIndex: 0,
        loading: true,
        orgSlug: '{{ $organization }}',
        playlistId: '{{ $playlist ?? null }}',
        loop: true,
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
    }" class="w-screen h-screen flex items-center justify-center">

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
                        class="absolute inset-0 flex items-center justify-center"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100">

                        <!-- Image -->
                        <template x-if="content.type === 'image'">
                            <img :src="content.file_url"
                                :alt="content.title"
                                class="max-w-full max-h-full object-contain">
                        </template>

                        <!-- Video -->
                        <template x-if="content.type === 'video'">
                            <video :src="content.file_url"
                                class="max-w-full max-h-full"
                                autoplay muted loop>
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

                <!-- Info Overlay -->
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-6">
                    <h2 class="text-white text-2xl font-bold"
                        x-text="getCurrentContent()?.title"></h2>
                    <p class="text-white/80 text-sm"
                        x-text="`${currentIndex + 1} / ${contents.length}`"></p>
                </div>
            </div>
        </template>
    </div>
</body>
</html><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display - {{ $organization }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-black">
    <div x-data="{
        contents: [],
        currentIndex: 0,
        loading: true,
        orgSlug: '{{ $organization }}',
        playlistId: '{{ $playlist ?? null }}',
        loop: true,
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
    }" class="w-screen h-screen flex items-center justify-center">

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
                        class="absolute inset-0 flex items-center justify-center"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100">

                        <!-- Image -->
                        <template x-if="content.type === 'image'">
                            <img :src="content.file_url"
                                :alt="content.title"
                                class="max-w-full max-h-full object-contain">
                        </template>

                        <!-- Video -->
                        <template x-if="content.type === 'video'">
                            <video :src="content.file_url"
                                class="max-w-full max-h-full"
                                autoplay muted loop>
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

                <!-- Info Overlay -->
                <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/80 to-transparent p-6">
                    <h2 class="text-white text-2xl font-bold"
                        x-text="getCurrentContent()?.title"></h2>
                    <p class="text-white/80 text-sm"
                        x-text="`${currentIndex + 1} / ${contents.length}`"></p>
                </div>
            </div>
        </template>
    </div>
</body>
</html>
