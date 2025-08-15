// Game Data Configuration
        const SPECIES_DATA = {
            'Kougra': {
                baseStats: { health: 80, hunger: 60, happiness: 70, cleanliness: 50 },
                colors: ['#ff6b35', '#f7931e', '#ffcc02'],
                traits: ['Energetic', 'Playful', 'Curious']
            },
            'Shoyru': {
                baseStats: { health: 70, hunger: 50, happiness: 80, cleanliness: 60 },
                colors: ['#4a90e2', '#7b68ee', '#9370db'],
                traits: ['Friendly', 'Adventurous', 'Loyal']
            },
            'Acara': {
                baseStats: { health: 75, hunger: 55, happiness: 75, cleanliness: 70 },
                colors: ['#ff69b4', '#ffc0cb', '#dda0dd'],
                traits: ['Gentle', 'Artistic', 'Peaceful']
            },
            'Gelert': {
                baseStats: { health: 85, hunger: 70, happiness: 65, cleanliness: 45 },
                colors: ['#8b4513', '#d2b48c', '#daa520'],
                traits: ['Brave', 'Protective', 'Strong']
            }
        };

        const PERSONALITY_MODIFIERS = {
            'Cheerful': { happiness: 10, health: 5, hunger: -5, cleanliness: 0 },
            'Lazy': { happiness: 5, health: -5, hunger: 10, cleanliness: -10 },
            'Active': { happiness: 5, health: 10, hunger: 5, cleanliness: -5 },
            'Calm': { happiness: 0, health: 5, hunger: -5, cleanliness: 10 },
            'Mischievous': { happiness: 15, health: 0, hunger: 0, cleanliness: -15 }
        };

        const FLAVOR_TEXTS = {
            feed: [
                "Nom nom nom! Your pet devours the delicious food happily!",
                "Your pet's eyes light up as they enjoy their meal!",
                "Mmm, that was tasty! Your pet feels satisfied.",
                "Your pet thanks you with a happy wiggle!"
            ],
            clean: [
                "Splish splash! Your pet is now sparkling clean!",
                "Your pet feels refreshed after a good wash!",
                "All clean! Your pet preens proudly.",
                "Your pet enjoys the warm, soapy bubbles!"
            ],
            play: [
                "Wheee! Your pet has a blast playing with you!",
                "Your pet bounces around with joy!",
                "What fun! Your pet is beaming with happiness!",
                "Your pet does a little victory dance after playing!"
            ],
            heal: [
                "Your pet feels much better now!",
                "The medicine works its magic! Your pet is healing.",
                "Your pet's wounds are mending nicely.",
                "With care and medicine, your pet recovers quickly!"
            ]
        };

        class Pet {
            constructor(name, species, color, gender, personality) {
                this.name = name;
                this.species = species;
                this.color = color;
                this.gender = gender;
                this.personality = personality;
                
                // Initialize stats based on species and personality
                const baseStats = SPECIES_DATA[species].baseStats;
                const personalityMod = PERSONALITY_MODIFIERS[personality];
                
                this.stats = {
                    health: Math.max(0, Math.min(100, baseStats.health + personalityMod.health)),
                    hunger: Math.max(0, Math.min(100, baseStats.hunger + personalityMod.hunger)),
                    happiness: Math.max(0, Math.min(100, baseStats.happiness + personalityMod.happiness)),
                    cleanliness: Math.max(0, Math.min(100, baseStats.cleanliness + personalityMod.cleanliness))
                };

                this.lastFed = Date.now();
                this.element = null;
            }

            updateStats(changes) {
                const oldStats = { ...this.stats };
                
                Object.keys(changes).forEach(stat => {
                    this.stats[stat] = Math.max(0, Math.min(100, this.stats[stat] + changes[stat]));
                });

                return { old: oldStats, new: { ...this.stats } };
            }

            generateSVG() {
                const colors = SPECIES_DATA[this.species].colors;
                const primaryColor = colors[0];
                const secondaryColor = colors[1];
                
                return `
                    <svg viewBox="0 0 120 120" class="pet-avatar">
                        <defs>
                            <radialGradient id="grad${this.name}" cx="50%" cy="30%">
                                <stop offset="0%" style="stop-color:${secondaryColor}"/>
                                <stop offset="100%" style="stop-color:${primaryColor}"/>
                            </radialGradient>
                        </defs>
                        
                        <!-- Body -->
                        <ellipse cx="60" cy="70" rx="35" ry="25" fill="url(#grad${this.name})" stroke="#333" stroke-width="2">
                            <animateTransform attributeName="transform" type="scale" 
                                values="1;1.05;1" dur="2s" repeatCount="indefinite"/>
                        </ellipse>
                        
                        <!-- Head -->
                        <circle cx="60" cy="40" r="25" fill="url(#grad${this.name})" stroke="#333" stroke-width="2">
                            <animateTransform attributeName="transform" type="rotate" 
                                values="0 60 40;5 60 40;-5 60 40;0 60 40" dur="4s" repeatCount="indefinite"/>
                        </circle>
                        
                        <!-- Eyes -->
                        <circle cx="52" cy="35" r="4" fill="#333">
                            <animate attributeName="ry" values="4;0.5;4" dur="3s" repeatCount="indefinite"/>
                        </circle>
                        <circle cx="68" cy="35" r="4" fill="#333">
                            <animate attributeName="ry" values="4;0.5;4" dur="3s" repeatCount="indefinite"/>
                        </circle>
                        
                        <!-- Nose -->
                        <ellipse cx="60" cy="42" rx="2" ry="3" fill="#ff69b4"/>
                        
                        <!-- Mouth -->
                        <path d="M 55 48 Q 60 52 65 48" stroke="#333" stroke-width="2" fill="none"/>
                        
                        <!-- Ears -->
                        <ellipse cx="45" cy="25" rx="8" ry="12" fill="${primaryColor}" stroke="#333" stroke-width="2"/>
                        <ellipse cx="75" cy="25" rx="8" ry="12" fill="${primaryColor}" stroke="#333" stroke-width="2"/>
                    </svg>
                `;
            }

            needsHealing() {
                return this.stats.health < 30;
            }
        }

        class GameManager {
            constructor() {
                this.pets = [];
                this.selectedPet = null;
                this.contextMenu = document.getElementById('contextMenu');
                this.modal = document.getElementById('actionModal');
                
                this.initializePets();
                this.bindEvents();
                this.startHungerTimer();
            }

            initializePets() {
                const petConfigs = [
                    { name: 'Sparkle', species: 'Kougra', color: 'Fire', gender: 'Female', personality: 'Cheerful' },
                    { name: 'Buddy', species: 'Shoyru', color: 'Blue', gender: 'Male', personality: 'Active' },
                    { name: 'Luna', species: 'Acara', color: 'Pink', gender: 'Female', personality: 'Calm' },
                    { name: 'Rex', species: 'Gelert', color: 'Brown', gender: 'Male', personality: 'Mischievous' }
                ];

                petConfigs.forEach(config => {
                    const pet = new Pet(config.name, config.species, config.color, config.gender, config.personality);
                    this.pets.push(pet);
                });

                this.renderPets();
            }

            renderPets() {
                const grid = document.getElementById('petsGrid');
                grid.innerHTML = '';

                this.pets.forEach(pet => {
                    const petCard = document.createElement('div');
                    petCard.className = 'pet-card';
                    petCard.dataset.petName = pet.name;
                    
                    petCard.innerHTML = `
                        ${pet.generateSVG()}
                        <div class="pet-info">
                            <h3>${pet.name}</h3>
                            <div class="stat-bar">
                                <span class="stat-label">Health:</span>
                                <div class="stat-progress">
                                    <div class="stat-fill health-fill" style="width: ${pet.stats.health}%"></div>
                                </div>
                            </div>
                            <div class="stat-bar">
                                <span class="stat-label">Hunger:</span>
                                <div class="stat-progress">
                                    <div class="stat-fill hunger-fill" style="width: ${pet.stats.hunger}%"></div>
                                </div>
                            </div>
                            <div class="stat-bar">
                                <span class="stat-label">Happy:</span>
                                <div class="stat-progress">
                                    <div class="stat-fill happiness-fill" style="width: ${pet.stats.happiness}%"></div>
                                </div>
                            </div>
                            <div class="stat-bar">
                                <span class="stat-label">Clean:</span>
                                <div class="stat-progress">
                                    <div class="stat-fill cleanliness-fill" style="width: ${pet.stats.cleanliness}%"></div>
                                </div>
                            </div>
                        </div>
                    `;

                    pet.element = petCard;
                    grid.appendChild(petCard);
                });
            }

            bindEvents() {
                // Pet selection and context menu
                document.addEventListener('click', (e) => {
                    const petCard = e.target.closest('.pet-card');
                    if (petCard) {
                        const petName = petCard.dataset.petName;
                        const clickedPet = this.pets.find(pet => pet.name === petName);
                        
                        // If clicking the same pet that's already selected and details are shown, close details
                        if (this.selectedPet && this.selectedPet.name === petName && 
                            document.getElementById('petDetails').style.display !== 'none') {
                            this.hidePetDetails();
                        } else {
                            // Otherwise, select the pet and show details
                            this.selectPet(petName);
                            this.showPetDetails(this.selectedPet);
                        }
                    } else {
                        this.hideContextMenu();
                    }
                });

                document.addEventListener('contextmenu', (e) => {
                    const petCard = e.target.closest('.pet-card');
                    if (petCard) {
                        e.preventDefault();
                        this.selectPet(petCard.dataset.petName);
                        this.showContextMenu(e.clientX, e.clientY);
                    }
                });

                // Context menu actions
                document.addEventListener('click', (e) => {
                    if (e.target.classList.contains('context-menu-item') && !e.target.classList.contains('disabled')) {
                        const action = e.target.dataset.action;
                        this.performAction(action);
                        this.hideContextMenu();
                    }
                });

                // Pet details close button
                document.getElementById('petDetailsClose').addEventListener('click', () => {
                    this.hidePetDetails();
                });

                // Modal close
                document.querySelector('.close').addEventListener('click', () => {
                    this.modal.style.display = 'none';
                });

                window.addEventListener('click', (e) => {
                    if (e.target === this.modal) {
                        this.modal.style.display = 'none';
                    }
                });
            }

            selectPet(petName) {
                // Remove previous selection
                document.querySelectorAll('.pet-card').forEach(card => {
                    card.classList.remove('selected');
                });

                this.selectedPet = this.pets.find(pet => pet.name === petName);
                if (this.selectedPet) {
                    this.selectedPet.element.classList.add('selected');
                }
            }

            showContextMenu(x, y) {
                if (!this.selectedPet) return;

                this.contextMenu.style.display = 'block';
                this.contextMenu.style.left = x + 'px';
                this.contextMenu.style.top = y + 'px';

                // Enable/disable heal option based on health
                const healItem = this.contextMenu.querySelector('[data-action="heal"]');
                if (this.selectedPet.needsHealing()) {
                    healItem.classList.remove('disabled');
                } else {
                    healItem.classList.add('disabled');
                }
            }

            hideContextMenu() {
                this.contextMenu.style.display = 'none';
            }

            performAction(action) {
                if (!this.selectedPet) return;

                let statChanges = {};
                let message = '';

                switch (action) {
                    case 'feed':
                        statChanges = { hunger: -30, cleanliness: -5 };
                        break;
                    case 'clean':
                        statChanges = { cleanliness: 40 };
                        break;
                    case 'play':
                        statChanges = { happiness: 25, cleanliness: -10 };
                        if (Math.random() < 0.3) statChanges.health = -5; // Sometimes risky
                        break;
                    case 'heal':
                        if (this.selectedPet.needsHealing()) {
                            statChanges = { health: 40, hunger: 15 };
                        }
                        break;
                }

                if (Object.keys(statChanges).length > 0) {
                    const result = this.selectedPet.updateStats(statChanges);
                    this.showActionModal(action, result);
                    this.updatePetDisplay(this.selectedPet);
                    this.selectedPet.element.classList.add('animated');
                    setTimeout(() => this.selectedPet.element.classList.remove('animated'), 1000);
                }
            }

            showActionModal(action, statResult) {
                const modal = this.modal;
                const pet = this.selectedPet;
                
                document.getElementById('modalPetAvatar').innerHTML = pet.generateSVG().match(/<svg[^>]*>(.*?)<\/svg>/s)[1];
                document.getElementById('modalTitle').textContent = `${pet.name} - ${action.charAt(0).toUpperCase() + action.slice(1)}`;
                
                const randomText = FLAVOR_TEXTS[action][Math.floor(Math.random() * FLAVOR_TEXTS[action].length)];
                document.getElementById('modalMessage').textContent = randomText;
                
                // Show stat changes
                const statsDiv = document.getElementById('modalStats');
                statsDiv.innerHTML = '';
                
                Object.keys(statResult.new).forEach(stat => {
                    const change = statResult.new[stat] - statResult.old[stat];
                    if (change !== 0) {
                        const statDiv = document.createElement('div');
                        statDiv.innerHTML = `${stat.charAt(0).toUpperCase() + stat.slice(1)}: ${statResult.old[stat]} → ${statResult.new[stat]} ${change > 0 ? '(+' + change + ')' : '(' + change + ')'}`;
                        statDiv.style.color = change > 0 ? '#4CAF50' : '#f44336';
                        statDiv.style.fontWeight = 'bold';
                        statsDiv.appendChild(statDiv);
                    }
                });

                modal.style.display = 'block';
            }

            updatePetDisplay(pet) {
                const petCard = pet.element;
                Object.keys(pet.stats).forEach(stat => {
                    const statFill = petCard.querySelector(`.${stat}-fill`);
                    if (statFill) {
                        statFill.style.width = pet.stats[stat] + '%';
                    }
                });
            }

            showPetDetails(pet) {
                if (!pet) return;

                const detailsDiv = document.getElementById('petDetails');
                const contentDiv = document.getElementById('detailsContent');
                
                contentDiv.innerHTML = `
                    <div style="text-align: center; margin-bottom: 20px;">
                        ${pet.generateSVG()}
                        <h3>${pet.name}</h3>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Species:</span>
                        <span>${pet.species}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Color:</span>
                        <span>${pet.color}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Gender:</span>
                        <span>${pet.gender}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Personality:</span>
                        <span>${pet.personality}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Health:</span>
                        <span>${pet.stats.health}/100</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Hunger:</span>
                        <span>${pet.stats.hunger}/100</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Happiness:</span>
                        <span>${pet.stats.happiness}/100</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Cleanliness:</span>
                        <span>${pet.stats.cleanliness}/100</span>
                    </div>
                `;
                
                detailsDiv.style.display = 'block';
            }

            hidePetDetails() {
                document.getElementById('petDetails').style.display = 'none';
                // Clear selection when hiding details
                document.querySelectorAll('.pet-card').forEach(card => {
                    card.classList.remove('selected');
                });
                this.selectedPet = null;
            }

            startHungerTimer() {
                setInterval(() => {
                    this.pets.forEach(pet => {
                        // Pets get hungry over time
                        if (Math.random() < 0.1) { // 10% chance every interval
                            pet.updateStats({ hunger: 5, cleanliness: -1 });
                            
                            // Health damage if too hungry
                            if (pet.stats.hunger > 80) {
                                pet.updateStats({ health: -3 });
                            }
                            
                            this.updatePetDisplay(pet);
                        }
                    });
                }, 10000); // Every 10 seconds for demo purposes
            }
        }

        // Initialize the game when DOM is loaded
        document.addEventListener('DOMContentLoaded', () => {
            new GameManager();
        });