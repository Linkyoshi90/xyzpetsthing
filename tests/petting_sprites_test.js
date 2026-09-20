'use strict';

const assert = require('assert');
const fs = require('fs');
const path = require('path');
const sprites = require('../assets/js/petting-sprites.js');

const pettingPage = fs.readFileSync(path.join(__dirname, '..', 'pages', 'petting.php'), 'utf8');
assert.match(pettingPage, /\$speciesSlug\s*=\s*pet_asset_slug/);
assert.match(pettingPage, /images\/games\/petting\/creatures\/['"]\s*\.\s*\$speciesSlug/);
assert.doesNotMatch(pettingPage, /\$imagePath\s*===\s*['"]images\/creatures\/azureus_f_blue\.webp/);

const first = sprites.getFrameGeometry(0, 500, 400, 5, 4);
assert.deepStrictEqual(first, {
    index: 0,
    column: 0,
    row: 0,
    sx: 0,
    sy: 0,
    sw: 100,
    sh: 100,
});

const middle = sprites.getFrameGeometry(7, 1000, 800, 5, 4);
assert.deepStrictEqual(middle, {
    index: 7,
    column: 2,
    row: 1,
    sx: 400,
    sy: 200,
    sw: 200,
    sh: 200,
});

const last = sprites.getFrameGeometry(19, 1536, 1024, 5, 4);
assert.strictEqual(last.column, 4);
assert.strictEqual(last.row, 3);
assert.strictEqual(last.sx, (1536 / 5) * 4);
assert.strictEqual(last.sy, 768);
assert.strictEqual(last.sw, 1536 / 5);
assert.strictEqual(last.sh, 256);

assert.throws(
    () => sprites.getFrameGeometry(20, 500, 400, 5, 4),
    /outside the sprite grid/
);
assert.throws(
    () => sprites.getFrameGeometry(0, 500, 400, 0, 4),
    /must be positive/
);

assert.deepStrictEqual(sprites.normalizeAnimationConfig({
    src: 'sheet.png',
    columns: 5,
    rows: 4,
    frameCount: 50,
    fps: 12,
}), {
    src: 'sheet.png',
    columns: 5,
    rows: 4,
    frameCount: 20,
    fps: 12,
});
assert.strictEqual(sprites.normalizeAnimationConfig({ src: '' }), null);

function FakeImage() {
    this.naturalWidth = 0;
    this.naturalHeight = 0;
    this.onload = null;
    this.onerror = null;
}

FakeImage.responses = Object.create(null);
FakeImage.attempts = Object.create(null);
FakeImage.plan = function (src, responses) {
    FakeImage.responses[src] = responses.slice();
    FakeImage.attempts[src] = 0;
};

Object.defineProperty(FakeImage.prototype, 'src', {
    set: function (src) {
        var fake = this;
        FakeImage.attempts[src] = (FakeImage.attempts[src] || 0) + 1;
        var response = (FakeImage.responses[src] || []).shift() || { ok: false };
        Promise.resolve().then(function () {
            if (!response.ok) {
                if (fake.onerror) {
                    fake.onerror(new Error('planned image failure'));
                }
                return;
            }
            fake.naturalWidth = response.width;
            fake.naturalHeight = response.height;
            if (fake.onload) {
                fake.onload();
            }
        });
    },
});

function createScheduler() {
    var nextId = 1;
    var callbacks = new Map();
    return {
        request: function (callback) {
            var id = nextId;
            nextId += 1;
            callbacks.set(id, callback);
            return id;
        },
        cancel: function (id) {
            callbacks.delete(id);
        },
        step: function (timestamp) {
            var pending = Array.from(callbacks.values());
            callbacks.clear();
            pending.forEach(function (callback) {
                callback(timestamp);
            });
        },
    };
}

function createHarness(manifest, bounds) {
    var scheduler = createScheduler();
    var context = {
        clearRectCalls: 0,
        drawImageCalls: 0,
        imageSmoothingEnabled: true,
        clearRect: function () {
            this.clearRectCalls += 1;
        },
        drawImage: function () {
            this.drawImageCalls += 1;
            this.lastDrawArguments = Array.from(arguments);
        },
    };
    var canvas = {
        width: 0,
        height: 0,
        parentNode: null,
        style: {},
        attributes: {},
        classList: { add: function () {} },
        setAttribute: function (name, value) {
            this.attributes[name] = value;
        },
        getContext: function (kind) {
            return kind === '2d' ? context : null;
        },
    };
    var dirtLayer = { style: {} };
    var container = {
        style: { position: '' },
        children: [],
        querySelector: function (selector) {
            return selector === '.pet-dirt-layer' ? dirtLayer : null;
        },
        insertBefore: function (child) {
            child.parentNode = this;
            this.children.push(child);
        },
        appendChild: function (child) {
            child.parentNode = this;
            this.children.push(child);
        },
        removeChild: function (child) {
            this.children = this.children.filter(function (candidate) {
                return candidate !== child;
            });
            child.parentNode = null;
        },
        getBoundingClientRect: function () {
            return bounds || { width: 300, height: 150 };
        },
    };
    var document = {
        defaultView: {
            getComputedStyle: function () {
                return { position: 'relative' };
            },
        },
        createElement: function (tagName) {
            assert.strictEqual(tagName, 'canvas');
            return canvas;
        },
    };
    var image = {
        ownerDocument: document,
        parentNode: container,
        style: { opacity: '0.4' },
    };
    container.children.push(image, dirtLayer);

    var player = new sprites.SpritePlayer({
        image: image,
        container: container,
        manifest: manifest,
        ImageConstructor: FakeImage,
        requestAnimationFrame: scheduler.request,
        cancelAnimationFrame: scheduler.cancel,
    });
    return {
        player: player,
        scheduler: scheduler,
        image: image,
        canvas: canvas,
        context: context,
    };
}

(async function runPlayerTests() {
    FakeImage.plan('retry.png', [
        { ok: false },
        { ok: true, width: 400, height: 400 },
    ]);
    var retryHarness = createHarness({
        retry: { src: 'retry.png', columns: 2, rows: 2, frameCount: 4, fps: 10 },
    });
    assert.strictEqual(await retryHarness.player.preload('retry'), false);
    assert.strictEqual(await retryHarness.player.preload('retry'), true);
    assert.strictEqual(FakeImage.attempts['retry.png'], 2);
    retryHarness.player.destroy();

    FakeImage.plan('finish.png', [
        { ok: true, width: 400, height: 400 },
    ]);
    var finishHarness = createHarness({
        eating: { src: 'finish.png', columns: 2, rows: 2, frameCount: 2, fps: 10 },
    });
    assert.strictEqual(await finishHarness.player.preload('eating'), true);
    var starts = 0;
    var finishes = 0;
    assert.strictEqual(finishHarness.player.play('eating', {
        onStart: function () { starts += 1; },
        onFinish: function () { finishes += 1; },
    }), true);
    assert.strictEqual(starts, 1);
    assert.strictEqual(finishHarness.image.style.opacity, '0');
    assert.strictEqual(finishHarness.canvas.style.display, 'block');
    assert.strictEqual(finishHarness.context.imageSmoothingEnabled, false);
    assert.strictEqual(finishHarness.canvas.style.width, 'auto');
    assert.strictEqual(finishHarness.canvas.style.height, '100%');
    finishHarness.scheduler.step(0);
    finishHarness.scheduler.step(201);
    assert.strictEqual(finishes, 1);
    assert.strictEqual(finishHarness.image.style.opacity, '0.4');
    assert.strictEqual(finishHarness.canvas.style.display, 'none');
    finishHarness.player.destroy();

    FakeImage.plan('first-replacement.png', [
        { ok: true, width: 800, height: 400 },
    ]);
    FakeImage.plan('second-replacement.png', [
        { ok: true, width: 800, height: 400 },
    ]);
    FakeImage.plan('pending-replacement.png', [
        { ok: false },
        { ok: true, width: 800, height: 400 },
    ]);
    var replacementHarness = createHarness({
        first: { src: 'first-replacement.png', columns: 1, rows: 1, frameCount: 1, fps: 10 },
        second: { src: 'second-replacement.png', columns: 1, rows: 1, frameCount: 1, fps: 10 },
        pending: { src: 'pending-replacement.png', columns: 1, rows: 1, frameCount: 1, fps: 10 },
    });
    await Promise.all([
        replacementHarness.player.preload('first'),
        replacementHarness.player.preload('second'),
    ]);

    var firstCancelReason = null;
    assert.strictEqual(replacementHarness.player.play('first', {
        onCancel: function (event) { firstCancelReason = event.reason; },
    }), true);
    var secondCancelReason = null;
    assert.strictEqual(replacementHarness.player.play('second', {
        onCancel: function (event) { secondCancelReason = event.reason; },
    }), true);
    assert.strictEqual(firstCancelReason, 'replaced');
    assert.strictEqual(replacementHarness.canvas.style.width, '100%');
    assert.strictEqual(replacementHarness.canvas.style.height, 'auto');
    replacementHarness.player.cancel('manual');
    assert.strictEqual(secondCancelReason, 'manual');
    assert.strictEqual(replacementHarness.image.style.opacity, '0.4');

    var unavailableCancelReason = null;
    assert.strictEqual(replacementHarness.player.play('first', {
        onCancel: function (event) { unavailableCancelReason = event.reason; },
    }), true);
    assert.strictEqual(replacementHarness.player.play('pending'), false);
    assert.strictEqual(unavailableCancelReason, 'unavailable');
    assert.strictEqual(replacementHarness.image.style.opacity, '0.4');
    assert.strictEqual(replacementHarness.canvas.style.display, 'none');

    // Let the planned failed request and its cache cleanup settle, then verify a
    // subsequent preload creates a fresh Image rather than reusing the failure.
    await Promise.resolve();
    await Promise.resolve();
    assert.strictEqual(await replacementHarness.player.preload('pending'), true);
    assert.strictEqual(FakeImage.attempts['pending-replacement.png'], 2);
    replacementHarness.player.destroy();

    FakeImage.plan('loop.png', [
        { ok: true, width: 200, height: 100 },
    ]);
    var loopHarness = createHarness({
        idle: { src: 'loop.png', columns: 2, rows: 1, frameCount: 2, fps: 10 },
    }, { width: 200, height: 100 });
    assert.strictEqual(await loopHarness.player.preload('idle'), true);
    var loopStarts = 0;
    var loopFinishes = 0;
    var loopCancels = 0;
    assert.strictEqual(loopHarness.player.play('idle', {
        loop: true,
        restart: false,
        onStart: function () { loopStarts += 1; },
        onFinish: function () { loopFinishes += 1; },
        onCancel: function () { loopCancels += 1; },
    }), true);
    assert.strictEqual(loopHarness.player.play('idle', {
        loop: true,
        restart: false,
        onStart: function () { loopStarts += 10; },
    }), true);
    assert.strictEqual(loopStarts, 1);
    loopHarness.scheduler.step(0);
    loopHarness.scheduler.step(100);
    assert.strictEqual(loopHarness.context.lastDrawArguments[1], 100);
    loopHarness.scheduler.step(200);
    assert.strictEqual(loopHarness.context.lastDrawArguments[1], 0);
    assert.strictEqual(loopFinishes, 0);
    loopHarness.player.cancel('loop-test');
    assert.strictEqual(loopCancels, 1);
    assert.strictEqual(loopFinishes, 0);
    assert.strictEqual(loopHarness.image.style.opacity, '0.4');
    assert.strictEqual(loopHarness.canvas.style.display, 'none');
    loopHarness.player.destroy();

    console.log('petting sprite tests passed');
}()).catch(function (error) {
    console.error(error);
    process.exitCode = 1;
});
