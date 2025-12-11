const dataElement = document.getElementById('adventure-data');
if (!dataElement) {
    console.warn('Adventure data not found.');
} else {
    const story = JSON.parse(dataElement.textContent);
    const sceneTitle = document.getElementById('adventure-scene-title');
    const sceneBody = document.getElementById('adventure-scene-body');
    const choicesContainer = document.getElementById('adventure-choices');
    const historyList = document.getElementById('adventure-history');

    const history = [];
    let currentNodeId = story.start;

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
                p.textContent = paragraph;
                sceneBody.appendChild(p);
            });
        }

        if (choicesContainer) {
            choicesContainer.innerHTML = '';
            node.choices.forEach((choice) => {
                const button = document.createElement('button');
                button.className = 'choice-btn';
                button.type = 'button';
                button.textContent = choice.text;
                button.addEventListener('click', () => {
                    if (choice.link) {
                        window.location.href = choice.link;
                        return;
                    }
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