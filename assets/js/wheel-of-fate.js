(function () {
    function normalizeAngle(angle) {
        var twoPi = Math.PI * 2;
        return ((angle % twoPi) + twoPi) % twoPi;
    }

    function easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
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
        var segments = Array.isArray(window.WHEEL_OF_FATE_SEGMENTS) ? window.WHEEL_OF_FATE_SEGMENTS : [];
        var canvas = document.getElementById('wheel-canvas');
        var spinButton = document.getElementById('spin-button');
        var timerElement = document.getElementById('spin-timer');
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
        var countdownInterval = null;
        var colors = ['#f94144', '#f3722c', '#f8961e', '#f9844a', '#f9c74f', '#90be6d', '#43aa8b', '#577590', '#277da1', '#4d908e', '#bc4749', '#ef476f'];

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

        function resetTimerDisplay() {
            if (timerElement) {
                timerElement.textContent = '--';
            }
        }

        function startCountdown(seconds) {
            if (!timerElement) return;
            clearInterval(countdownInterval);
            var remaining = seconds;
            timerElement.textContent = String(remaining);
            countdownInterval = setInterval(function () {
                remaining -= 1;
                if (remaining <= 0) {
                    clearInterval(countdownInterval);
                    countdownInterval = null;
                    timerElement.textContent = '0';
                } else {
                    timerElement.textContent = String(remaining);
                }
            }, 1000);
        }

        function finishSpin(reward, balances) {
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
            if (timerElement) {
                timerElement.textContent = '0';
            }
            if (resultElement) {
                resultElement.classList.remove('error');
                resultElement.classList.add('success');
                if (reward && reward.type === 'currency') {
                    resultElement.textContent = 'You won ' + reward.amount + ' Cash!';
                } else if (reward && reward.type === 'item') {
                    resultElement.textContent = 'You won ' + reward.label + '!';
                } else {
                    resultElement.textContent = 'Spin complete!';
                }
            }
            if (balances && typeof window.updateCurrencyDisplay === 'function') {
                window.updateCurrencyDisplay(balances);
            }
            if (spinButton) {
                spinButton.disabled = false;
            }
            spinning = false;
        }

        function startSpinAnimation(targetIndex, reward, balances) {
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

            function step(now) {
                var elapsed = now - startTime;
                var progress = Math.min(elapsed / duration, 1);
                var eased = easeOutCubic(progress);
                currentRotation = startRotation + spinAmount * eased;
                drawWheel(currentRotation);
                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    currentRotation = normalizeAngle(targetRotation);
                    drawWheel(currentRotation);
                    finishSpin(reward, balances);
                }
            }

            requestAnimationFrame(step);
        }

        function handleError(message) {
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
            if (resultElement) {
                resultElement.classList.remove('success');
                resultElement.classList.add('error');
                resultElement.textContent = message;
            }
            if (spinButton) {
                spinButton.disabled = false;
            }
            resetTimerDisplay();
            spinning = false;
        }

        drawWheel(currentRotation);
        resetTimerDisplay();

        spinButton.addEventListener('click', function () {
            if (spinning) return;
            spinning = true;
            resultElement.textContent = '';
            resultElement.classList.remove('success', 'error');
            spinButton.disabled = true;
            timerElement.textContent = '...';

            fetch('index.php?pg=wheel-of-fate', {
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
                        throw new Error(errorMessage);
                    }
                    var data = payload.data;
                    if (typeof data.segmentIndex !== 'number' || data.segmentIndex < 0 || data.segmentIndex >= segments.length) {
                        throw new Error('Invalid spin result received.');
                    }
                    startCountdown(10);
                    startSpinAnimation(data.segmentIndex, data.reward || null, data.balances || null);
                })
                .catch(function (err) {
                    handleError(err && err.message ? err.message : 'Unable to spin the wheel right now.');
                });
        });
    });
})();