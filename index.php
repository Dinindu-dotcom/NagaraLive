<!DOCTYPE html>
<html lang="en" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NagaraLive — Real-Time Neighborhood Community Pulse & Alerts</title>
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        slate: {
                            950: '#030712',
                            900: '#0f172a',
                            850: '#131e36',
                            800: '#1e293b'
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                        'radar': 'radar 2s cubic-bezier(0, 0, 0.2, 1) infinite',
                    },
                    keyframes: {
                        radar: {
                            '0%': { transform: 'scale(0.9)', opacity: '1' },
                            '100%': { transform: 'scale(2.2)', opacity: '0' },
                        }
                    }
                }
            }
        };
    </script>
    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        .gradient-brand {
            background: linear-gradient(135deg, #38bdf8 0%, #6366f1 50%, #a855f7 100%);
        }
        .text-gradient {
            background: linear-gradient(135deg, #38bdf8 0%, #818cf8 50%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(51, 65, 85, 0.65);
        }
        .glass-card-hover {
            transition: all 0.25s ease-in-out;
        }
        .glass-card-hover:hover {
            border-color: rgba(99, 102, 241, 0.45);
            transform: translateY(-2px);
            box-shadow: 0 12px 30px -10px rgba(99, 102, 241, 0.15);
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #030712;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col antialiased selection:bg-purple-600 selection:text-white">

    <!-- Decorative Top Atmosphere Glow -->
    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-72 bg-gradient-to-r from-sky-500/10 via-purple-500/10 to-indigo-500/10 blur-3xl pointer-events-none -z-10"></div>

    <!-- ==================================================================== -->
    <!-- NAVIGATION BAR                                                       -->
    <!-- ==================================================================== -->
    <header class="sticky top-0 z-40 bg-slate-950/85 backdrop-blur-xl border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-18 py-3.5 flex items-center justify-between">
            <!-- Brand & District Badge -->
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl gradient-brand flex items-center justify-center shadow-lg shadow-sky-500/20 text-white font-extrabold text-xl tracking-tight">
                    NL
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xl font-bold tracking-tight text-gradient">NagaraLive</span>
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span class="text-[11px] font-semibold text-emerald-400 uppercase tracking-widest hidden sm:inline">LIVE</span>
                    </div>
                    <p class="text-xs text-slate-400 flex items-center gap-1 font-medium">
                        <svg class="w-3.5 h-3.5 text-sky-400 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Gampaha District Pulse
                    </p>
                </div>
            </div>

            <!-- Right Controls: Mode Toggle & Quick Post Action -->
            <div class="flex items-center gap-2.5 sm:gap-4">
                <!-- Mode Toggle (Public Resident / Admin) -->
                <button id="modeToggleBtn" onclick="toggleAdminMode()" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all duration-200 bg-slate-900 border-slate-700 text-slate-300 hover:border-slate-500">
                    <span id="modeIcon" class="text-sky-400">👤</span>
                    <span id="modeLabel">Resident Mode</span>
                    <span id="modeBadge" class="bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded text-[10px] uppercase font-bold tracking-wider">Add Only</span>
                </button>

                <a href="#postSection" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white gradient-brand hover:opacity-95 shadow-md shadow-purple-600/20 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Post Alert
                </a>
            </div>
        </div>
    </header>

    <!-- Admin Banner (when admin is active) -->
    <div id="adminNoticeBar" class="hidden bg-gradient-to-r from-purple-950 via-slate-900 to-purple-950 border-b border-purple-800/60 px-4 py-2 text-center text-xs text-purple-300 font-medium">
        <span class="inline-flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-purple-400 animate-pulse"></span>
            <strong>Admin Moderation Mode Active:</strong> You have full rights to edit, mark resolved, or delete community posts.
            <button onclick="exitAdminMode()" class="ml-2 underline text-white hover:text-purple-200 font-bold">Exit Admin Mode</button>
        </span>
    </div>

    <!-- ==================================================================== -->
    <!-- MAIN CONTENT CONTAINER                                               -->
    <!-- ==================================================================== -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 flex-1 space-y-8 w-full">

        <!-- ================================================================ -->
        <!-- TOP CARD: GEMINI LIVE COMMUNITY BRIEF                            -->
        <!-- ================================================================ -->
        <section class="relative overflow-hidden rounded-2xl p-6 sm:p-7 glass-card border border-slate-800/80 shadow-2xl shadow-slate-950/50">
            <!-- Background accent gradient blob -->
            <div class="absolute -right-16 -top-16 w-64 h-64 bg-gradient-to-br from-purple-500/15 to-sky-500/10 rounded-full blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4 pb-4 border-b border-slate-800/70">
                <div class="flex items-center gap-3">
                    <div class="p-2.5 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-lg sm:text-xl font-bold tracking-tight text-white flex items-center gap-2">
                                Gemini Live Community Brief
                            </h2>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-900/60 text-purple-300 border border-purple-700/50">
                                gemini-3.8-flash
                            </span>
                        </div>
                        <p class="text-xs text-slate-400">Synthesizing active weather hazards, utility cuts, and community events in real time.</p>
                    </div>
                </div>

                <!-- Hazard Level Badge & Refresh Button -->
                <div class="flex items-center gap-3 self-start md:self-auto">
                    <!-- Hazard Badge Placeholder -->
                    <div id="briefHazardBadge" class="flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-800 text-slate-300 border border-slate-700">
                        <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                        <span>Evaluating...</span>
                    </div>

                    <button id="refreshBriefBtn" onclick="loadSituationBrief(true)" title="Refresh AI Brief" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-900 hover:bg-slate-850 text-slate-300 border border-slate-700 hover:border-slate-600 transition-all">
                        <svg id="refreshSpinner" class="w-3.5 h-3.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <span>Refresh Brief</span>
                    </button>
                </div>
            </div>

            <!-- Bullet Points Container -->
            <div class="mt-4 pt-1">
                <ul id="briefBulletsList" class="space-y-2.5 text-sm text-slate-200">
                    <li class="flex items-start gap-2.5 text-slate-400 text-xs animate-pulse">
                        <span class="text-purple-400 mt-0.5">✦</span>
                        <span>Loading live neighborhood intelligence...</span>
                    </li>
                </ul>

                <div class="mt-4 pt-3 flex flex-wrap items-center justify-between text-[11px] text-slate-400 border-t border-slate-800/40 gap-2">
                    <span id="briefGeneratedTime" class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Last synchronized: Just now
                    </span>
                    <span id="briefPostCountBadge" class="text-slate-400 font-medium">Monitoring 6 active reports</span>
                </div>
            </div>

            <!-- Quick Stats Bar -->
            <div class="mt-5 grid grid-cols-2 sm:grid-cols-4 gap-3 pt-4 border-t border-slate-800/60">
                <div class="px-3 py-2 rounded-xl bg-slate-900/60 border border-slate-800/80">
                    <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Active Alerts</span>
                    <p id="statActiveCount" class="text-lg font-bold text-sky-400">--</p>
                </div>
                <div class="px-3 py-2 rounded-xl bg-slate-900/60 border border-slate-800/80">
                    <span class="text-[10px] text-amber-400/90 uppercase tracking-wider font-semibold">Power & Water Cuts</span>
                    <p id="statPowerWater" class="text-lg font-bold text-amber-400">--</p>
                </div>
                <div class="px-3 py-2 rounded-xl bg-slate-900/60 border border-slate-800/80">
                    <span class="text-[10px] text-rose-400/90 uppercase tracking-wider font-semibold">Weather Emergencies</span>
                    <p id="statWeather" class="text-lg font-bold text-rose-400">--</p>
                </div>
                <div class="px-3 py-2 rounded-xl bg-slate-900/60 border border-slate-800/80">
                    <span class="text-[10px] text-purple-400/90 uppercase tracking-wider font-semibold">Community Events</span>
                    <p id="statEvents" class="text-lg font-bold text-purple-400">--</p>
                </div>
            </div>
        </section>

        <!-- ================================================================ -->
        <!-- QUICK-POST FORM                                                  -->
        <!-- ================================================================ -->
        <section id="postSection" class="glass-card rounded-2xl p-6 sm:p-7 border border-slate-800/90 shadow-xl">
            <div class="flex items-center justify-between pb-4 border-b border-slate-800/80">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-xl bg-sky-500/10 border border-sky-500/20 text-sky-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-white tracking-tight">Post Neighborhood Note or Alert</h3>
                        <p class="text-xs text-slate-400">Share eyewitness updates, utility cuts, or local community events in any language.</p>
                    </div>
                </div>
                <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-950/60 text-emerald-400 border border-emerald-800/50">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    Public Append-Only
                </span>
            </div>

            <form id="createPostForm" onsubmit="handleCreatePost(event)" class="mt-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Area Selector -->
                    <div>
                        <label for="postArea" class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Neighborhood / Area <span class="text-rose-400">*</span>
                        </label>
                        <select id="postArea" required class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-colors">
                            <option value="" disabled selected>Select Area...</option>
                            <option value="Gampaha Town">Gampaha Town</option>
                            <option value="Yakkala">Yakkala</option>
                            <option value="Miriswatta">Miriswatta</option>
                            <option value="Ganemulla">Ganemulla</option>
                            <option value="Ja-Ela">Ja-Ela</option>
                            <option value="Kiribathgoda">Kiribathgoda</option>
                            <option value="Kadawatha">Kadawatha</option>
                            <option value="Kelaniya">Kelaniya</option>
                            <option value="Nittambuwa">Nittambuwa</option>
                            <option value="Veyangoda">Veyangoda</option>
                            <option value="Minuwangoda">Minuwangoda</option>
                            <option value="Other Area">Other Area</option>
                        </select>
                    </div>

                    <!-- Category Selector -->
                    <div>
                        <label for="postCategory" class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Category <span class="text-rose-400">*</span>
                        </label>
                        <select id="postCategory" required onchange="handleCategoryChange()" class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-colors">
                            <option value="" disabled selected>Select Category...</option>
                            <option value="POWER_WATER">⚡ Power & Water Cutout</option>
                            <option value="WEATHER_EMERGENCY">🌧️ Weather Emergency / Flash Flood</option>
                            <option value="NOTICE">📢 Public Notice / Advisory</option>
                            <option value="EVENT">🎉 Community Event</option>
                        </select>
                    </div>

                    <!-- Conditional Target Group (Visible ONLY when EVENT is selected) -->
                    <div id="targetGroupWrapper" class="transition-all duration-300 opacity-50 pointer-events-none">
                        <label for="postTargetGroup" class="block text-xs font-semibold text-purple-300 mb-1.5 flex items-center justify-between">
                            <span>Event Target Age Group</span>
                            <span class="text-[10px] text-purple-400 bg-purple-950 px-1.5 py-0.5 rounded">Event Only</span>
                        </label>
                        <select id="postTargetGroup" disabled class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-colors">
                            <option value="ALL">👥 All Community Ages</option>
                            <option value="ELDERLY">👴 Elderly / Senior Citizens</option>
                            <option value="CHILDREN">👶 Children & Toddlers</option>
                            <option value="YOUTH">🚀 Youth & Young Adults</option>
                        </select>
                    </div>
                </div>

                <!-- Message Input -->
                <div>
                    <label for="postMessage" class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
                        <span>Report Message (Sinhala, Singlish, Tamil, or English) <span class="text-rose-400">*</span></span>
                        <span class="text-[11px] text-slate-400 font-normal">AI auto-translates to clear English</span>
                    </label>
                    <textarea id="postMessage" required rows="3" placeholder="Eg: Yakkala junction eke main transformer eka pipuruwa, Radawana road ekama current na... / Free dental clinic for kids at Ja-Ela Town Hall..." class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-colors"></textarea>
                </div>

                <!-- Footer & Submit -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 pt-2">
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <span class="text-purple-400 font-bold">✨ AI Pipeline:</span>
                        <span>Gemini 3.8 Flash auto-translates & flags direct eyewitness accounts.</span>
                    </div>

                    <button type="submit" id="submitPostBtn" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl font-semibold text-xs text-white gradient-brand hover:opacity-95 shadow-lg shadow-purple-600/25 transition-all">
                        <svg id="submitSpinner" class="hidden w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        <span id="submitBtnText">Publish to Neighborhood Board</span>
                    </button>
                </div>
            </form>
        </section>

        <!-- ================================================================ -->
        <!-- FILTER BAR & POSTS FEED                                          -->
        <!-- ================================================================ -->
        <section class="space-y-4">
            <!-- Filter Controls Container -->
            <div class="glass-card rounded-2xl p-4 sm:p-5 border border-slate-800/80 space-y-3.5">
                <!-- Primary Category Filter Tabs -->
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                        <button onclick="setCategoryFilter('ALL')" data-category="ALL" class="cat-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-sky-500 text-white transition-all shadow-md shadow-sky-500/20">
                            All Posts
                        </button>
                        <button onclick="setCategoryFilter('POWER_WATER')" data-category="POWER_WATER" class="cat-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-900 text-slate-300 hover:text-white border border-slate-700 hover:border-slate-600 transition-all">
                            ⚡ Power & Water
                        </button>
                        <button onclick="setCategoryFilter('WEATHER_EMERGENCY')" data-category="WEATHER_EMERGENCY" class="cat-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-900 text-slate-300 hover:text-white border border-slate-700 hover:border-slate-600 transition-all">
                            🌧️ Weather Emergencies
                        </button>
                        <button onclick="setCategoryFilter('NOTICE')" data-category="NOTICE" class="cat-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-900 text-slate-300 hover:text-white border border-slate-700 hover:border-slate-600 transition-all">
                            📢 Public Notices
                        </button>
                        <button onclick="setCategoryFilter('EVENT')" data-category="EVENT" class="cat-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-900 text-slate-300 hover:text-white border border-slate-700 hover:border-slate-600 transition-all">
                            🎉 Events
                        </button>
                    </div>

                    <!-- Search Box & Status Filter -->
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <div class="relative flex-1 sm:w-48">
                            <input type="text" id="searchInput" oninput="handleSearch()" placeholder="Search notes..." class="w-full bg-slate-900 border border-slate-700/80 rounded-xl pl-8 pr-3 py-1.5 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-sky-500">
                            <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <select id="statusFilter" onchange="loadPosts()" class="bg-slate-900 border border-slate-700/80 rounded-xl px-2.5 py-1.5 text-xs text-slate-200 focus:outline-none focus:border-sky-500">
                            <option value="ACTIVE" selected>Active Only</option>
                            <option value="ALL">All (Inc. Resolved)</option>
                            <option value="RESOLVED">Resolved Only</option>
                        </select>
                    </div>
                </div>

                <!-- Secondary Age-Group Filter Pills (Rendered dynamically when EVENT category is selected) -->
                <div id="eventAgePillsWrapper" class="hidden pt-3 border-t border-slate-800/60 flex items-center gap-2">
                    <span class="text-xs font-semibold text-purple-400 mr-1 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Filter by Age:
                    </span>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button onclick="setAgeGroupFilter('ALL')" data-age="ALL" class="age-filter-btn px-2.5 py-1 rounded-lg text-xs font-medium bg-purple-600 text-white transition-all">
                            All Ages
                        </button>
                        <button onclick="setAgeGroupFilter('ELDERLY')" data-age="ELDERLY" class="age-filter-btn px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-900 text-slate-300 hover:text-white border border-slate-700 transition-all">
                            👴 Elderly
                        </button>
                        <button onclick="setAgeGroupFilter('CHILDREN')" data-age="CHILDREN" class="age-filter-btn px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-900 text-slate-300 hover:text-white border border-slate-700 transition-all">
                            👶 Children
                        </button>
                        <button onclick="setAgeGroupFilter('YOUTH')" data-age="YOUTH" class="age-filter-btn px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-900 text-slate-300 hover:text-white border border-slate-700 transition-all">
                            🚀 Youth
                        </button>
                    </div>
                </div>
            </div>

            <!-- Posts Grid -->
            <div id="postsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                <!-- Dynamic cards populated by JavaScript -->
            </div>

            <!-- Empty State Placeholder -->
            <div id="emptyPostsState" class="hidden glass-card rounded-2xl p-12 text-center border border-slate-800">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-900 flex items-center justify-center text-slate-500 mb-4 text-2xl">
                    🔍
                </div>
                <h4 class="text-base font-bold text-white mb-1">No community reports match your filter</h4>
                <p class="text-xs text-slate-400 max-w-sm mx-auto mb-4">Try clearing your search query or selecting a different category filter tab.</p>
                <button onclick="resetFilters()" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 text-slate-200 hover:bg-slate-700 transition-colors">
                    Reset All Filters
                </button>
            </div>
        </section>
    </main>

    <!-- ==================================================================== -->
    <!-- FOOTER                                                               -->
    <!-- ==================================================================== -->
    <footer class="mt-auto border-t border-slate-800/80 bg-slate-950 py-6 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2">
                <span class="font-bold text-slate-300">NagaraLive</span>
                <span>•</span>
                <span>Production Neighborhood Intelligence Board</span>
                <span>•</span>
                <span class="text-sky-400">Gampaha District, Sri Lanka</span>
            </div>
            <div class="flex items-center gap-4">
                <span>PHP 8.2 + MySQL</span>
                <span>•</span>
                <span>Gemini 3.8 Flash</span>
                <span>•</span>
                <a href="https://github.com/Dinindu-dotcom/NagaraLive" target="_blank" class="hover:text-slate-300 transition-colors">MIT License</a>
            </div>
        </div>
    </footer>

    <!-- ==================================================================== -->
    <!-- ADMIN PASSCODE MODAL                                                 -->
    <!-- ==================================================================== -->
    <div id="adminPassModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="glass-card rounded-2xl p-6 max-w-md w-full border border-purple-500/40 shadow-2xl relative">
            <button onclick="closeAdminModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div class="flex items-center gap-3 mb-4">
                <div class="p-2.5 rounded-xl bg-purple-500/20 text-purple-400 border border-purple-500/30">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Administrator Access</h3>
                    <p class="text-xs text-slate-400">Unlock post editing, status resolving, and deletion rights.</p>
                </div>
            </div>

            <form onsubmit="submitAdminPasscode(event)" class="space-y-4">
                <div>
                    <label for="adminPasscode" class="block text-xs font-semibold text-slate-300 mb-1">
                        Admin Passcode (Default: <code class="text-purple-300">admin123</code>)
                    </label>
                    <input type="password" id="adminPasscodeInput" required placeholder="Enter passcode..." class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:outline-none focus:border-purple-500">
                    <p id="adminPassError" class="text-xs text-rose-400 mt-1.5 hidden"></p>
                </div>

                <div class="flex items-center justify-end gap-2.5">
                    <button type="button" onclick="closeAdminModal()" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-purple-600 hover:bg-purple-500 shadow-md shadow-purple-600/30">
                        Authenticate
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================================================================== -->
    <!-- ADMIN EDIT POST MODAL                                                -->
    <!-- ==================================================================== -->
    <div id="editPostModal" class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="glass-card rounded-2xl p-6 max-w-lg w-full border border-sky-500/40 shadow-2xl relative">
            <button onclick="closeEditModal()" class="absolute top-4 right-4 text-slate-400 hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <div class="flex items-center gap-3 mb-4">
                <div class="p-2.5 rounded-xl bg-sky-500/20 text-sky-400 border border-sky-500/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Edit Post (Admin)</h3>
                    <p class="text-xs text-slate-400" id="editModalSub">Update neighborhood alert contents</p>
                </div>
            </div>

            <form onsubmit="submitEditPost(event)" class="space-y-3.5">
                <input type="hidden" id="editPostId">

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Area</label>
                        <input type="text" id="editArea" required class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Category</label>
                        <select id="editCategory" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                            <option value="POWER_WATER">⚡ Power & Water</option>
                            <option value="WEATHER_EMERGENCY">🌧️ Weather Emergency</option>
                            <option value="NOTICE">📢 Public Notice</option>
                            <option value="EVENT">🎉 Community Event</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Target Group</label>
                        <select id="editTargetGroup" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                            <option value="ALL">👥 All Ages</option>
                            <option value="ELDERLY">👴 Elderly</option>
                            <option value="CHILDREN">👶 Children</option>
                            <option value="YOUTH">🚀 Youth</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1">Status</label>
                        <select id="editStatus" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white">
                            <option value="ACTIVE">Active</option>
                            <option value="RESOLVED">Resolved</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Translated Message (English)</label>
                    <textarea id="editTranslated" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Original Local Message</label>
                    <textarea id="editRaw" rows="2" class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white"></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-slate-300 border border-slate-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl text-xs font-semibold text-white bg-sky-600 hover:bg-sky-500">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================================================================== -->
    <!-- TOAST NOTIFICATION CONTAINER                                         -->
    <!-- ==================================================================== -->
    <div id="toastContainer" class="fixed bottom-5 right-5 z-50 flex flex-col gap-2 pointer-events-none"></div>

    <!-- ==================================================================== -->
    <!-- JAVASCRIPT APPLICATION LOGIC                                         -->
    <!-- ==================================================================== -->
    <script>
        // State Management
        const state = {
            isAdmin: false,
            adminPasscode: localStorage.getItem('nagaralive_admin_pass') || '',
            categoryFilter: 'ALL',
            ageGroupFilter: 'ALL',
            statusFilter: 'ACTIVE',
            searchQuery: '',
            posts: []
        };

        // Initialize App
        document.addEventListener('DOMContentLoaded', () => {
            // Check if saved admin passcode is valid
            if (state.adminPasscode) {
                verifyAdminPasscodeSilently(state.adminPasscode);
            }
            loadSituationBrief();
            loadPosts();
            loadStats();
        });

        // ----------------------------------------------------------------
        // Toast Notifications
        // ----------------------------------------------------------------
        function showToast(message, type = 'info') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = `pointer-events-auto flex items-center gap-2.5 px-4 py-3 rounded-xl text-xs font-semibold shadow-xl border transition-all duration-300 transform translate-y-2 opacity-0 ${
                type === 'success' ? 'bg-emerald-950/95 text-emerald-200 border-emerald-700/60' :
                type === 'error'   ? 'bg-rose-950/95 text-rose-200 border-rose-700/60' :
                'bg-slate-900/95 text-sky-200 border-sky-700/60'
            }`;

            const icon = type === 'success' ? '✓' : (type === 'error' ? '⚠' : 'ℹ');
            toast.innerHTML = `<span class="text-base">${icon}</span><span>${message}</span>`;
            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            });

            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => toast.remove(), 300);
            }, 3500);
        }

        // ----------------------------------------------------------------
        // Admin Mode Handling
        // ----------------------------------------------------------------
        function toggleAdminMode() {
            if (state.isAdmin) {
                exitAdminMode();
            } else {
                openAdminModal();
            }
        }

        function openAdminModal() {
            document.getElementById('adminPassModal').classList.remove('hidden');
            document.getElementById('adminPasscodeInput').focus();
        }

        function closeAdminModal() {
            document.getElementById('adminPassModal').classList.add('hidden');
            document.getElementById('adminPassError').classList.add('hidden');
            document.getElementById('adminPasscodeInput').value = '';
        }

        async function submitAdminPasscode(e) {
            e.preventDefault();
            const pass = document.getElementById('adminPasscodeInput').value.trim();
            const errorEl = document.getElementById('adminPassError');

            try {
                const res = await fetch('api.php?action=verify_admin', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ admin_passcode: pass })
                });
                const data = await res.json();

                if (data.authorized) {
                    state.isAdmin = true;
                    state.adminPasscode = pass;
                    localStorage.setItem('nagaralive_admin_pass', pass);
                    updateAdminUI();
                    closeAdminModal();
                    showToast('Admin moderation mode activated', 'success');
                    renderPosts(); // Re-render to show admin buttons
                } else {
                    errorEl.textContent = 'Invalid administrator passcode. Default is admin123';
                    errorEl.classList.remove('hidden');
                }
            } catch (err) {
                errorEl.textContent = 'Failed to verify passcode with backend.';
                errorEl.classList.remove('hidden');
            }
        }

        async function verifyAdminPasscodeSilently(pass) {
            try {
                const res = await fetch('api.php?action=verify_admin', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ admin_passcode: pass })
                });
                const data = await res.json();
                if (data.authorized) {
                    state.isAdmin = true;
                    updateAdminUI();
                    renderPosts();
                } else {
                    localStorage.removeItem('nagaralive_admin_pass');
                }
            } catch (e) {
                // Ignore silent verification failure
            }
        }

        function exitAdminMode() {
            state.isAdmin = false;
            state.adminPasscode = '';
            localStorage.removeItem('nagaralive_admin_pass');
            updateAdminUI();
            showToast('Returned to Public Resident mode', 'info');
            renderPosts();
        }

        function updateAdminUI() {
            const btn = document.getElementById('modeToggleBtn');
            const icon = document.getElementById('modeIcon');
            const label = document.getElementById('modeLabel');
            const badge = document.getElementById('modeBadge');
            const banner = document.getElementById('adminNoticeBar');

            if (state.isAdmin) {
                icon.textContent = '🛡️';
                label.textContent = 'Admin Mode';
                badge.textContent = 'Full Access';
                badge.className = 'bg-purple-900 text-purple-200 px-1.5 py-0.5 rounded text-[10px] uppercase font-bold tracking-wider';
                btn.className = 'flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all duration-200 bg-purple-950/80 border-purple-600 text-purple-200 shadow-lg shadow-purple-900/30';
                banner.classList.remove('hidden');
            } else {
                icon.textContent = '👤';
                label.textContent = 'Resident Mode';
                badge.textContent = 'Add Only';
                badge.className = 'bg-slate-800 text-slate-400 px-1.5 py-0.5 rounded text-[10px] uppercase font-bold tracking-wider';
                btn.className = 'flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all duration-200 bg-slate-900 border-slate-700 text-slate-300 hover:border-slate-500';
                banner.classList.add('hidden');
            }
        }

        // ----------------------------------------------------------------
        // Form & Category Logic
        // ----------------------------------------------------------------
        function handleCategoryChange() {
            const cat = document.getElementById('postCategory').value;
            const targetWrapper = document.getElementById('targetGroupWrapper');
            const targetSelect = document.getElementById('postTargetGroup');

            if (cat === 'EVENT') {
                targetWrapper.classList.remove('opacity-50', 'pointer-events-none');
                targetSelect.removeAttribute('disabled');
            } else {
                targetWrapper.classList.add('opacity-50', 'pointer-events-none');
                targetSelect.setAttribute('disabled', 'disabled');
                targetSelect.value = 'ALL';
            }
        }

        async function handleCreatePost(e) {
            e.preventDefault();
            const area = document.getElementById('postArea').value;
            const category = document.getElementById('postCategory').value;
            const targetGroup = document.getElementById('postTargetGroup').value;
            const rawMessage = document.getElementById('postMessage').value.trim();

            if (!area || !category || !rawMessage) {
                showToast('Please fill in all required fields.', 'error');
                return;
            }

            const submitBtn = document.getElementById('submitPostBtn');
            const spinner = document.getElementById('submitSpinner');
            const btnText = document.getElementById('submitBtnText');

            submitBtn.disabled = true;
            spinner.classList.remove('hidden');
            btnText.textContent = 'Analyzing & Translating with Gemini...';

            try {
                const res = await fetch('api.php?action=create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        area,
                        category,
                        target_group: category === 'EVENT' ? targetGroup : 'ALL',
                        raw_message: rawMessage
                    })
                });

                const data = await res.json();
                if (data.success) {
                    showToast('Report verified & published to board!', 'success');
                    document.getElementById('createPostForm').reset();
                    handleCategoryChange();
                    loadPosts();
                    loadStats();
                    loadSituationBrief(); // Refresh brief with new post
                } else {
                    showToast(data.error || 'Failed to submit post.', 'error');
                }
            } catch (err) {
                showToast('Network error while posting report.', 'error');
            } finally {
                submitBtn.disabled = false;
                spinner.classList.add('hidden');
                btnText.textContent = 'Publish to Neighborhood Board';
            }
        }

        // ----------------------------------------------------------------
        // Confirmation (+1 Action)
        // ----------------------------------------------------------------
        async function confirmPost(id, btnElement) {
            btnElement.disabled = true;
            try {
                const res = await fetch('api.php?action=confirm', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                });

                const data = await res.json();
                if (data.success) {
                    showToast(`Alert #${id} confirmed! (+1)`, 'success');
                    // Find post in state and update
                    const p = state.posts.find(item => item.id === id);
                    if (p) {
                        p.confirmations = data.confirmations;
                        renderPosts();
                    }
                } else {
                    showToast(data.error || 'Failed to confirm.', 'error');
                    btnElement.disabled = false;
                }
            } catch (err) {
                showToast('Network error confirming post.', 'error');
                btnElement.disabled = false;
            }
        }

        // ----------------------------------------------------------------
        // Gemini Live Situation Brief
        // ----------------------------------------------------------------
        async function loadSituationBrief(isManualRefresh = false) {
            const spinner = document.getElementById('refreshSpinner');
            const refreshBtn = document.getElementById('refreshBriefBtn');
            if (isManualRefresh) {
                spinner.classList.add('animate-spin');
                refreshBtn.disabled = true;
            }

            try {
                const res = await fetch('api.php?action=brief');
                const data = await res.json();

                if (data.success && data.brief) {
                    renderBrief(data.brief, data.active_count, data.generated_at);
                    if (isManualRefresh) showToast('AI Situation Brief refreshed', 'success');
                }
            } catch (err) {
                console.error('Brief load error', err);
            } finally {
                if (isManualRefresh) {
                    spinner.classList.remove('animate-spin');
                    refreshBtn.disabled = false;
                }
            }
        }

        function renderBrief(brief, activeCount, generatedAt) {
            const badgeContainer = document.getElementById('briefHazardBadge');
            const bulletsList = document.getElementById('briefBulletsList');
            const timeEl = document.getElementById('briefGeneratedTime');
            const countEl = document.getElementById('briefPostCountBadge');

            const level = (brief.hazard_level || 'Normal').toUpperCase();

            // Hazard Level Styling
            let badgeHtml = '';
            if (level === 'CRITICAL') {
                badgeHtml = `
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-rose-500"></span>
                    </span>
                    <span class="text-rose-400 font-bold uppercase tracking-wider">🔴 Critical Hazard Alert</span>
                `;
                badgeContainer.className = 'flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-950/80 text-rose-300 border border-rose-700/60 shadow-lg shadow-rose-950/40';
            } else if (level === 'CAUTION') {
                badgeHtml = `
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    </span>
                    <span class="text-amber-400 font-bold uppercase tracking-wider">🟡 Caution Advised</span>
                `;
                badgeContainer.className = 'flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-950/80 text-amber-300 border border-amber-700/60 shadow-lg shadow-amber-950/40';
            } else {
                badgeHtml = `
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    <span class="text-emerald-400 font-bold uppercase tracking-wider">🟢 Normal Conditions</span>
                `;
                badgeContainer.className = 'flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-950/80 text-emerald-300 border border-emerald-700/60';
            }
            badgeContainer.innerHTML = badgeHtml;

            // Render Bullets
            if (brief.bullets && brief.bullets.length > 0) {
                bulletsList.innerHTML = brief.bullets.map(bullet => `
                    <li class="flex items-start gap-2.5 leading-relaxed">
                        <span class="text-purple-400 font-bold mt-0.5 select-none">✦</span>
                        <span>${escapeHtml(bullet)}</span>
                    </li>
                `).join('');
            }

            if (generatedAt) {
                timeEl.innerHTML = `
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Synchronized: ${generatedAt}
                `;
            }

            if (countEl) {
                countEl.textContent = `Monitoring ${activeCount} active neighborhood reports`;
            }
        }

        // ----------------------------------------------------------------
        // Stats Loading
        // ----------------------------------------------------------------
        async function loadStats() {
            try {
                const res = await fetch('api.php?action=stats');
                const data = await res.json();
                if (data.success && data.stats) {
                    document.getElementById('statActiveCount').textContent = data.stats.total_active;
                    document.getElementById('statPowerWater').textContent = data.stats.power_water;
                    document.getElementById('statWeather').textContent = data.stats.weather_emergency;
                    document.getElementById('statEvents').textContent = data.stats.events;
                }
            } catch (err) {
                console.error('Stats error', err);
            }
        }

        // ----------------------------------------------------------------
        // Posts Feed Loading & Rendering
        // ----------------------------------------------------------------
        async function loadPosts() {
            const grid = document.getElementById('postsGrid');
            grid.innerHTML = `
                <div class="col-span-full py-12 text-center text-slate-500 text-sm">
                    <div class="w-6 h-6 border-2 border-purple-500 border-t-transparent rounded-full animate-spin mx-auto mb-2"></div>
                    Loading neighborhood pulses...
                </div>
            `;

            const status = document.getElementById('statusFilter').value;
            state.statusFilter = status;

            let url = `api.php?action=list&status=${status}`;
            if (state.categoryFilter !== 'ALL') {
                url += `&category=${state.categoryFilter}`;
            }
            if (state.categoryFilter === 'EVENT' && state.ageGroupFilter !== 'ALL') {
                url += `&target_group=${state.ageGroupFilter}`;
            }
            if (state.searchQuery) {
                url += `&search=${encodeURIComponent(state.searchQuery)}`;
            }

            try {
                const res = await fetch(url);
                const data = await res.json();
                if (data.success) {
                    state.posts = data.data || [];
                    renderPosts();
                }
            } catch (err) {
                grid.innerHTML = `
                    <div class="col-span-full py-12 text-center text-rose-400 text-sm">
                        Failed to load reports. Please ensure MySQL database is initialized.
                    </div>
                `;
            }
        }

        function renderPosts() {
            const grid = document.getElementById('postsGrid');
            const empty = document.getElementById('emptyPostsState');

            if (state.posts.length === 0) {
                grid.innerHTML = '';
                empty.classList.remove('hidden');
                return;
            }

            empty.classList.add('hidden');

            grid.innerHTML = state.posts.map(post => {
                const isVerified = (post.confirmations >= 3);
                const isEvent = (post.category === 'EVENT');
                const isResolved = (post.status === 'RESOLVED');

                // Category configuration
                const catMeta = {
                    'POWER_WATER': {
                        name: 'Power & Water',
                        badge: 'bg-amber-950/80 text-amber-300 border-amber-700/60',
                        icon: '⚡'
                    },
                    'WEATHER_EMERGENCY': {
                        name: 'Weather Emergency',
                        badge: 'bg-rose-950/80 text-rose-300 border-rose-700/60',
                        icon: '🌧️'
                    },
                    'NOTICE': {
                        name: 'Public Notice',
                        badge: 'bg-sky-950/80 text-sky-300 border-sky-700/60',
                        icon: '📢'
                    },
                    'EVENT': {
                        name: 'Community Event',
                        badge: 'bg-purple-950/80 text-purple-300 border-purple-700/60',
                        icon: '🎉'
                    }
                }[post.category] || { name: post.category, badge: 'bg-slate-800 text-slate-300', icon: '📌' };

                // Target Group styling for Events
                const targetMeta = {
                    'ELDERLY': { label: 'Elderly Focus', badge: 'bg-amber-900/50 text-amber-300 border-amber-700/50', icon: '👴' },
                    'CHILDREN': { label: 'Children & Kids', badge: 'bg-pink-900/50 text-pink-300 border-pink-700/50', icon: '👶' },
                    'YOUTH': { label: 'Youth & Young Adults', badge: 'bg-indigo-900/50 text-indigo-300 border-indigo-700/50', icon: '🚀' },
                    'ALL': { label: 'All Ages', badge: 'bg-slate-800 text-slate-300 border-slate-700', icon: '👥' }
                }[post.target_group] || { label: post.target_group, badge: 'bg-slate-800 text-slate-300 border-slate-700', icon: '👥' };

                const primaryMsg = post.translated_message || post.raw_message;
                const hasTranslationDiff = (post.translated_message && post.translated_message.trim() !== post.raw_message.trim());

                return `
                    <div class="glass-card glass-card-hover rounded-2xl p-5 flex flex-col justify-between border ${isResolved ? 'opacity-60 border-slate-800 bg-slate-950/60' : 'border-slate-800/80'}">
                        <div>
                            <!-- Card Header: Badges & Time -->
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <!-- Category Badge -->
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold border ${catMeta.badge}">
                                        <span>${catMeta.icon}</span>
                                        <span>${catMeta.name}</span>
                                    </span>

                                    <!-- Age Group Badge (for Events) -->
                                    ${isEvent ? `
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-semibold border ${targetMeta.badge}">
                                            <span>${targetMeta.icon}</span>
                                            <span>${targetMeta.label}</span>
                                        </span>
                                    ` : ''}

                                    <!-- Status Badge -->
                                    ${isResolved ? `
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-800 text-slate-400 uppercase tracking-wider">
                                            Resolved
                                        </span>
                                    ` : ''}
                                </div>

                                <!-- Area Pill -->
                                <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-400 bg-slate-900/80 border border-slate-800 px-2 py-0.5 rounded-md shrink-0">
                                    <svg class="w-3 h-3 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    ${escapeHtml(post.area)}
                                </span>
                            </div>

                            <!-- Post Content: Translated primary & original italic secondary -->
                            <div class="space-y-2 mt-2">
                                <p class="text-sm font-semibold text-slate-100 leading-snug">
                                    ${escapeHtml(primaryMsg)}
                                </p>

                                ${hasTranslationDiff ? `
                                    <div class="bg-slate-950/60 rounded-xl p-2.5 border border-slate-800/60">
                                        <div class="flex items-center gap-1 text-[10px] text-purple-400 font-semibold mb-0.5">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"></path></svg>
                                            Original Citizen Report:
                                        </div>
                                        <p class="text-xs text-slate-400 italic">
                                            "${escapeHtml(post.raw_message)}"
                                        </p>
                                    </div>
                                ` : ''}
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="mt-4 pt-3 border-t border-slate-800/80 flex flex-col gap-2.5">
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <!-- Eyewitness indicator -->
                                <span class="flex items-center gap-1 ${post.is_eyewitness ? 'text-sky-400' : 'text-slate-400'}">
                                    <span>${post.is_eyewitness ? '👁️' : '📢'}</span>
                                    <span>${post.is_eyewitness ? 'Eyewitness Report' : 'Community Bulletin'}</span>
                                </span>

                                <span>${formatTimeAgo(post.created_at)}</span>
                            </div>

                            <!-- Action Bar: Confirm Button + Community Verified + Admin Controls -->
                            <div class="flex items-center justify-between gap-2 pt-1">
                                <div class="flex items-center gap-2">
                                    <!-- +1 Confirm Button (Public) -->
                                    <button onclick="confirmPost(${post.id}, this)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-900 hover:bg-slate-800 text-slate-200 border border-slate-700 hover:border-slate-500 transition-all active:scale-95">
                                        <span class="text-sky-400 font-bold">+1</span>
                                        <span>Confirm</span>
                                        <span class="bg-slate-800 px-1.5 py-0.2 rounded text-[10px] text-slate-400 font-mono">${post.confirmations}</span>
                                    </button>

                                    <!-- Community Verified Badge (confirmations >= 3) -->
                                    ${isVerified ? `
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-xl text-[10px] font-bold bg-emerald-950/80 text-emerald-300 border border-emerald-600/60 shadow-md shadow-emerald-950/50" title="3+ residents confirmed this report">
                                            <svg class="w-3.5 h-3.5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                            <span>Community Verified</span>
                                        </span>
                                    ` : ''}
                                </div>

                                <!-- Admin Controls (Visible ONLY in Admin Mode) -->
                                ${state.isAdmin ? `
                                    <div class="flex items-center gap-1">
                                        <button onclick="openEditModal(${post.id})" title="Edit Post (Admin)" class="p-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 text-sky-400 border border-slate-700 hover:border-sky-500 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button onclick="toggleResolvePost(${post.id}, '${post.status}')" title="${isResolved ? 'Reactivate Post' : 'Mark Resolved'}" class="p-1.5 rounded-lg bg-slate-900 hover:bg-slate-800 ${isResolved ? 'text-amber-400' : 'text-emerald-400'} border border-slate-700 hover:border-emerald-500 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        </button>
                                        <button onclick="deletePost(${post.id})" title="Delete Post (Admin)" class="p-1.5 rounded-lg bg-slate-900 hover:bg-rose-950 text-rose-400 border border-slate-700 hover:border-rose-600 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }

        // ----------------------------------------------------------------
        // Filter Handlers
        // ----------------------------------------------------------------
        function setCategoryFilter(cat) {
            state.categoryFilter = cat;
            state.ageGroupFilter = 'ALL'; // Reset age filter

            // Update button styles
            document.querySelectorAll('.cat-filter-btn').forEach(btn => {
                if (btn.dataset.category === cat) {
                    btn.className = 'cat-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-sky-500 text-white transition-all shadow-md shadow-sky-500/20';
                } else {
                    btn.className = 'cat-filter-btn px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-slate-900 text-slate-300 hover:text-white border border-slate-700 hover:border-slate-600 transition-all';
                }
            });

            // Toggle secondary age filter pills visibility (only for EVENT)
            const ageWrapper = document.getElementById('eventAgePillsWrapper');
            if (cat === 'EVENT') {
                ageWrapper.classList.remove('hidden');
                updateAgePillStyles();
            } else {
                ageWrapper.classList.add('hidden');
            }

            loadPosts();
        }

        function setAgeGroupFilter(age) {
            state.ageGroupFilter = age;
            updateAgePillStyles();
            loadPosts();
        }

        function updateAgePillStyles() {
            document.querySelectorAll('.age-filter-btn').forEach(btn => {
                if (btn.dataset.age === state.ageGroupFilter) {
                    btn.className = 'age-filter-btn px-2.5 py-1 rounded-lg text-xs font-medium bg-purple-600 text-white transition-all shadow-md shadow-purple-600/30';
                } else {
                    btn.className = 'age-filter-btn px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-900 text-slate-300 hover:text-white border border-slate-700 transition-all';
                }
            });
        }

        let searchDebounce;
        function handleSearch() {
            clearTimeout(searchDebounce);
            searchDebounce = setTimeout(() => {
                state.searchQuery = document.getElementById('searchInput').value.trim();
                loadPosts();
            }, 300);
        }

        function resetFilters() {
            document.getElementById('searchInput').value = '';
            document.getElementById('statusFilter').value = 'ACTIVE';
            state.searchQuery = '';
            setCategoryFilter('ALL');
        }

        // ----------------------------------------------------------------
        // Admin Actions (Edit, Resolve, Delete)
        // ----------------------------------------------------------------
        function openEditModal(id) {
            const post = state.posts.find(p => p.id === id);
            if (!post) return;

            document.getElementById('editPostId').value = post.id;
            document.getElementById('editArea').value = post.area;
            document.getElementById('editCategory').value = post.category;
            document.getElementById('editTargetGroup').value = post.target_group;
            document.getElementById('editStatus').value = post.status;
            document.getElementById('editTranslated').value = post.translated_message || '';
            document.getElementById('editRaw').value = post.raw_message || '';
            document.getElementById('editModalSub').textContent = `Editing Alert #${post.id} in ${post.area}`;

            document.getElementById('editPostModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editPostModal').classList.add('hidden');
        }

        async function submitEditPost(e) {
            e.preventDefault();
            const id = parseInt(document.getElementById('editPostId').value);
            const payload = {
                id,
                area: document.getElementById('editArea').value.trim(),
                category: document.getElementById('editCategory').value,
                target_group: document.getElementById('editTargetGroup').value,
                status: document.getElementById('editStatus').value,
                translated_message: document.getElementById('editTranslated').value.trim(),
                raw_message: document.getElementById('editRaw').value.trim(),
                admin_passcode: state.adminPasscode
            };

            try {
                const res = await fetch('api.php?action=update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Admin-Passcode': state.adminPasscode
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.success) {
                    showToast(`Post #${id} updated successfully.`, 'success');
                    closeEditModal();
                    loadPosts();
                    loadSituationBrief();
                } else {
                    showToast(data.error || 'Failed to update post.', 'error');
                }
            } catch (err) {
                showToast('Network error updating post.', 'error');
            }
        }

        async function toggleResolvePost(id, currentStatus) {
            const newStatus = (currentStatus === 'ACTIVE') ? 'RESOLVED' : 'ACTIVE';
            try {
                const res = await fetch('api.php?action=resolve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Admin-Passcode': state.adminPasscode
                    },
                    body: JSON.stringify({
                        id,
                        status: newStatus,
                        admin_passcode: state.adminPasscode
                    })
                });
                const data = await res.json();
                if (data.success) {
                    showToast(`Post #${id} marked as ${newStatus}`, 'success');
                    loadPosts();
                    loadStats();
                    loadSituationBrief();
                } else {
                    showToast(data.error || 'Failed to update status.', 'error');
                }
            } catch (err) {
                showToast('Network error resolving post.', 'error');
            }
        }

        async function deletePost(id) {
            if (!confirm(`Are you sure you want to permanently delete Post #${id}? This cannot be undone.`)) {
                return;
            }

            try {
                const res = await fetch('api.php?action=delete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Admin-Passcode': state.adminPasscode
                    },
                    body: JSON.stringify({
                        id,
                        admin_passcode: state.adminPasscode
                    })
                });
                const data = await res.json();
                if (data.success) {
                    showToast(`Post #${id} permanently deleted`, 'success');
                    loadPosts();
                    loadStats();
                    loadSituationBrief();
                } else {
                    showToast(data.error || 'Failed to delete post.', 'error');
                }
            } catch (err) {
                showToast('Network error deleting post.', 'error');
            }
        }

        // ----------------------------------------------------------------
        // Utilities
        // ----------------------------------------------------------------
        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function formatTimeAgo(dateString) {
            if (!dateString) return '';
            const date = new Date(dateString.replace(' ', 'T'));
            const now = new Date();
            const diffSec = Math.floor((now - date) / 1000);

            if (diffSec < 60) return 'Just now';
            const diffMin = Math.floor(diffSec / 60);
            if (diffMin < 60) return `${diffMin}m ago`;
            const diffHour = Math.floor(diffMin / 60);
            if (diffHour < 24) return `${diffHour}h ago`;
            const diffDay = Math.floor(diffHour / 24);
            return `${diffDay}d ago`;
        }
    </script>
</body>
</html>
