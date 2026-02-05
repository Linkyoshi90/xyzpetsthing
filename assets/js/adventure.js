const dataElement = document.getElementById('adventure-data');
if (!dataElement) {
    console.warn('Adventure data not found.');
} else {
    const story = JSON.parse(dataElement.textContent);
    const sceneTitle = document.getElementById('adventure-scene-title');
    const sceneImage = document.getElementById('adventure-scene-image');
    const sceneBody = document.getElementById('adventure-scene-body');
    const choicesContainer = document.getElementById('adventure-choices');
    const historyList = document.getElementById('adventure-history');
    const flash = document.getElementById('adventure-flash');

    const history = [];
    let currentNodeId = story.start;
    let rewardLock = false;

    const showFlash = (message, tone = 'info') => {
        if (!flash || !message) return;
        flash.textContent = message;
        flash.dataset.tone = tone;
        flash.hidden = false;
        flash.classList.remove('is-error', 'is-info');
        flash.classList.add(tone === 'error' ? 'is-error' : 'is-info');
    };

    const maybeGrantItem = async (choice) => {
        if (Array.isArray(choice.rewardNotePool) && choice.rewardNotePool.length > 0) {
            const note = choice.rewardNotePool[Math.floor(Math.random() * choice.rewardNotePool.length)];
            showFlash(`You received something special: ${note}.`, 'info');
            return;
        }
        if (choice.rewardNote) {
            showFlash(choice.rewardNote, 'info');
            return;
        }
        if (!choice.giveItem || rewardLock) {
            return;
        }
        rewardLock = true;
        try {
            const response = await fetch('urb_adventure_reward.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ item_id: choice.giveItem })
            });
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const payload = await response.json();
            if (payload?.message) {
                showFlash(payload.message, 'info');
            } else if (choice.giveItemNote) {
                showFlash(`You received something special: ${choice.giveItemNote}.`, 'info');
            }
        } catch (error) {
            console.error('Could not grant item:', error);
            showFlash('Could not deliver the item right now. Try again in a moment.', 'error');
        } finally {
            rewardLock = false;
        }
    };

    const renderHistory = () => {
        if (!historyList) return;
        historyList.innerHTML = '';
        history.forEach((entry, index) => {
            const item = document.createElement('li');
            item.innerHTML = `<strong>${index + 1}. ${entry.choice}</strong><span> → ${entry.destination}</span>`;
            historyList.appendChild(item);
        });
    };

    const renderNode = (nodeId) => {
        const node = story.nodes[nodeId];
        if (!node) {
            console.error('Missing story node', nodeId);
            return;
        }

        currentNodeId = nodeId;

        if (sceneTitle) {
            sceneTitle.textContent = node.title;
        }

        if (sceneBody) {
            sceneBody.innerHTML = '';
            node.body.forEach((paragraph) => {
                const p = document.createElement('p');
                p.innerHTML = paragraph;
                sceneBody.appendChild(p);
            });
        }

        if (sceneImage) {
            if (node.image?.src) {
                sceneImage.src = node.image.src;
                sceneImage.alt = node.image.alt || `${node.title} scene illustration`;
                sceneImage.hidden = false;
            } else {
                sceneImage.hidden = true;
                sceneImage.removeAttribute('src');
                sceneImage.alt = '';
            }
        }

        if (choicesContainer) {
            choicesContainer.innerHTML = '';
            node.choices.forEach((choice) => {
                const button = document.createElement('button');
                button.className = 'choice-btn';
                button.type = 'button';
                button.textContent = choice.text;
                button.addEventListener('click', async () => {
                    if (choice.link) {
                        window.location.href = choice.link;
                        return;
                    }
                    await maybeGrantItem(choice);
                    if (!choice.restart) {
                        history.push({
                            from: currentNodeId,
                            choice: choice.text,
                            destination: story.nodes[choice.target]?.title || choice.target
                        });
                    } else {
                        history.length = 0;
                    }
                    renderHistory();
                    renderNode(choice.target);
                });
                if (choice.note) {
                    button.setAttribute('data-note', choice.note);
                }
                choicesContainer.appendChild(button);
            });
        }
    };

    renderNode(currentNodeId);
}
