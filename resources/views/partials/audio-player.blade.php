@php
    $driveUrl = $driveUrl ?? null;
@endphp
<div class="audio-player" data-src="{{ $src }}">
    <button type="button" class="audio-player-toggle" aria-label="Reproducir {{ $title }}">
        <i class="fa fa-play"></i>
    </button>
    <div class="audio-player-main">
        <input type="range" class="audio-player-seek" min="0" max="100" step="0.1" value="0" disabled
            aria-label="Progreso de {{ $title }}">
        <div class="audio-player-meta">
            <span class="audio-player-time">
                <span class="audio-player-current">0:00</span> / <span class="audio-player-duration">--:--</span>
            </span>
            <span class="audio-player-error">No se pudo cargar</span>
        </div>
    </div>
    <div class="audio-player-volume">
        <button type="button" class="audio-player-volume-btn" aria-label="Volumen">
            <i class="fa fa-volume-up"></i>
        </button>
        <div class="audio-player-volume-pop">
            <input type="range" class="audio-player-volume-range" min="0" max="1" step="0.01" value="1"
                aria-label="Nivel de volumen">
        </div>
    </div>
    @if ($driveUrl)
        <a href="{{ $driveUrl }}" target="_blank" rel="noopener" class="audio-player-fallback"
            title="Abrir en Google Drive">
            <i class="fa fa-external-link-alt"></i>
        </a>
    @endif
    <audio class="audio-player-el" preload="none"></audio>
</div>

@once
    <script>
        (function () {
            function onReady(fn) {
                if (document.readyState !== 'loading') fn();
                else document.addEventListener('DOMContentLoaded', fn);
            }

            function formatTime(sec) {
                if (!isFinite(sec) || sec < 0) return '0:00';
                var m = Math.floor(sec / 60);
                var s = Math.floor(sec % 60);
                return m + ':' + (s < 10 ? '0' : '') + s;
            }

            function pauseOthers(except) {
                document.querySelectorAll('.audio-player.is-playing').forEach(function (el) {
                    if (el === except) return;
                    var audio = el.querySelector('.audio-player-el');
                    if (audio) audio.pause();
                });
            }

            var VOLUME_KEY = 'sisipedia_audio_volume';

            function getStoredVolume() {
                var raw = parseFloat(localStorage.getItem(VOLUME_KEY));
                return isFinite(raw) && raw >= 0 && raw <= 1 ? raw : 1;
            }

            // Cierra cualquier popover de volumen abierto al hacer click fuera de él.
            document.addEventListener('click', function (e) {
                document.querySelectorAll('.audio-player-volume.is-open').forEach(function (vol) {
                    if (!vol.contains(e.target)) vol.classList.remove('is-open');
                });
            });

            function setupPlayer(wrap) {
                var audio = wrap.querySelector('.audio-player-el');
                var toggle = wrap.querySelector('.audio-player-toggle');
                var seek = wrap.querySelector('.audio-player-seek');
                var current = wrap.querySelector('.audio-player-current');
                var duration = wrap.querySelector('.audio-player-duration');
                var src = wrap.getAttribute('data-src');
                var seeking = false;

                var volumeWrap = wrap.querySelector('.audio-player-volume');
                var volumeBtn = wrap.querySelector('.audio-player-volume-btn');
                var volumeRange = wrap.querySelector('.audio-player-volume-range');

                function volumeIconClass() {
                    if (audio.muted || audio.volume === 0) return 'fa-volume-mute';
                    if (audio.volume < 0.5) return 'fa-volume-down';
                    return 'fa-volume-up';
                }

                function updateVolumeIcon() {
                    volumeBtn.innerHTML = '<i class="fa ' + volumeIconClass() + '"></i>';
                }

                audio.volume = getStoredVolume();
                volumeRange.value = audio.volume;
                updateVolumeIcon();

                volumeBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    document.querySelectorAll('.audio-player-volume.is-open').forEach(function (vol) {
                        if (vol !== volumeWrap) vol.classList.remove('is-open');
                    });
                    volumeWrap.classList.toggle('is-open');
                });
                volumeRange.addEventListener('input', function () {
                    var v = parseFloat(volumeRange.value);
                    audio.volume = v;
                    audio.muted = v === 0;
                    localStorage.setItem(VOLUME_KEY, v);
                    updateVolumeIcon();
                });

                function setIcon(playing) {
                    toggle.innerHTML = playing ? '<i class="fa fa-pause"></i>' : '<i class="fa fa-play"></i>';
                }

                function ensureSrc() {
                    if (!audio.getAttribute('src')) {
                        audio.src = src;
                    }
                }

                toggle.addEventListener('click', function () {
                    ensureSrc();
                    if (audio.paused) {
                        pauseOthers(wrap);
                        wrap.classList.remove('has-error');
                        wrap.classList.add('is-loading');
                        audio.play().catch(function () {
                            wrap.classList.remove('is-loading');
                            wrap.classList.add('has-error');
                        });
                    } else {
                        audio.pause();
                    }
                });

                audio.addEventListener('waiting', function () { wrap.classList.add('is-loading'); });
                audio.addEventListener('canplay', function () { wrap.classList.remove('is-loading'); });
                audio.addEventListener('playing', function () {
                    wrap.classList.remove('is-loading');
                    wrap.classList.add('is-playing');
                    setIcon(true);
                });
                audio.addEventListener('pause', function () {
                    wrap.classList.remove('is-playing');
                    setIcon(false);
                });
                audio.addEventListener('ended', function () {
                    wrap.classList.remove('is-playing');
                    setIcon(false);
                    seek.value = 0;
                    current.textContent = '0:00';
                });
                audio.addEventListener('error', function () {
                    if (!audio.getAttribute('src')) return;
                    wrap.classList.remove('is-loading', 'is-playing');
                    wrap.classList.add('has-error');
                    setIcon(false);
                });
                audio.addEventListener('loadedmetadata', function () {
                    if (isFinite(audio.duration)) {
                        seek.max = audio.duration;
                        seek.disabled = false;
                        duration.textContent = formatTime(audio.duration);
                    }
                });
                audio.addEventListener('timeupdate', function () {
                    if (seeking) return;
                    seek.value = audio.currentTime;
                    current.textContent = formatTime(audio.currentTime);
                });
                seek.addEventListener('input', function () {
                    seeking = true;
                    current.textContent = formatTime(parseFloat(seek.value));
                });
                seek.addEventListener('change', function () {
                    ensureSrc();
                    audio.currentTime = parseFloat(seek.value);
                    seeking = false;
                });
            }

            onReady(function () {
                document.querySelectorAll('.audio-player').forEach(function (wrap) {
                    if (wrap.dataset.audioReady) return;
                    wrap.dataset.audioReady = '1';
                    setupPlayer(wrap);
                });
            });
        })();
    </script>
@endonce
