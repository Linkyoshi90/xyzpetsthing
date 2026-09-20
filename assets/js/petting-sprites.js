(function (root, factory) {
    'use strict';

    var api = factory();
    if (typeof module === 'object' && module.exports) {
        module.exports = api;
    } else {
        root.HarmontidePettingSprites = api;
    }
}(typeof globalThis !== 'undefined' ? globalThis : this, function () {
    'use strict';

    var sheetCache = Object.create(null);

    function positiveNumber(value, fallback) {
        var number = Number(value);
        return Number.isFinite(number) && number > 0 ? number : fallback;
    }

    function positiveInteger(value, fallback) {
        return Math.max(1, Math.floor(positiveNumber(value, fallback)));
    }

    /**
     * Return one source rectangle from a regular sprite grid. This is DOM-free so
     * frame selection remains directly testable in Node.
     */
    function getFrameGeometry(frameIndex, sheetWidth, sheetHeight, columns, rows) {
        var width = Number(sheetWidth);
        var height = Number(sheetHeight);
        var columnCount = Number(columns);
        var rowCount = Number(rows);
        var index = Number(frameIndex);

        if (!Number.isFinite(width) || width <= 0
            || !Number.isFinite(height) || height <= 0
            || !Number.isInteger(columnCount) || columnCount <= 0
            || !Number.isInteger(rowCount) || rowCount <= 0) {
            throw new RangeError('Sprite dimensions, columns, and rows must be positive.');
        }
        if (!Number.isInteger(index) || index < 0 || index >= columnCount * rowCount) {
            throw new RangeError('Frame index is outside the sprite grid.');
        }

        var frameWidth = width / columnCount;
        var frameHeight = height / rowCount;
        var column = index % columnCount;
        var row = Math.floor(index / columnCount);
        return {
            index: index,
            column: column,
            row: row,
            sx: column * frameWidth,
            sy: row * frameHeight,
            sw: frameWidth,
            sh: frameHeight,
        };
    }

    function normalizeAnimationConfig(config) {
        if (!config || typeof config.src !== 'string' || !config.src.trim()) {
            return null;
        }

        var columns = positiveInteger(config.columns, 5);
        var rows = positiveInteger(config.rows, 4);
        var capacity = columns * rows;
        return {
            src: config.src,
            columns: columns,
            rows: rows,
            frameCount: Math.min(positiveInteger(config.frameCount, capacity), capacity),
            fps: positiveNumber(config.fps, 20),
        };
    }

    function normalizeManifest(manifest) {
        var result = Object.create(null);
        if (!manifest || typeof manifest !== 'object') {
            return result;
        }
        Object.keys(manifest).forEach(function (name) {
            var normalized = normalizeAnimationConfig(manifest[name]);
            if (normalized) {
                result[name] = normalized;
            }
        });
        return result;
    }

    function resolveImageConstructor(player) {
        var view = player.document && player.document.defaultView;
        if (view && typeof view.Image === 'function') {
            return view.Image;
        }
        if (typeof Image === 'function') {
            return Image;
        }
        return null;
    }

    function loadSheet(src, ImageConstructor) {
        if (sheetCache[src]) {
            return sheetCache[src].promise;
        }

        var entry = {
            state: 'loading',
            image: null,
            promise: null,
        };
        entry.promise = new Promise(function (resolve) {
            function fail() {
                entry.state = 'error';
                entry.image = null;
                resolve(null);
            }

            if (!ImageConstructor) {
                fail();
                return;
            }

            try {
                var image = new ImageConstructor();
                image.onload = function () {
                    if (!image.naturalWidth || !image.naturalHeight) {
                        fail();
                        return;
                    }
                    entry.state = 'ready';
                    entry.image = image;
                    resolve(image);
                };
                image.onerror = fail;
                image.src = src;
            } catch (error) {
                fail();
            }
        });
        sheetCache[src] = entry;
        entry.promise.then(function (image) {
            // Decode/network failures may be transient. Do not permanently poison
            // this URL; a later preload is allowed to make a fresh attempt.
            if (!image && sheetCache[src] === entry) {
                delete sheetCache[src];
            }
        });
        return entry.promise;
    }

    function cachedSheet(src) {
        var entry = sheetCache[src];
        return entry && entry.state === 'ready' ? entry.image : null;
    }

    function safeCall(callback, value) {
        if (typeof callback !== 'function') {
            return;
        }
        try {
            callback(value);
        } catch (error) {
            setTimeout(function () {
                throw error;
            }, 0);
        }
    }

    function SpritePlayer(options) {
        options = options || {};
        if (!options.image || !options.image.ownerDocument) {
            throw new TypeError('SpritePlayer requires the static creature image element.');
        }

        this.image = options.image;
        this.document = this.image.ownerDocument;
        this.container = options.container || this.image.parentNode;
        this.manifest = normalizeManifest(options.manifest);
        this.ImageConstructor = options.ImageConstructor || resolveImageConstructor(this);
        this.requestFrame = options.requestAnimationFrame
            || (this.document.defaultView
                && this.document.defaultView.requestAnimationFrame.bind(this.document.defaultView));
        this.cancelFrame = options.cancelAnimationFrame
            || (this.document.defaultView
                && this.document.defaultView.cancelAnimationFrame.bind(this.document.defaultView));
        this.requestFrame = this.requestFrame || function (callback) {
            return setTimeout(function () { callback(Date.now()); }, 16);
        };
        this.cancelFrame = this.cancelFrame || clearTimeout;
        this.destroyed = false;
        this.current = null;
        this.frameRequest = null;
        this.originalImageOpacity = this.image.style.opacity;
        this.originalContainerPosition = this.container ? this.container.style.position : '';
        this.changedContainerPosition = false;

        this.canvas = options.canvas || this.document.createElement('canvas');
        this.ownsCanvas = !options.canvas;
        this.canvas.classList.add('pet-sprite-canvas');
        this.canvas.setAttribute('aria-hidden', 'true');
        this.canvas.style.display = 'none';
        this.canvas.style.position = 'absolute';
        this.canvas.style.inset = 'auto';
        this.canvas.style.left = '50%';
        this.canvas.style.top = '50%';
        this.canvas.style.width = '100%';
        this.canvas.style.height = 'auto';
        this.canvas.style.maxWidth = '100%';
        this.canvas.style.maxHeight = '100%';
        this.canvas.style.transform = 'translate(-50%, -50%)';
        this.canvas.style.objectFit = 'contain';
        this.canvas.style.pointerEvents = 'none';
        this.canvas.style.imageRendering = 'pixelated';
        this.canvas.style.zIndex = '1';

        if (this.container) {
            var view = this.document.defaultView;
            if (view && view.getComputedStyle(this.container).position === 'static') {
                this.container.style.position = 'relative';
                this.changedContainerPosition = true;
            }
            var dirtLayer = this.container.querySelector('.pet-dirt-layer');
            if (dirtLayer) {
                dirtLayer.style.zIndex = '2';
                this.container.insertBefore(this.canvas, dirtLayer);
            } else {
                this.container.appendChild(this.canvas);
            }
        }
    }

    SpritePlayer.prototype.has = function (name) {
        return Boolean(this.manifest[name]);
    };

    SpritePlayer.prototype.isReady = function (name) {
        var config = this.manifest[name];
        return Boolean(config && cachedSheet(config.src));
    };

    SpritePlayer.prototype.isPlaying = function (name) {
        return Boolean(this.current && (!name || this.current.name === name));
    };

    SpritePlayer.prototype.preload = function (name) {
        var config = this.manifest[name];
        if (!config || this.destroyed) {
            return Promise.resolve(false);
        }
        return loadSheet(config.src, this.ImageConstructor).then(function (image) {
            return Boolean(image);
        });
    };

    SpritePlayer.prototype.preloadAll = function () {
        var player = this;
        return Promise.all(Object.keys(this.manifest).map(function (name) {
            return player.preload(name);
        }));
    };

    SpritePlayer.prototype._restoreStaticImage = function () {
        this.image.style.opacity = this.originalImageOpacity;
        this.canvas.style.display = 'none';
        this.canvas.setAttribute('aria-hidden', 'true');
        var context = this.canvas.getContext('2d');
        if (context) {
            context.clearRect(0, 0, this.canvas.width, this.canvas.height);
        }
    };

    SpritePlayer.prototype._fitCanvas = function (frameWidth, frameHeight) {
        var bounds = this.container && typeof this.container.getBoundingClientRect === 'function'
            ? this.container.getBoundingClientRect()
            : null;
        var containerWidth = bounds ? Number(bounds.width) : 0;
        var containerHeight = bounds ? Number(bounds.height) : 0;

        this.canvas.style.aspectRatio = frameWidth + ' / ' + frameHeight;
        if (!containerWidth || !containerHeight
            || (frameWidth / frameHeight) >= (containerWidth / containerHeight)) {
            this.canvas.style.width = '100%';
            this.canvas.style.height = 'auto';
        } else {
            this.canvas.style.width = 'auto';
            this.canvas.style.height = '100%';
        }
    };

    SpritePlayer.prototype._draw = function (sheet, config, frameIndex) {
        var geometry = getFrameGeometry(
            frameIndex,
            sheet.naturalWidth,
            sheet.naturalHeight,
            config.columns,
            config.rows
        );
        var context = this.canvas.getContext('2d');
        if (!context) {
            throw new Error('Canvas 2D rendering is unavailable.');
        }

        var targetWidth = Math.max(1, Math.round(geometry.sw));
        var targetHeight = Math.max(1, Math.round(geometry.sh));
        if (this.canvas.width !== targetWidth || this.canvas.height !== targetHeight) {
            this.canvas.width = targetWidth;
            this.canvas.height = targetHeight;
        }
        this._fitCanvas(targetWidth, targetHeight);
        context.imageSmoothingEnabled = false;
        context.clearRect(0, 0, targetWidth, targetHeight);
        context.drawImage(
            sheet,
            geometry.sx,
            geometry.sy,
            geometry.sw,
            geometry.sh,
            0,
            0,
            targetWidth,
            targetHeight
        );
    };

    SpritePlayer.prototype.play = function (name, options) {
        options = options || {};
        if (this.destroyed) {
            return false;
        }
        if (!this.manifest[name]) {
            this.cancel('unavailable');
            return false;
        }
        if (this.current && this.current.name === name && options.restart === false) {
            return true;
        }

        var config = this.manifest[name];
        var sheet = cachedSheet(config.src);
        if (!sheet) {
            // Restore the static fallback before this new interaction starts an
            // asynchronous preload; never leave a superseded sheet visible.
            this.cancel('unavailable');
            void this.preload(name);
            return false;
        }

        this.cancel('replaced');
        var player = this;
        var playback = {
            name: name,
            config: config,
            sheet: sheet,
            options: options,
            startedAt: null,
            lastFrame: -1,
        };
        this.current = playback;

        try {
            this._draw(sheet, config, 0);
        } catch (error) {
            this.current = null;
            this._restoreStaticImage();
            safeCall(options.onError, error);
            return false;
        }

        this.canvas.style.display = 'block';
        this.image.style.opacity = '0';
        safeCall(options.onStart, { name: name });

        function finish() {
            if (player.current !== playback) {
                return;
            }
            player.current = null;
            player.frameRequest = null;
            player._restoreStaticImage();
            safeCall(options.onFinish, { name: name });
        }

        function tick(timestamp) {
            if (player.current !== playback) {
                return;
            }
            if (playback.startedAt === null) {
                playback.startedAt = timestamp;
            }

            var frameDuration = 1000 / config.fps;
            var elapsed = Math.max(0, timestamp - playback.startedAt);
            var elapsedFrames = Math.floor(elapsed / frameDuration);
            var frameIndex = options.loop
                ? elapsedFrames % config.frameCount
                : Math.min(config.frameCount - 1, elapsedFrames);
            try {
                if (frameIndex !== playback.lastFrame) {
                    player._draw(sheet, config, frameIndex);
                    playback.lastFrame = frameIndex;
                }
            } catch (error) {
                player.current = null;
                player.frameRequest = null;
                player._restoreStaticImage();
                safeCall(options.onError, error);
                return;
            }

            if (!options.loop && elapsed >= config.frameCount * frameDuration) {
                finish();
                return;
            }
            player.frameRequest = player.requestFrame(tick);
        }

        if (this.current === playback) {
            this.frameRequest = this.requestFrame(tick);
        }
        return true;
    };

    SpritePlayer.prototype.cancel = function (reason) {
        if (!this.current) {
            this._restoreStaticImage();
            return false;
        }

        var playback = this.current;
        this.current = null;
        if (this.frameRequest !== null) {
            this.cancelFrame(this.frameRequest);
            this.frameRequest = null;
        }
        this._restoreStaticImage();
        safeCall(playback.options.onCancel, {
            name: playback.name,
            reason: reason || 'cancelled',
        });
        return true;
    };

    SpritePlayer.prototype.destroy = function () {
        if (this.destroyed) {
            return;
        }
        this.cancel('destroyed');
        this.destroyed = true;
        if (this.ownsCanvas && this.canvas.parentNode) {
            this.canvas.parentNode.removeChild(this.canvas);
        }
        if (this.changedContainerPosition && this.container) {
            this.container.style.position = this.originalContainerPosition;
        }
    };

    return {
        SpritePlayer: SpritePlayer,
        getFrameGeometry: getFrameGeometry,
        normalizeAnimationConfig: normalizeAnimationConfig,
        normalizeManifest: normalizeManifest,
    };
}));
