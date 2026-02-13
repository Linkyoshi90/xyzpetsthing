<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petting Mode - Harmontide</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #e8f4fc;
            --bg-secondary: #fff5f5;
            --accent-pink: #ff6b9d;
            --accent-blue: #4fc3f7;
            --accent-green: #81c784;
            --accent-orange: #ffb74d;
            --accent-purple: #ba68c8;
            --text-dark: #2d3436;
            --text-light: #636e72;
            --card-bg: rgba(255, 255, 255, 0.95);
            --shadow-soft: 0 4px 20px rgba(0, 0, 0, 0.08);
            --shadow-medium: 0 8px 32px rgba(0, 0, 0, 0.12);
            --shadow-glow: 0 0 40px rgba(255, 107, 157, 0.3);
            --radius-sm: 12px;
            --radius-md: 20px;
            --radius-lg: 28px;
            --radius-full: 9999px;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
            overflow-x: hidden;
        }

        .page-header {
            text-align: center;
            margin-bottom: 20px;
            color: white;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 900;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .page-header p {
            font-size: 1rem;
            opacity: 0.9;
            max-width: 500px;
        }

        /* Main Petting Container */
        .petting-container {
            width: 100%;
            max-width: 1000px;
            aspect-ratio: 16 / 10;
            background: linear-gradient(180deg, 
                #87ceeb 0%, 
                #b4e4f7 20%, 
                #d4f1d4 50%, 
                #c8e6c9 70%,
                #a5d6a7 100%
            );
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-medium), var(--shadow-glow);
            position: relative;
            overflow: hidden;
            border: 4px solid rgba(255, 255, 255, 0.5);
        }

        /* Decorative Background Elements */
        .bg-decoration {
            position: absolute;
            pointer-events: none;
            z-index: 1;
        }

        .cloud {
            position: absolute;
            background: rgba(255, 255, 255, 0.8);
            border-radius: var(--radius-full);
            filter: blur(1px);
        }

        .cloud::before, .cloud::after {
            content: '';
            position: absolute;
            background: inherit;
            border-radius: inherit;
        }

        .cloud-1 {
            width: 100px;
            height: 40px;
            top: 10%;
            left: 10%;
            animation: floatCloud 20s ease-in-out infinite;
        }

        .cloud-1::before {
            width: 50px;
            height: 50px;
            top: -25px;
            left: 15px;
        }

        .cloud-1::after {
            width: 60px;
            height: 40px;
            top: -15px;
            right: 10px;
        }

        .cloud-2 {
            width: 80px;
            height: 30px;
            top: 15%;
            right: 15%;
            animation: floatCloud 25s ease-in-out infinite reverse;
        }

        .cloud-2::before {
            width: 40px;
            height: 40px;
            top: -20px;
            left: 10px;
        }

        .grass-patch {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 25%;
            background: linear-gradient(180deg, 
                transparent 0%,
                rgba(139, 195, 74, 0.3) 30%,
                rgba(104, 159, 56, 0.4) 100%
            );
        }

        .flowers {
            position: absolute;
            bottom: 5%;
            font-size: 1.5rem;
            opacity: 0.7;
            animation: swayFlower 3s ease-in-out infinite;
        }

        .flower-1 { left: 5%; animation-delay: 0s; }
        .flower-2 { left: 15%; animation-delay: 0.5s; }
        .flower-3 { left: 85%; animation-delay: 1s; }
        .flower-4 { right: 8%; animation-delay: 1.5s; }

        @keyframes floatCloud {
            0%, 100% { transform: translateX(0); }
            50% { transform: translateX(30px); }
        }

        @keyframes swayFlower {
            0%, 100% { transform: rotate(-5deg); }
            50% { transform: rotate(5deg); }
        }

        /* Status Bars */
        .status-panel {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 20;
        }

        .status-bar {
            background: var(--card-bg);
            border-radius: var(--radius-md);
            padding: 12px 16px;
            min-width: 200px;
            box-shadow: var(--shadow-soft);
            backdrop-filter: blur(10px);
        }

        .status-bar-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
        }

        .status-bar-icon {
            font-size: 1.2rem;
        }

        .status-bar-label {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 0.9rem;
        }

        .status-bar-track {
            height: 14px;
            background: #e0e0e0;
            border-radius: var(--radius-full);
            overflow: hidden;
            position: relative;
        }

        .status-bar-fill {
            height: 100%;
            border-radius: var(--radius-full);
            transition: width 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        .status-bar-fill::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .hunger-fill {
            background: linear-gradient(90deg, #ffb74d, #ff9800);
        }

        .health-fill {
            background: linear-gradient(90deg, #81c784, #4caf50);
        }

        .happiness-fill {
            background: linear-gradient(90deg, #f48fb1, #e91e63);
        }

        .status-bar-value {
            font-size: 0.75rem;
            color: var(--text-light);
            text-align: right;
            margin-top: 4px;
            font-weight: 600;
        }

        /* Notification Banner */
        .notification-banner {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple));
            color: white;
            padding: 12px 24px;
            border-radius: var(--radius-full);
            font-weight: 700;
            box-shadow: var(--shadow-medium);
            z-index: 30;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            white-space: nowrap;
        }

        .notification-banner.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        /* Pet Display Area */
        .pet-stage {
            position: absolute;
            inset: 0;
            z-index: 10;
            cursor: pointer;
        }

        .pet-sprite {
            position: absolute;
            width: 180px;
            height: 180px;
            left: 50%;
            bottom: 20%;
            transform: translateX(-50%);
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 15;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
        }

        .pet-sprite img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .pet-sprite.hopping {
            animation: hop 0.4s ease-out;
        }

        .pet-sprite.eating {
            animation: eat 0.5s ease-in-out;
        }

        .pet-sprite.happy {
            animation: bounce 0.6s ease-in-out;
        }

        .pet-shadow {
            position: absolute;
            width: 120px;
            height: 30px;
            left: 50%;
            bottom: calc(20% - 10px);
            transform: translateX(-50%);
            background: radial-gradient(ellipse, rgba(0, 0, 0, 0.25) 0%, transparent 70%);
            z-index: 14;
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes hop {
            0%, 100% { transform: translateX(-50%) translateY(0) scale(1); }
            50% { transform: translateX(-50%) translateY(-40px) scale(1.05); }
        }

        @keyframes eat {
            0%, 100% { transform: translateX(-50%) scale(1); }
            25% { transform: translateX(-50%) scale(1.1, 0.9); }
            50% { transform: translateX(-50%) scale(0.95, 1.1); }
            75% { transform: translateX(-50%) scale(1.05, 0.95); }
        }

        @keyframes bounce {
            0%, 100% { transform: translateX(-50%) translateY(0) rotate(0deg); }
            25% { transform: translateX(-50%) translateY(-20px) rotate(-5deg); }
            50% { transform: translateX(-50%) translateY(-30px) rotate(5deg); }
            75% { transform: translateX(-50%) translateY(-15px) rotate(-3deg); }
        }

        /* Pet Name Tag */
        .pet-name-tag {
            position: absolute;
            left: 50%;
            bottom: calc(20% + 200px);
            transform: translateX(-50%);
            background: var(--card-bg);
            padding: 8px 20px;
            border-radius: var(--radius-full);
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--text-dark);
            box-shadow: var(--shadow-soft);
            z-index: 16;
            white-space: nowrap;
        }

        .pet-name-tag .level {
            font-size: 0.8rem;
            color: var(--accent-purple);
            margin-left: 8px;
        }

        /* Effects Layer */
        .effects-layer {
            position: absolute;
            inset: 0;
            pointer-events: none;
            z-index: 25;
        }

        .heart {
            position: absolute;
            font-size: 2rem;
            animation: floatHeart 1.5s ease-out forwards;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        @keyframes floatHeart {
            0% { 
                opacity: 1; 
                transform: translateY(0) scale(0.5) rotate(-10deg); 
            }
            50% { 
                opacity: 1; 
                transform: translateY(-60px) scale(1.2) rotate(10deg); 
            }
            100% { 
                opacity: 0; 
                transform: translateY(-120px) scale(0.8) rotate(-5deg); 
            }
        }

        .sparkle {
            position: absolute;
            width: 20px;
            height: 20px;
            animation: sparkle 0.8s ease-out forwards;
        }

        .sparkle::before, .sparkle::after {
            content: '✨';
            font-size: 1.5rem;
        }

        @keyframes sparkle {
            0% { opacity: 0; transform: scale(0) rotate(0deg); }
            50% { opacity: 1; transform: scale(1.2) rotate(180deg); }
            100% { opacity: 0; transform: scale(0.5) rotate(360deg); }
        }

        .crumb {
            position: absolute;
            width: 8px;
            height: 8px;
            background: linear-gradient(135deg, #d4a574, #b8956e);
            border-radius: 50%;
            animation: crumbFall 1s ease-out forwards;
        }

        @keyframes crumbFall {
            0% { opacity: 1; transform: translateY(0) rotate(0deg); }
            100% { opacity: 0; transform: translateY(100px) rotate(360deg); }
        }

        .dust-puff {
            position: absolute;
            width: 60px;
            height: 40px;
            background: radial-gradient(ellipse, rgba(255, 255, 255, 0.8) 0%, transparent 70%);
            animation: dustPuff 0.6s ease-out forwards;
        }

        @keyframes dustPuff {
            0% { opacity: 0; transform: scale(0.5); }
            50% { opacity: 0.8; transform: scale(1.2); }
            100% { opacity: 0; transform: scale(1.5); }
        }

        /* Control Buttons */
        .control-buttons {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 12px;
            z-index: 20;
        }

        .control-btn {
            width: 56px;
            height: 56px;
            border: none;
            border-radius: var(--radius-md);
            background: var(--card-bg);
            box-shadow: var(--shadow-soft);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.2s ease;
            position: relative;
        }

        .control-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-medium);
        }

        .control-btn:active {
            transform: translateY(0);
        }

        .control-btn.active {
            background: linear-gradient(135deg, var(--accent-pink), var(--accent-purple));
            color: white;
        }

        .control-btn .badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 22px;
            height: 22px;
            background: var(--accent-orange);
            color: white;
            font-size: 0.7rem;
            font-weight: 800;
            border-radius: var(--radius-full);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* Slide-out Panels */
        .slide-panel {
            position: absolute;
            background: linear-gradient(180deg, rgba(30, 30, 50, 0.95), rgba(50, 40, 70, 0.95));
            backdrop-filter: blur(20px);
            z-index: 50;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            color: white;
        }

        .slide-panel-top {
            top: 0;
            left: 0;
            right: 0;
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
            transform: translateY(-100%);
            padding: 20px;
        }

        .slide-panel-bottom {
            bottom: 0;
            left: 0;
            right: 0;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0;
            transform: translateY(100%);
            padding: 20px;
        }

        .slide-panel-top.show {
            transform: translateY(0);
        }

        .slide-panel-bottom.show {
            transform: translateY(0);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .panel-title {
            font-size: 1.2rem;
            font-weight: 800;
        }

        .panel-close {
            width: 36px;
            height: 36px;
            border: none;
            border-radius: var(--radius-sm);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .panel-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Item Grid */
        .item-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 12px;
            max-height: 200px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .item-grid::-webkit-scrollbar {
            width: 6px;
        }

        .item-grid::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        .item-grid::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .item-card {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: var(--radius-md);
            padding: 12px;
            text-align: center;
            cursor: grab;
            transition: all 0.2s ease;
            position: relative;
        }

        .item-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .item-card:active {
            cursor: grabbing;
        }

        .item-card .item-icon {
            font-size: 2.5rem;
            margin-bottom: 8px;
            display: block;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
        }

        .item-card .item-name {
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .item-card .item-quantity {
            font-size: 0.75rem;
            opacity: 0.7;
        }

        .item-card .item-preference {
            position: absolute;
            top: 6px;
            right: 6px;
            font-size: 0.7rem;
        }

        /* Pet Cards in Switch Panel */
        .pet-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
            gap: 16px;
        }

        .pet-card {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid rgba(255, 255, 255, 0.15);
            border-radius: var(--radius-md);
            padding: 16px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .pet-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.02);
            border-color: var(--accent-pink);
        }

        .pet-card.active {
            border-color: var(--accent-green);
            background: rgba(129, 199, 132, 0.2);
        }

        .pet-card .pet-icon {
            font-size: 3.5rem;
            margin-bottom: 8px;
            display: block;
        }

        .pet-card .pet-name {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .pet-card .pet-level {
            font-size: 0.75rem;
            opacity: 0.7;
        }

        /* Drag Proxy */
        .drag-proxy {
            position: fixed;
            pointer-events: none;
            z-index: 1000;
            font-size: 4rem;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.3));
            transform: translate(-50%, -50%);
            transition: transform 0.1s ease;
        }

        .drag-proxy.dragging {
            transform: translate(-50%, -50%) scale(1.2);
        }

        /* Eating Animation */
        .eating-item {
            position: absolute;
            font-size: 3rem;
            z-index: 30;
            animation: eatItem 0.6s ease-out forwards;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
        }

        @keyframes eatItem {
            0% { 
                opacity: 1; 
                transform: translate(-50%, -50%) scale(1); 
            }
            50% { 
                opacity: 1; 
                transform: translate(-50%, -50%) scale(1.3); 
            }
            100% { 
                opacity: 0; 
                transform: translate(-50%, -50%) scale(0); 
            }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            opacity: 0.7;
        }

        .empty-state .empty-icon {
            font-size: 3rem;
            margin-bottom: 12px;
            opacity: 0.5;
        }

        .empty-state p {
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .petting-container {
                aspect-ratio: 4 / 5;
                border-radius: var(--radius-md);
            }

            .control-buttons {
                flex-direction: row;
                top: auto;
                bottom: 20px;
                left: 50%;
                transform: translateX(-50%);
            }

            .status-panel {
                top: 10px;
                right: 10px;
            }

            .status-bar {
                min-width: 150px;
                padding: 10px 12px;
            }

            .pet-sprite {
                width: 140px;
                height: 140px;
            }

            .slide-panel-top {
                max-height: 50vh;
            }
        }

        /* Touch feedback */
        @media (hover: none) {
            .control-btn:active {
                transform: scale(0.95);
            }

            .item-card:active {
                transform: scale(0.98);
            }
        }

        /* Petting interaction indicator */
        .pet-indicator {
            position: absolute;
            width: 60px;
            height: 60px;
            border: 3px solid var(--accent-pink);
            border-radius: 50%;
            pointer-events: none;
            z-index: 100;
            opacity: 0;
            animation: ripple 0.6s ease-out forwards;
        }

        @keyframes ripple {
            0% { 
                transform: translate(-50%, -50%) scale(0.5); 
                opacity: 1; 
            }
            100% { 
                transform: translate(-50%, -50%) scale(2); 
                opacity: 0; 
            }
        }
    </style>
</head>
<body>
    <header class="page-header">
        <h1>💕 Petting Mode</h1>
        <p>Click anywhere to call your pet, drag food to feed them, and watch them play!</p>
    </header>

    <div class="petting-container" id="pettingContainer">
        <!-- Background Decorations -->
        <div class="bg-decoration cloud cloud-1"></div>
        <div class="bg-decoration cloud cloud-2"></div>
        <div class="grass-patch"></div>
        <span class="flowers flower-1">🌸</span>
        <span class="flowers flower-2">🌼</span>
        <span class="flowers flower-3">🌺</span>
        <span class="flowers flower-4">🌷</span>

        <!-- Notification Banner -->
        <div class="notification-banner" id="notificationBanner">
            ✨ She's full!
        </div>

        <!-- Status Panel -->
        <div class="status-panel">
            <div class="status-bar" id="hungerBar">
                <div class="status-bar-header">
                    <span class="status-bar-icon">🍖</span>
                    <span class="status-bar-label">Hunger</span>
                </div>
                <div class="status-bar-track">
                    <div class="status-bar-fill hunger-fill" id="hungerFill" style="width: 65%"></div>
                </div>
                <div class="status-bar-value" id="hungerValue">65 / 100</div>
            </div>

            <div class="status-bar" id="healthBar">
                <div class="status-bar-header">
                    <span class="status-bar-icon">💚</span>
                    <span class="status-bar-label">Health</span>
                </div>
                <div class="status-bar-track">
                    <div class="status-bar-fill health-fill" id="healthFill" style="width: 80%"></div>
                </div>
                <div class="status-bar-value" id="healthValue">80 / 100</div>
            </div>

            <div class="status-bar" id="happinessBar">
                <div class="status-bar-header">
                    <span class="status-bar-icon">💖</span>
                    <span class="status-bar-label">Happiness</span>
                </div>
                <div class="status-bar-track">
                    <div class="status-bar-fill happiness-fill" id="happinessFill" style="width: 85%"></div>
                </div>
                <div class="status-bar-value" id="happinessValue">85 / 100</div>
            </div>
        </div>

        <!-- Control Buttons -->
        <div class="control-buttons">
            <button class="control-btn" id="foodBtn" title="Food Inventory">
                🍱
                <span class="badge" id="foodBadge">8</span>
            </button>
            <button class="control-btn" id="healBtn" title="Healing Items">
                🧪
                <span class="badge" id="healBadge">3</span>
            </button>
            <button class="control-btn" id="petBtn" title="Switch Pet">
                🐾
            </button>
        </div>

        <!-- Food Panel -->
        <div class="slide-panel slide-panel-top" id="foodPanel">
            <div class="panel-header">
                <span class="panel-title">🍱 Food Inventory</span>
                <button class="panel-close" id="foodClose">✕</button>
            </div>
            <div class="item-grid" id="foodGrid">
                <!-- Populated by JS -->
            </div>
        </div>

        <!-- Healing Panel -->
        <div class="slide-panel slide-panel-top" id="healPanel">
            <div class="panel-header">
                <span class="panel-title">🧪 Healing Items</span>
                <button class="panel-close" id="healClose">✕</button>
            </div>
            <div class="item-grid" id="healGrid">
                <!-- Populated by JS -->
            </div>
        </div>

        <!-- Pet Switch Panel -->
        <div class="slide-panel slide-panel-bottom" id="petPanel">
            <div class="panel-header">
                <span class="panel-title">🐾 Your Pets</span>
                <button class="panel-close" id="petClose">✕</button>
            </div>
            <div class="pet-grid" id="petGrid">
                <!-- Populated by JS -->
            </div>
        </div>

        <!-- Pet Stage -->
        <div class="pet-stage" id="petStage">
            <div class="pet-name-tag" id="petNameTag">
                <span id="petName">Sparkle</span>
                <span class="level" id="petLevel">Lv. 12</span>
            </div>
            <div class="pet-shadow" id="petShadow"></div>
            <div class="pet-sprite" id="petSprite">
                <span style="font-size: 8rem; line-height: 1;">🦋</span>
            </div>
        </div>

        <!-- Effects Layer -->
        <div class="effects-layer" id="effectsLayer"></div>
    </div>

    <script>
        // ==========================================
        // Game Data
        // ==========================================
        const gameData = {
            activePetId: 1,
            pets: [
                {
                    id: 1,
                    name: 'Sparkle',
                    species: 'flutterby',
                    emoji: '🦋',
                    level: 12,
                    hunger: 65,
                    health: 80,
                    maxHealth: 100,
                    happiness: 85,
                    preferences: { 1: 3, 2: 2, 3: 1 } // food_id: hearts (1-3)
                },
                {
                    id: 2,
                    name: 'Bubbles',
                    species: 'glimmerfin',
                    emoji: '🐟',
                    level: 8,
                    hunger: 45,
                    health: 100,
                    maxHealth: 100,
                    happiness: 70,
                    preferences: { 1: 1, 2: 3, 4: 2 }
                },
                {
                    id: 3,
                    name: 'Shadow',
                    species: 'starmane',
                    emoji: '🦁',
                    level: 15,
                    hunger: 90,
                    health: 60,
                    maxHealth: 120,
                    happiness: 50,
                    preferences: { 3: 3, 5: 2 }
                }
            ],
            food: [
                { id: 1, name: 'Starfruit', emoji: '🍎', quantity: 5, replenish: 15 },
                { id: 2, name: 'Moonberry', emoji: '🫐', quantity: 8, replenish: 10 },
                { id: 3, name: 'Dream Cake', emoji: '🍰', quantity: 2, replenish: 30 },
                { id: 4, name: 'Honey Drop', emoji: '🍯', quantity: 4, replenish: 12 },
                { id: 5, name: 'Crystal Candy', emoji: '🍬', quantity: 3, replenish: 20 },
                { id: 6, name: 'Rainbow Kelp', emoji: '🥬', quantity: 6, replenish: 8 }
            ],
            healing: [
                { id: 101, name: 'Healing Potion', emoji: '💊', quantity: 3, heal: 30 },
                { id: 102, name: 'Health Elixir', emoji: '🧴', quantity: 1, heal: 50 },
                { id: 103, name: 'Magic Bandage', emoji: '🩹', quantity: 5, heal: 15 }
            ]
        };

        // ==========================================
        // DOM Elements
        // ==========================================
        const container = document.getElementById('pettingContainer');
        const petStage = document.getElementById('petStage');
        const petSprite = document.getElementById('petSprite');
        const petShadow = document.getElementById('petShadow');
        const petNameEl = document.getElementById('petName');
        const petLevelEl = document.getElementById('petLevel');
        const effectsLayer = document.getElementById('effectsLayer');
        const notificationBanner = document.getElementById('notificationBanner');

        // Status bars
        const hungerFill = document.getElementById('hungerFill');
        const hungerValue = document.getElementById('hungerValue');
        const healthFill = document.getElementById('healthFill');
        const healthValue = document.getElementById('healthValue');
        const happinessFill = document.getElementById('happinessFill');
        const happinessValue = document.getElementById('happinessValue');

        // Buttons and panels
        const foodBtn = document.getElementById('foodBtn');
        const healBtn = document.getElementById('healBtn');
        const petBtn = document.getElementById('petBtn');
        const foodPanel = document.getElementById('foodPanel');
        const healPanel = document.getElementById('healPanel');
        const petPanel = document.getElementById('petPanel');
        const foodGrid = document.getElementById('foodGrid');
        const healGrid = document.getElementById('healGrid');
        const petGrid = document.getElementById('petGrid');
        const foodBadge = document.getElementById('foodBadge');
        const healBadge = document.getElementById('healBadge');

        // ==========================================
        // State
        // ==========================================
        let activePet = gameData.pets.find(p => p.id === gameData.activePetId);
        let draggingItem = null;
        let dragProxy = null;
        let idleTimer = null;

        // ==========================================
        // Utility Functions
        // ==========================================
        function clamp(value, min, max) {
            return Math.min(Math.max(value, min), max);
        }

        function getActivePet() {
            return gameData.pets.find(p => p.id === gameData.activePetId);
        }

        function showNotification(message) {
            notificationBanner.textContent = message;
            notificationBanner.classList.add('show');
            setTimeout(() => notificationBanner.classList.remove('show'), 3000);
        }

        // ==========================================
        // UI Updates
        // ==========================================
        function updateStatusBars() {
            const pet = getActivePet();
            if (!pet) return;

            // Hunger
            hungerFill.style.width = `${pet.hunger}%`;
            hungerValue.textContent = `${pet.hunger} / 100`;

            // Health
            const healthPercent = Math.round((pet.health / pet.maxHealth) * 100);
            healthFill.style.width = `${healthPercent}%`;
            healthValue.textContent = `${pet.health} / ${pet.maxHealth}`;

            // Happiness
            happinessFill.style.width = `${pet.happiness}%`;
            happinessValue.textContent = `${pet.happiness} / 100`;
        }

        function updateBadges() {
            const totalFood = gameData.food.reduce((sum, item) => sum + item.quantity, 0);
            const totalHealing = gameData.healing.reduce((sum, item) => sum + item.quantity, 0);
            foodBadge.textContent = totalFood;
            healBadge.textContent = totalHealing;
        }

        function setPetPosition(leftPx, bottomPx) {
            petSprite.style.left = `${leftPx}px`;
            petSprite.style.bottom = `${bottomPx}px`;
            petShadow.style.left = `${leftPx}px`;
            petShadow.style.bottom = `${bottomPx - 10}px`;
        }

        function updatePetDisplay() {
            const pet = getActivePet();
            if (!pet) return;

            petSprite.innerHTML = `<span style="font-size: 8rem; line-height: 1;">${pet.emoji}</span>`;
            petNameEl.textContent = pet.name;
            petLevelEl.textContent = `Lv. ${pet.level}`;
            updateStatusBars();
        }

        // ==========================================
        // Panels
        // ==========================================
        function closeAllPanels() {
            foodPanel.classList.remove('show');
            healPanel.classList.remove('show');
            petPanel.classList.remove('show');
            foodBtn.classList.remove('active');
            healBtn.classList.remove('active');
            petBtn.classList.remove('active');
        }

        function togglePanel(panel, btn) {
            const isShowing = panel.classList.contains('show');
            closeAllPanels();
            if (!isShowing) {
                panel.classList.add('show');
                btn.classList.add('active');
            }
        }

        foodBtn.addEventListener('click', () => togglePanel(foodPanel, foodBtn));
        healBtn.addEventListener('click', () => togglePanel(healPanel, healBtn));
        petBtn.addEventListener('click', () => togglePanel(petPanel, petBtn));

        document.getElementById('foodClose').addEventListener('click', closeAllPanels);
        document.getElementById('healClose').addEventListener('click', closeAllPanels);
        document.getElementById('petClose').addEventListener('click', closeAllPanels);

        // ==========================================
        // Render Functions
        // ==========================================
        function renderFoodGrid() {
            foodGrid.innerHTML = '';
            
            const pet = getActivePet();
            const availableFood = gameData.food.filter(item => item.quantity > 0);
            
            if (availableFood.length === 0) {
                foodGrid.innerHTML = `
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <div class="empty-icon">🍽️</div>
                        <p>No food in inventory!</p>
                    </div>
                `;
                return;
            }

            availableFood.forEach(item => {
                const pref = pet?.preferences?.[item.id] || 0;
                const hearts = pref > 0 ? '❤️'.repeat(pref) : '';
                
                const card = document.createElement('div');
                card.className = 'item-card';
                card.dataset.itemId = item.id;
                card.dataset.type = 'food';
                card.innerHTML = `
                    <span class="item-icon">${item.emoji}</span>
                    <div class="item-name">${item.name}</div>
                    <div class="item-quantity">x${item.quantity} • +${item.replenish}🍖</div>
                    ${hearts ? `<div class="item-preference">${hearts}</div>` : ''}
                `;
                
                card.addEventListener('pointerdown', (e) => startDrag(e, item, 'food'));
                foodGrid.appendChild(card);
            });
        }

        function renderHealGrid() {
            healGrid.innerHTML = '';
            
            const availableHealing = gameData.healing.filter(item => item.quantity > 0);
            
            if (availableHealing.length === 0) {
                healGrid.innerHTML = `
                    <div class="empty-state" style="grid-column: 1 / -1;">
                        <div class="empty-icon">💊</div>
                        <p>No healing items!</p>
                    </div>
                `;
                return;
            }

            availableHealing.forEach(item => {
                const card = document.createElement('div');
                card.className = 'item-card';
                card.dataset.itemId = item.id;
                card.dataset.type = 'healing';
                card.innerHTML = `
                    <span class="item-icon">${item.emoji}</span>
                    <div class="item-name">${item.name}</div>
                    <div class="item-quantity">x${item.quantity} • +${item.heal}💚</div>
                `;
                
                card.addEventListener('pointerdown', (e) => startDrag(e, item, 'healing'));
                healGrid.appendChild(card);
            });
        }

        function renderPetGrid() {
            petGrid.innerHTML = '';

            gameData.pets.forEach(pet => {
                const isActive = pet.id === gameData.activePetId;
                const card = document.createElement('div');
                card.className = `pet-card ${isActive ? 'active' : ''}`;
                card.dataset.petId = pet.id;
                card.innerHTML = `
                    <span class="pet-icon">${pet.emoji}</span>
                    <div class="pet-name">${pet.name}</div>
                    <div class="pet-level">Level ${pet.level}</div>
                `;
                
                card.addEventListener('click', () => switchPet(pet.id));
                petGrid.appendChild(card);
            });
        }

        // ==========================================
        // Drag and Drop
        // ==========================================
        function startDrag(e, item, type) {
            e.preventDefault();
            draggingItem = { item, type };
            
            dragProxy = document.createElement('div');
            dragProxy.className = 'drag-proxy';
            dragProxy.textContent = item.emoji;
            document.body.appendChild(dragProxy);
            
            moveDrag(e.clientX, e.clientY);
            closeAllPanels();
            
            window.addEventListener('pointermove', handleDragMove);
            window.addEventListener('pointerup', handleDragEnd, { once: true });
        }

        function moveDrag(x, y) {
            if (dragProxy) {
                dragProxy.style.left = `${x}px`;
                dragProxy.style.top = `${y}px`;
                dragProxy.classList.add('dragging');
            }
        }

        function handleDragMove(e) {
            if (!draggingItem) return;
            moveDrag(e.clientX, e.clientY);
        }

        function handleDragEnd(e) {
            window.removeEventListener('pointermove', handleDragMove);
            
            const petRect = petSprite.getBoundingClientRect();
            const droppedOnPet = (
                e.clientX >= petRect.left &&
                e.clientX <= petRect.right &&
                e.clientY >= petRect.top &&
                e.clientY <= petRect.bottom
            );

            if (droppedOnPet && draggingItem) {
                if (draggingItem.type === 'food') {
                    feedPet(draggingItem.item, e.clientX, e.clientY);
                } else {
                    healPet(draggingItem.item, e.clientX, e.clientY);
                }
            } else if (draggingItem?.type === 'food') {
                // Drop crumbs when missing pet
                createCrumbs(e.clientX, e.clientY);
            }

            if (dragProxy) {
                dragProxy.remove();
                dragProxy = null;
            }
            draggingItem = null;
        }

        // ==========================================
        // Pet Actions
        // ==========================================
        function feedPet(item, x, y) {
            const pet = getActivePet();
            if (!pet) return;

            if (pet.hunger >= 100) {
                showNotification('✨ She\'s full!');
                return;
            }

            // Optimistic update
            const oldHunger = pet.hunger;
            const oldQuantity = item.quantity;

            pet.hunger = clamp(pet.hunger + item.replenish, 0, 100);
            item.quantity = Math.max(0, item.quantity - 1);

            // Calculate hearts based on preference
            const pref = pet.preferences?.[item.id] || 2;
            const hearts = clamp(pref, 1, 3);

            // Add happiness
            pet.happiness = clamp(pet.happiness + hearts * 3, 0, 100);

            // Animations
            createEatingAnimation(item.emoji, x, y);
            petSprite.classList.add('eating');
            setTimeout(() => petSprite.classList.remove('eating'), 500);

            // Effects
            setTimeout(() => {
                createHearts(hearts);
                createCrumbs(x, y, 4);
                createSparkles();
            }, 200);

            updateStatusBars();
            updateBadges();
            renderFoodGrid();

            if (pet.hunger >= 100) {
                setTimeout(() => showNotification('✨ She\'s full!'), 500);
            }
        }

        function healPet(item, x, y) {
            const pet = getActivePet();
            if (!pet) return;

            if (pet.health >= pet.maxHealth) {
                showNotification('💚 Already at full health!');
                return;
            }

            // Optimistic update
            pet.health = clamp(pet.health + item.heal, 0, pet.maxHealth);
            item.quantity = Math.max(0, item.quantity - 1);

            // Animations
            createEatingAnimation(item.emoji, x, y);
            petSprite.classList.add('happy');
            setTimeout(() => petSprite.classList.remove('happy'), 600);

            // Effects
            setTimeout(() => {
                createHearts(2, '#81c784');
            }, 200);

            updateStatusBars();
            updateBadges();
            renderHealGrid();
        }

        function switchPet(petId) {
            if (petId === gameData.activePetId) {
                closeAllPanels();
                return;
            }

            const stageRect = petStage.getBoundingClientRect();
            const currentBottom = parseFloat(getComputedStyle(petSprite).bottom) || stageRect.height * 0.2;

            // Run off screen
            setPetPosition(-150, currentBottom);
            petSprite.style.opacity = '0.5';

            setTimeout(() => {
                gameData.activePetId = petId;
                activePet = getActivePet();
                
                updatePetDisplay();
                
                // Enter from other side
                setPetPosition(stageRect.width + 150, currentBottom);
                petSprite.style.opacity = '1';

                requestAnimationFrame(() => {
                    setPetPosition(stageRect.width / 2, currentBottom);
                    createDustPuff(stageRect.width / 2, stageRect.height - currentBottom);
                });
            }, 300);

            closeAllPanels();
            renderPetGrid();
        }

        // ==========================================
        // Effects
        // ==========================================
        function createHearts(count, color = '#ff6b9d') {
            const petRect = petSprite.getBoundingClientRect();
            const stageRect = petStage.getBoundingClientRect();

            for (let i = 0; i < count; i++) {
                setTimeout(() => {
                    const heart = document.createElement('div');
                    heart.className = 'heart';
                    heart.textContent = '❤';
                    heart.style.color = color;
                    heart.style.left = `${petRect.left - stageRect.left + petRect.width / 2 + (i - (count - 1) / 2) * 30}px`;
                    heart.style.top = `${petRect.top - stageRect.top}px`;
                    effectsLayer.appendChild(heart);
                    setTimeout(() => heart.remove(), 1500);
                }, i * 100);
            }
        }

        function createSparkles(count = 3) {
            const petRect = petSprite.getBoundingClientRect();
            const stageRect = petStage.getBoundingClientRect();

            for (let i = 0; i < count; i++) {
                const sparkle = document.createElement('div');
                sparkle.className = 'sparkle';
                sparkle.style.left = `${petRect.left - stageRect.left + Math.random() * petRect.width}px`;
                sparkle.style.top = `${petRect.top - stageRect.top + Math.random() * petRect.height}px`;
                effectsLayer.appendChild(sparkle);
                setTimeout(() => sparkle.remove(), 800);
            }
        }

        function createCrumbs(x, y, count = 6) {
            const stageRect = petStage.getBoundingClientRect();

            for (let i = 0; i < count; i++) {
                const crumb = document.createElement('div');
                crumb.className = 'crumb';
                crumb.style.left = `${x - stageRect.left + (Math.random() * 30 - 15)}px`;
                crumb.style.top = `${y - stageRect.top}px`;
                effectsLayer.appendChild(crumb);
                setTimeout(() => crumb.remove(), 1000);
            }
        }

        function createDustPuff(x, y) {
            const stageRect = petStage.getBoundingClientRect();
            const dust = document.createElement('div');
            dust.className = 'dust-puff';
            dust.style.left = `${x - 30}px`;
            dust.style.top = `${y - 20}px`;
            effectsLayer.appendChild(dust);
            setTimeout(() => dust.remove(), 600);
        }

        function createEatingAnimation(emoji, x, y) {
            const stageRect = petStage.getBoundingClientRect();
            const eating = document.createElement('div');
            eating.className = 'eating-item';
            eating.textContent = emoji;
            eating.style.left = `${x}px`;
            eating.style.top = `${y}px`;
            document.body.appendChild(eating);
            setTimeout(() => eating.remove(), 600);
        }

        function createPetIndicator(x, y) {
            const stageRect = petStage.getBoundingClientRect();
            const indicator = document.createElement('div');
            indicator.className = 'pet-indicator';
            indicator.style.left = `${x - stageRect.left}px`;
            indicator.style.top = `${y - stageRect.top}px`;
            effectsLayer.appendChild(indicator);
            setTimeout(() => indicator.remove(), 600);
        }

        // ==========================================
        // Pet Movement & Idle Behavior
        // ==========================================
        function hopTo(leftPx, bottomPx) {
            const stageRect = petStage.getBoundingClientRect();
            const clampedLeft = clamp(leftPx, 100, stageRect.width - 100);
            const clampedBottom = clamp(bottomPx, stageRect.height * 0.1, stageRect.height * 0.35);

            setPetPosition(clampedLeft, clampedBottom);
            petSprite.classList.add('hopping');
            createDustPuff(clampedLeft, stageRect.height - clampedBottom);

            setTimeout(() => petSprite.classList.remove('hopping'), 400);
        }

        function idleBehavior() {
            const pet = getActivePet();
            if (!pet) return;

            const stageRect = petStage.getBoundingClientRect();
            const rand = Math.random();

            if (rand < 0.4) {
                // Hop to new position
                const newLeft = Math.random() * (stageRect.width - 200) + 100;
                const newBottom = Math.random() * (stageRect.height * 0.15) + stageRect.height * 0.12;
                hopTo(newLeft, newBottom);
            } else if (rand < 0.7) {
                // Play animation
                petSprite.classList.add('happy');
                setTimeout(() => petSprite.classList.remove('happy'), 600);
            } else {
                // Subtle movement
                const currentLeft = parseFloat(getComputedStyle(petSprite).left) || stageRect.width / 2;
                const offset = (Math.random() - 0.5) * 20;
                petSprite.style.left = `${clamp(currentLeft + offset, 100, stageRect.width - 100)}px`;
            }
        }

        function resetIdleTimer() {
            clearTimeout(idleTimer);
            idleTimer = setTimeout(() => {
                idleBehavior();
                resetIdleTimer();
            }, 4000 + Math.random() * 3000);
        }

        // Click to call pet
        petStage.addEventListener('click', (e) => {
            if (draggingItem) return;

            const stageRect = petStage.getBoundingClientRect();
            const clickX = e.clientX - stageRect.left;
            const clickY = e.clientY - stageRect.top;

            createPetIndicator(e.clientX, e.clientY);
            hopTo(clickX, stageRect.height - clickY);
            resetIdleTimer();
        });

        // ==========================================
        // Initialize
        // ==========================================
        function init() {
            updatePetDisplay();
            updateBadges();
            renderFoodGrid();
            renderHealGrid();
            renderPetGrid();
            resetIdleTimer();

            // Center pet
            const stageRect = petStage.getBoundingClientRect();
            setPetPosition(stageRect.width / 2, stageRect.height * 0.2);
        }

        // Wait for layout
        requestAnimationFrame(() => {
            requestAnimationFrame(init);
        });

        // Handle resize
        window.addEventListener('resize', () => {
            const stageRect = petStage.getBoundingClientRect();
            setPetPosition(stageRect.width / 2, stageRect.height * 0.2);
        });
    </script>
</body>
</html>
