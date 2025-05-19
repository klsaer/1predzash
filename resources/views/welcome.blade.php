<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Music App</title>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Montserrat', sans-serif;
                background-color: #121212;
                color: #ffffff;
            }
            .sidebar {
                background-color: #000000;
                width: 232px;
            }
            .main-content {
                background: linear-gradient(#1A1A1A, #121212);
            }
            .card {
                background: #282828;
                transition: all 0.3s ease;
            }
            .card:hover {
                background: #383838;
                transform: scale(1.03);
            }
            .card img {
                width: 160px;
                height: 160px;
                object-fit: cover;
            }
        </style>
        <!-- Добавляем Howler.js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/howler/2.2.3/howler.min.js"></script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="flex h-screen">
            <!-- Sidebar -->
            <div class="sidebar p-6">
                <div class="mb-8">
                    <h1 class="text-2xl font-bold text-white">Music App</h1>
                </div>
                <nav>
                    <ul class="space-y-4">
                        <li><a href="{{ url('/') }}" class="text-white font-semibold">Главная</a></li>
                        <li><a href="{{ route('login') }}" class="text-gray-400 hover:text-white">Вход</a></li>
                        <li><a href="{{ route('register') }}" class="text-gray-400 hover:text-white">Регистрация</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Поиск</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white">Моя медиатека</a></li>
                    </ul>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="main-content flex-1 p-8 overflow-y-auto">
                <!-- Добавляем панель управления плеером -->
                <div class="player-bar fixed bottom-0 left-0 right-0 bg-gray-900 p-4 flex items-center">
                    <div class="flex-1 flex items-center">
                        <button id="play-pause" class="bg-transparent text-white p-2 rounded-full hover:bg-gray-700 mr-4">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path id="play-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                <path id="pause-icon" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6"></path>
                            </svg>
                        </button>
                        <div class="text-white text-sm">
                            <div id="now-playing">Не воспроизводится</div>
                            <div class="flex items-center mt-1">
                                <span id="current-time">0:00</span>
                                <input type="range" id="progress" class="mx-2 w-64" value="0">
                                <span id="duration">0:00</span>
                            </div>
                        </div>
                    </div>
                    <div class="volume-control flex items-center">
                        <svg class="w-5 h-5 text-white mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M12 6a7.975 7.975 0 015.657 2.343m0 0a7.975 7.975 0 010 11.314m-11.314 0a7.975 7.975 0 010-11.314m0 0a7.975 7.975 0 015.657-2.343"></path>
                        </svg>
                        <input type="range" id="volume" class="w-24" min="0" max="1" step="0.1" value="0.7">
                    </div>
                </div>

                <div class="mb-8">
                    <h2 class="text-2xl font-bold mb-6">Популярные треки</h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                        @foreach(App\Models\Song::all() as $song)
                        <div class="card p-4 rounded-lg relative group" onclick="playSong('{{ asset($song->audio_path) }}', '{{ $song->title }}', '{{ $song->artist }}')">
                            <div class="relative mb-4">
                                @if($song->cover_path)
                                <img src="{{ asset($song->cover_path) }}" alt="Album Cover" class="w-full rounded shadow-lg">
                                @else
                                <img src="https://via.placeholder.com/160" alt="Album Cover" class="w-full rounded shadow-lg">
                                @endif
                            </div>
                            <h3 class="font-semibold text-white truncate">{{ $song->title }}</h3>
                            <p class="text-gray-400 text-sm mt-1 truncate">{{ $song->artist }}</p>
                            @if($song->album)
                            <p class="text-gray-500 text-xs mt-1 truncate">{{ $song->album }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <script>
            let sound = null;
            let currentSong = null;
            let isSeeking = false;
            
            function formatTime(secs) {
                const minutes = Math.floor(secs / 60) || 0;
                const seconds = Math.floor(secs % 60) || 0;
                return `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
            }
            
            function playSong(songUrl, songTitle, songArtist) {
                if (sound) {
                    sound.stop();
                }
                
                document.querySelectorAll('.playing').forEach(el => {
                    el.classList.remove('playing');
                });
                
                event.currentTarget.classList.add('playing');
                
                sound = new Howl({
                    src: [songUrl],
                    html5: true,
                    volume: document.getElementById('volume').value,
                    onplay: function() {
                        document.getElementById('play-icon').classList.add('hidden');
                        document.getElementById('pause-icon').classList.remove('hidden');
                        document.getElementById('now-playing').textContent = `${songTitle} - ${songArtist}`;
                        updateProgress();
                    },
                    onpause: function() {
                        document.getElementById('play-icon').classList.remove('hidden');
                        document.getElementById('pause-icon').classList.add('hidden');
                    },
                    onend: function() {
                        resetPlayerUI();
                    },
                    onstop: function() {
                        resetPlayerUI();
                    },
                    onseek: function() {
                        isSeeking = false;
                        updateProgress();
                    }
                });
                
                sound.play();
                currentSong = {url: songUrl, title: songTitle, artist: songArtist};
            }
            
            function resetPlayerUI() {
                document.getElementById('play-icon').classList.remove('hidden');
                document.getElementById('pause-icon').classList.add('hidden');
                document.getElementById('progress').value = 0;
                document.getElementById('current-time').textContent = '0:00';
            }
            
            function updateProgress() {
                if (!sound || isSeeking) return;
                
                const progress = document.getElementById('progress');
                const currentTime = document.getElementById('current-time');
                const duration = document.getElementById('duration');
                
                const seek = sound.seek() || 0;
                const durationVal = sound.duration() || 1;
                
                progress.value = (seek / durationVal) * 100;
                currentTime.textContent = formatTime(seek);
                duration.textContent = formatTime(durationVal);
                
                if (sound.playing()) {
                    requestAnimationFrame(updateProgress);
                }
            }
            
            document.getElementById('play-pause').addEventListener('click', function() {
                if (!sound) return;
                
                if (sound.playing()) {
                    sound.pause();
                } else {
                    sound.play();
                }
            });
            
            document.getElementById('progress').addEventListener('input', function() {
                if (!sound || !sound.playing()) return;
                
                isSeeking = true;
                const durationVal = sound.duration();
                const seek = durationVal * (this.value / 100);
                sound.seek(seek);
                document.getElementById('current-time').textContent = formatTime(seek);
            });
            
            // Принудительное обновление прогресса после перемотки
            setTimeout(() => {
                isSeeking = false;
                updateProgress();
            }, 100);
            
            document.getElementById('volume').addEventListener('input', function() {
                if (!sound) return;
                sound.volume(this.value);
            });
        </script>
        <style>
            .playing {
                background: #383838 !important;
                box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
            }
            .player-bar {
                height: 80px;
                border-top: 1px solid #333;
            }
            input[type="range"] {
                -webkit-appearance: none;
                height: 4px;
                background: #535353;
                border-radius: 2px;
            }
            input[type="range"]::-webkit-slider-thumb {
                -webkit-appearance: none;
                width: 12px;
                height: 12px;
                background: #fff;
                border-radius: 50%;
                cursor: pointer;
            }
        </style>
    </body>
</html>
