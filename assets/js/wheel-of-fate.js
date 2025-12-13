(function () {
    function normalizeAngle(angle) {
        var twoPi = Math.PI * 2;
        return ((angle % twoPi) + twoPi) % twoPi;
    }

    function easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }

    function sanitizeNumber(value) {
        var num = Number(value);
        if (!Number.isFinite(num) || num <= 0) {
            return 0;
        }
        return Math.floor(num);
    }

    function formatDuration(seconds) {
        var total = Math.max(0, Math.floor(Number(seconds) || 0));
        var hours = Math.floor(total / 3600);
        var minutes = Math.floor((total % 3600) / 60);
        var secs = total % 60;
        var pad = function (num) {
            return String(num).padStart(2, '0');
        };
        if (hours > 0) {
            return pad(hours) + ':' + pad(minutes) + ':' + pad(secs);
        }
        return pad(minutes) + ':' + pad(secs);
    }
    function getCurrencyLongName() {
        return (window.appCurrency && window.appCurrency.longName) || 'Cash-Dosh';
    }

    function getTextColor(hex) {
        if (typeof hex !== 'string') {
            return '#0f172a';
        }
        var value = hex.replace('#', '');
        if (!/^[0-9a-fA-F]{6}$/.test(value)) {
            return '#0f172a';
        }
        var r = parseInt(value.slice(0, 2), 16);
        var g = parseInt(value.slice(2, 4), 16);
        var b = parseInt(value.slice(4, 6), 16);
        var luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        return luminance > 0.6 ? '#0f172a' : '#f8fafc';
    }

    function drawWrappedText(ctx, text, maxWidth) {
        var words = String(text || '').split(/\s+/);
        var lines = [];
        var currentLine = '';
        for (var i = 0; i < words.length; i++) {
            var word = words[i];
            var testLine = currentLine ? currentLine + ' ' + word : word;
            if (ctx.measureText(testLine).width > maxWidth && currentLine) {
                lines.push(currentLine);
                currentLine = word;
            } else {
                currentLine = testLine;
            }
        }
        if (currentLine) {
            lines.push(currentLine);
        }
        var lineHeight = 16;
        var totalHeight = lineHeight * lines.length;
        for (var j = 0; j < lines.length; j++) {
            ctx.fillText(lines[j], 0, j * lineHeight - (totalHeight - lineHeight) / 2);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var state = (typeof window.WHEEL_OF_FATE_STATE === 'object' && window.WHEEL_OF_FATE_STATE) ? window.WHEEL_OF_FATE_STATE : {};
        var spinEndpoint = (typeof window.WHEEL_OF_FATE_ENDPOINT === 'string' && window.WHEEL_OF_FATE_ENDPOINT)
            ? window.WHEEL_OF_FATE_ENDPOINT
            : 'index.php?pg=wheel-of-fate';
        var segments = Array.isArray(window.WHEEL_OF_FATE_SEGMENTS) ? window.WHEEL_OF_FATE_SEGMENTS : [];
        var canvas = document.getElementById('wheel-canvas');
        var spinButton = document.getElementById('spin-button');
        var timerElement = document.getElementById('spin-timer');
        var cooldownElement = document.getElementById('spin-cooldown');
        var resultElement = document.getElementById('spin-result');

        if (!canvas || !spinButton || !timerElement || !resultElement || !segments.length) {
            return;
        }

        var ctx = canvas.getContext('2d');
        var totalSegments = segments.length;
        var arc = (Math.PI * 2) / totalSegments;
        var pointerAngle = -Math.PI / 2;
        var centerX = canvas.width / 2;
        var centerY = canvas.height / 2;
        var radius = Math.min(centerX, centerY) - 8;
        var currentRotation = 0;
        var spinning = false;
        var spinCountdownInterval = null;
        var cooldownInterval = null;
        var colors = ['#f94144', '#f3722c', '#f8961e', '#f9844a', '#f9c74f', '#90be6d', '#43aa8b', '#577590', '#277da1', '#4d908e', '#bc4749', '#ef476f'];
        var defaultCooldownSeconds = sanitizeNumber(state.cooldownSeconds);
        var cooldownRemaining = sanitizeNumber(state.cooldownRemaining);
        var lastPointerSegment = 0;
        var audioCtx = null;
        var audioDisabled = false;

        function ensureAudioContext() {
            if (audioDisabled) {
                return null;
            }
            var AudioContextConstructor = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextConstructor) {
                audioDisabled = true;
                return null;
            }
            if (!audioCtx) {
                try {
                    audioCtx = new AudioContextConstructor();
                } catch (error) {
                    audioDisabled = true;
                    return null;
                }
            }
            if (audioCtx.state === 'suspended') {
                audioCtx.resume().catch(function () { /* ignored */ });
            }
            return audioCtx;
        }

        function playClickSound() {
            if (audioDisabled) {
                return;
            }
            var context = ensureAudioContext();
            if (!context) {
                return;
            }
            try {
                var oscillator = context.createOscillator();
                var gainNode = context.createGain();
                oscillator.type = 'square';
                oscillator.frequency.setValueAtTime(750, context.currentTime);
                gainNode.gain.setValueAtTime(0.0001, context.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.08, context.currentTime + 0.001);
                gainNode.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + 0.08);
                oscillator.connect(gainNode);
                gainNode.connect(context.destination);
                oscillator.start();
                oscillator.stop(context.currentTime + 0.09);
            } catch (error) {
                audioDisabled = true;
            }
        }

        function getPointerSegmentIndex(rotation) {
            var normalized = normalizeAngle(rotation);
            var rawIndex = Math.floor(((normalized + arc / 2) / arc)) % totalSegments;
            return (totalSegments - rawIndex) % totalSegments;
        }

        function updateSpinButtonState() {
            if (!spinButton) {
                return;
            }
            spinButton.disabled = spinning || cooldownRemaining > 0;
        }

        function drawWheel(rotation) {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            var baseStart = pointerAngle - arc / 2 + rotation;
            for (var i = 0; i < totalSegments; i++) {
                var startAngle = baseStart + i * arc;
                var endAngle = startAngle + arc;
                var fillColor = colors[i % colors.length];

                ctx.beginPath();
                ctx.moveTo(centerX, centerY);
                ctx.arc(centerX, centerY, radius, startAngle, endAngle);
                ctx.closePath();
                ctx.fillStyle = fillColor;
                ctx.fill();
                ctx.lineWidth = 2;
                ctx.strokeStyle = 'rgba(255,255,255,0.65)';
                ctx.stroke();

                var textAngle = startAngle + arc / 2;
                var textX = centerX + Math.cos(textAngle) * radius * 0.62;
                var textY = centerY + Math.sin(textAngle) * radius * 0.62;

                ctx.save();
                ctx.translate(textX, textY);
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.font = '16px "Inter", "Segoe UI", sans-serif';
                ctx.fillStyle = getTextColor(fillColor);
                drawWrappedText(ctx, segments[i].label, radius * 0.6);
                ctx.restore();
            }

            ctx.beginPath();
            ctx.arc(centerX, centerY, radius * 0.18, 0, Math.PI * 2);
            ctx.fillStyle = 'rgba(255,255,255,0.85)';
            ctx.fill();
            ctx.lineWidth = 2;
            ctx.strokeStyle = 'rgba(15,23,42,0.12)';
            ctx.stroke();
        }

        function resetSpinTimerDisplay() {
            if (timerElement) {
                timerElement.textContent = '--';
            }
        }

        function clearSpinCountdown() {
            if (spinCountdownInterval) {
                clearInterval(spinCountdownInterval);
                spinCountdownInterval = null;
            }
        }

        function startSpinCountdown(seconds) {
            if (!timerElement) {
                return;
            }
            var duration = sanitizeNumber(seconds);
            clearSpinCountdown();
            if (duration <= 0) {
                timerElement.textContent = '0';
                return;
            }
            var remaining = duration;
            timerElement.textContent = String(remaining);
            spinCountdownInterval = setInterval(function () {
                remaining -= 1;
                if (remaining <= 0) {
                    clearSpinCountdown();
                    timerElement.textContent = '0';
                } else {
                    timerElement.textContent = String(remaining);
                }
            }, 1000);
        }

        function setCooldown(seconds) {
            var sanitized = sanitizeNumber(seconds);
            cooldownRemaining = sanitized;
            if (cooldownInterval) {
                clearInterval(cooldownInterval);
                cooldownInterval = null;
            }
            if (cooldownElement) {
                if (sanitized > 0) {
                    cooldownElement.textContent = formatDuration(sanitized);
                } else {
                    cooldownElement.textContent = 'Ready';
                }
            }
            updateSpinButtonState();
            if (sanitized > 0) {
                cooldownInterval = setInterval(function () {
                    cooldownRemaining = Math.max(0, cooldownRemaining - 1);
                    if (cooldownRemaining <= 0) {
                        clearInterval(cooldownInterval);
                        cooldownInterval = null;
                        if (cooldownElement) {
                            cooldownElement.textContent = 'Ready';
                        }
                        updateSpinButtonState();
                    } else if (cooldownElement) {
                        cooldownElement.textContent = formatDuration(cooldownRemaining);
                    }
                }, 1000);
            }
        }

        function finishSpin(reward, balances, cooldownSeconds) {
            clearSpinCountdown();
            if (timerElement) {
                timerElement.textContent = '0';
            }
            if (resultElement) {
                resultElement.classList.remove('error');
                resultElement.classList.add('success');
                if (reward && reward.type === 'currency') {
                    resultElement.textContent = 'You won ' + reward.amount + ' ' + getCurrencyLongName() + '!';
                } else if (reward && reward.type === 'item') {
                    resultElement.textContent = 'You won ' + reward.label + '!';
                } else {
                    resultElement.textContent = 'Spin complete!';
                }
            }
            if (balances && typeof window.updateCurrencyDisplay === 'function') {
                window.updateCurrencyDisplay(balances);
            }
            spinning = false;
            var finalCooldown = sanitizeNumber(typeof cooldownSeconds === 'undefined' ? defaultCooldownSeconds : cooldownSeconds);
            if (!finalCooldown && !cooldownSeconds && defaultCooldownSeconds) {
                finalCooldown = defaultCooldownSeconds;
            }
            setCooldown(finalCooldown);
        }

        function handleError(message, cooldownSeconds, balances) {
            clearSpinCountdown();
            resetSpinTimerDisplay();
            if (resultElement) {
                resultElement.classList.remove('success');
                resultElement.classList.add('error');
                resultElement.textContent = message;
            }
            if (balances && typeof window.updateCurrencyDisplay === 'function') {
                window.updateCurrencyDisplay(balances);
            }
            spinning = false;
            if (cooldownSeconds !== null && typeof cooldownSeconds !== 'undefined') {
                setCooldown(cooldownSeconds);
            } else {
                updateSpinButtonState();
            }
        }

        function startSpinAnimation(targetIndex, reward, balances, cooldownSeconds) {
            var startRotation = currentRotation;
            var desired = normalizeAngle(-targetIndex * arc);
            var current = normalizeAngle(startRotation);
            var delta = desired - current;
            var twoPi = Math.PI * 2;
            if (delta <= 0) {
                delta += twoPi;
            }
            var extraTurns = 4; // ensures multiple full spins
            var spinAmount = delta + extraTurns * twoPi;
            var targetRotation = startRotation + spinAmount;
            var duration = 10000;
            var startTime = performance.now();
            lastPointerSegment = getPointerSegmentIndex(startRotation);

            function step(now) {
                var elapsed = now - startTime;
                var progress = Math.min(elapsed / duration, 1);
                var eased = easeOutCubic(progress);
                currentRotation = startRotation + spinAmount * eased;
                drawWheel(currentRotation);
                var pointerSegment = getPointerSegmentIndex(currentRotation);
                if (pointerSegment !== lastPointerSegment) {
                    playClickSound();
                    lastPointerSegment = pointerSegment;
                }
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    currentRotation = normalizeAngle(targetRotation);
                    drawWheel(currentRotation);
                    lastPointerSegment = getPointerSegmentIndex(currentRotation);
                    finishSpin(reward, balances, cooldownSeconds);
                }
            }

            requestAnimationFrame(step);
        }

        drawWheel(currentRotation);
        lastPointerSegment = getPointerSegmentIndex(currentRotation);
        resetSpinTimerDisplay();
        setCooldown(cooldownRemaining);

        spinButton.addEventListener('click', function () {
            if (spinning || cooldownRemaining > 0) {
                return;
            }
            spinning = true;
            updateSpinButtonState();
            if (resultElement) {
                resultElement.textContent = '';
                resultElement.classList.remove('success', 'error');
            }
            ensureAudioContext();
            if (timerElement) {
                timerElement.textContent = '...';
            }

            fetch(spinEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ action: 'spin' })
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, data: data };
                    }).catch(function () {
                        return { ok: response.ok, data: null };
                    });
                })
                .then(function (payload) {
                    if (!payload.ok || !payload.data || !payload.data.success) {
                        var errorMessage = (payload.data && payload.data.error) ? payload.data.error : 'Spin failed. Please try again.';
                        var error = new Error(errorMessage);
                        if (payload.data && typeof payload.data.cooldownRemaining !== 'undefined') {
                            error.cooldownRemaining = payload.data.cooldownRemaining;
                        }
                        if (payload.data && payload.data.balances) {
                            error.balances = payload.data.balances;
                        }
                        throw error;
                    }
                    var data = payload.data;
                    if (typeof data.segmentIndex !== 'number' || data.segmentIndex < 0 || data.segmentIndex >= segments.length) {
                        throw new Error('Invalid spin result received.');
                    }
                    var cooldownSeconds = sanitizeNumber(data.cooldownRemaining);
                    if (!cooldownSeconds && defaultCooldownSeconds) {
                        cooldownSeconds = defaultCooldownSeconds;
                    }
                    startSpinCountdown(10);
                    startSpinAnimation(data.segmentIndex, data.reward || null, data.balances || null, cooldownSeconds);
                })
                .catch(function (err) {
                    var cooldownSeconds = (err && typeof err.cooldownRemaining !== 'undefined') ? err.cooldownRemaining : null;
                    var balances = err && err.balances ? err.balances : null;
                    handleError(err && err.message ? err.message : 'Unable to spin the wheel right now.', cooldownSeconds, balances);
                });
        });
    });
})();