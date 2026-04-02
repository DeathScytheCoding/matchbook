<?php
$games = [];
$maps = [];
$characters = [];
$initialFeedback = "Select an item from the Add menu to hook this homepage into your next modal or form flow.";
$dbCandidates = [
    "/app/data/matchbook.sqlite",
    dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "matchbook.sqlite",
];
$dbPath = null;

foreach ($dbCandidates as $candidate) {
    if (is_file($candidate)) {
        $dbPath = $candidate;
        break;
    }
}

if ($dbPath !== null) {
    try {
        $pdo = new PDO("sqlite:" . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $games = $pdo->query("SELECT game_id, game_name, score_type FROM gamelist ORDER BY game_name COLLATE NOCASE")->fetchAll(PDO::FETCH_ASSOC);
        $maps = $pdo->query("SELECT map_id, game_id, map_name FROM maps ORDER BY map_name COLLATE NOCASE")->fetchAll(PDO::FETCH_ASSOC);
        $characters = $pdo->query("SELECT char_id, game_id, char_name FROM characters ORDER BY char_name COLLATE NOCASE")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $exception) {
        $games = [];
        $maps = [];
        $characters = [];
    }
}

if (empty($games)) {
    $initialFeedback = "Add your first game to unlock map, character, and match selections tied to your database.";
}

$gameData = [];
foreach ($games as $game) {
    $gameData[] = [
        "id" => (int) $game["game_id"],
        "name" => $game["game_name"],
        "scoreType" => (int) $game["score_type"],
    ];
}

$mapData = [];
foreach ($maps as $map) {
    $mapData[] = [
        "id" => (int) $map["map_id"],
        "gameId" => (int) $map["game_id"],
        "name" => $map["map_name"],
    ];
}

$characterData = [];
foreach ($characters as $character) {
    $characterData[] = [
        "id" => (int) $character["char_id"],
        "gameId" => (int) $character["game_id"],
        "name" => $character["char_name"],
    ];
}

function h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        (function () {
            var theme = "light";

            try {
                var storedTheme = localStorage.getItem("matchbook-theme");
                if (storedTheme === "light" || storedTheme === "dark") {
                    theme = storedTheme;
                } else if (window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches) {
                    theme = "dark";
                }
            } catch (error) {
                theme = "light";
            }

            document.documentElement.setAttribute("data-theme", theme);
        }());
    </script>
    <title>Game Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/home.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-shell">
        <div class="app-frame">
            <header class="hero-header p-4 p-lg-5 border-bottom border-opacity-10">
                <div class="row g-4 align-items-start">
                    <div class="col-12 col-xl-8">
                        <div class="hero-copy">
                            <div class="eyebrow">
                                <img class="eyebrow-logo" src="imgs/MatchBook%20Logo%20Only.png" alt="MatchBook">
                                <span class="eyebrow-title">MatchBook</span>
                            </div>
							<br/>
							<br/>
                            <h2>Your own local match tracker.</h2>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="d-flex flex-column align-items-stretch align-items-xl-end gap-3">
                            <div class="header-actions">
                                <button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false" aria-label="Switch to dark mode">
                                    <i class="bi bi-moon-stars-fill theme-toggle-icon" aria-hidden="true"></i>
                                    <span class="theme-toggle-label">Dark mode</span>
                                </button>
                                <div class="dropdown">
                                    <button class="btn btn-accent btn-lg dropdown-toggle header-add-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        Add
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><button class="dropdown-item" type="button" data-add-type="Game" data-bs-toggle="modal" data-bs-target="#addGameModal"><i class="bi bi-joystick"></i>Add Game</button></li>
                                        <li><button class="dropdown-item" type="button" data-add-type="Map" data-bs-toggle="modal" data-bs-target="#addMapModal"><i class="bi bi-map"></i>Add Map</button></li>
                                        <li><button class="dropdown-item" type="button" data-add-type="Character" data-bs-toggle="modal" data-bs-target="#addCharacterModal"><i class="bi bi-person-bounding-box"></i>Add Character</button></li>
                                        <li><button class="dropdown-item" type="button" data-add-type="Match" data-bs-toggle="modal" data-bs-target="#addMatchModal"><i class="bi bi-trophy"></i>Add Match</button></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="action-feedback" id="action-feedback">
                                <?= h($initialFeedback) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-4 p-lg-5">
                <section class="row g-4 align-items-stretch">
                    <div class="col-12 col-xl-8">
                        <div class="surface-card">
                            <div class="panel-title">
                                <div>
                                    <h2 class="h3">Performance Overview</h2>
                                    <p class="meta">Your main graph space for win rate, session trends, or match volume.</p>
                                </div>
                                <span class="mini-tag">
                                    <i class="bi bi-cpu"></i>
                                    Primary chart
                                </span>
                            </div>

                            <div class="chart-stage">
                                <div class="chart-overlay">
                                    <div class="spark-note">
                                        <i class="bi bi-info-circle"></i>
                                        Drop your first real graph component here later.
                                    </div>
                                    <div class="spark-line" aria-hidden="true">
                                        <svg viewBox="0 0 800 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M0 155C54 158 89 115 142 118C195 121 222 158 280 144C337 130 364 53 425 49C487 45 528 115 583 113C637 111 683 75 734 68C765 64 783 70 800 78" stroke="#1E7788" stroke-width="5" stroke-linecap="round"/>
                                            <path d="M0 174C56 175 88 143 144 146C200 149 226 180 281 172C338 164 366 99 424 96C484 92 525 142 580 142C634 141 679 117 732 110C763 106 783 111 800 118" stroke="#F3C969" stroke-width="5" stroke-linecap="round" opacity="0.95"/>
                                            <circle cx="142" cy="118" r="7" fill="#1E7788"/>
                                            <circle cx="425" cy="49" r="7" fill="#1E7788"/>
                                            <circle cx="734" cy="68" r="7" fill="#1E7788"/>
                                            <circle cx="144" cy="146" r="7" fill="#F3C969"/>
                                            <circle cx="424" cy="96" r="7" fill="#F3C969"/>
                                            <circle cx="732" cy="110" r="7" fill="#F3C969"/>
                                        </svg>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="mini-tag"><i class="bi bi-check2-circle"></i> Win rate</span>
                                        <span class="mini-tag"><i class="bi bi-clock-history"></i> Session pacing</span>
                                        <span class="mini-tag"><i class="bi bi-bar-chart-line"></i> Match volume</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-xl-4">
                        <div class="d-grid gap-4 h-100">
                            <div class="surface-card">
                                <div class="panel-title">
                                    <div>
                                        <h3 class="h4">Games by Focus</h3>
                                        <p class="meta">A compact card for game distribution or playtime.</p>
                                    </div>
                                    <i class="bi bi-pie-chart fs-4 text-secondary"></i>
                                </div>
                                <div class="small-chart" aria-hidden="true">
                                    <div class="bars">
                                        <div class="bar" style="height: 88%;"></div>
                                        <div class="bar soft" style="height: 56%;"></div>
                                        <div class="bar" style="height: 74%;"></div>
                                        <div class="bar soft" style="height: 46%;"></div>
                                        <div class="bar" style="height: 68%;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="surface-card">
                                <div class="panel-title">
                                    <div>
                                        <h3 class="h4">Map and Character Trends</h3>
                                        <p class="meta">Use this area for per-map stats, pick rates, or matchup notes.</p>
                                    </div>
                                    <i class="bi bi-diagram-3 fs-4 text-secondary"></i>
                                </div>
                                <div class="empty-state">
                                    <strong class="d-block mb-2">Future graph card</strong>
                                    <p>This side panel stays light on purpose so your core analytics can grow without the page feeling crowded.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="mt-4 mt-lg-5">
                    <div class="row g-4">
                        <div class="col-md-6 col-xl-4">
                            <div class="surface-card">
                                <div class="panel-title">
                                    <div>
                                        <h3 class="h4">Recent Sessions</h3>
                                        <p class="meta">This can become your match feed or latest uploads panel.</p>
                                    </div>
                                    <span class="mini-tag">Matches</span>
                                </div>
                                <div class="list-board">
                                    <div class="list-item">
                                        <div>
                                            <strong>Session card placeholder</strong>
                                            <span>Drop recent results here</span>
                                        </div>
                                        <span class="badge text-bg-light">Empty</span>
                                    </div>
                                    <div class="list-item">
                                        <div>
                                            <strong>Session card placeholder</strong>
                                            <span>Use for streaks, notes, or scorelines</span>
                                        </div>
                                        <span class="badge text-bg-light">Empty</span>
                                    </div>
                                    <div class="list-item">
                                        <div>
                                            <strong>Session card placeholder</strong>
                                            <span>Keep quick insights visible without filling the page</span>
                                        </div>
                                        <span class="badge text-bg-light">Empty</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-xl-4">
                            <div class="surface-card">
                                <div class="panel-title">
                                    <div>
                                        <h3 class="h4">Map Heat</h3>
                                        <p class="meta">A clean slot for map performance, favorite zones, or rotation stats.</p>
                                    </div>
                                    <span class="mini-tag">Maps</span>
                                </div>
                                <div class="small-chart" aria-hidden="true">
                                    <div class="bars">
                                        <div class="bar soft" style="height: 52%;"></div>
                                        <div class="bar" style="height: 72%;"></div>
                                        <div class="bar" style="height: 90%;"></div>
                                        <div class="bar soft" style="height: 64%;"></div>
                                        <div class="bar" style="height: 78%;"></div>
                                        <div class="bar soft" style="height: 50%;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="surface-card">
                                <div class="panel-title">
                                    <div>
                                        <h3 class="h4">Character Notes</h3>
                                        <p class="meta">Keep a space open for usage rates, mains, or matchup watchlists.</p>
                                    </div>
                                    <span class="mini-tag">Characters</span>
                                </div>
                                <div class="empty-state">
                                    <strong class="d-block mb-2">Ready for cards or graph widgets</strong>
                                    <p class="mb-3">This is a good slot for a radar chart, top picks, or a quick comparison table.</p>
                                    <div class="footer-note">
                                        Tip: the Add menu already exposes the four content types you asked for, so the next step is wiring each one into its own modal or form.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>

    <div class="modal fade" id="addGameModal" tabindex="-1" aria-labelledby="addGameModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content matchbook-modal">
                <div class="modal-header">
                    <div>
                        <span class="modal-kicker">
                            <i class="bi bi-joystick"></i>
                            New Game
                        </span>
                        <h2 class="modal-title h3 mt-3 mb-2" id="addGameModalLabel">Add a game to your library</h2>
                        <p class="modal-subtitle">Start a new tracked title with the core details you will want to filter or chart later.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="add-entry-form" data-entry-type="Game">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label" for="game-name">Game Name</label>
                                <input class="form-control" id="game-name" name="game_name" type="text" placeholder="Street Fighter 6" data-primary-field autocomplete="off" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="modal-footer-note">This matches the current <code>gamelist</code> insert shape here: just the game name. <code>score_type</code> keeps its database default for now.</div>
                        <button type="button" class="btn btn-soft-neutral" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-accent">Save Game</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addMapModal" tabindex="-1" aria-labelledby="addMapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content matchbook-modal">
                <div class="modal-header">
                    <div>
                        <span class="modal-kicker">
                            <i class="bi bi-map"></i>
                            New Map
                        </span>
                        <h2 class="modal-title h3 mt-3 mb-2" id="addMapModalLabel">Add a tracked map</h2>
                        <p class="modal-subtitle">Capture the map details you may want for win-rate breakdowns, rotations, or mode-specific stats.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="add-entry-form" data-entry-type="Map">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label" for="map-game">Game</label>
                                <select class="form-select" id="map-game" name="game_id" required>
                                    <option value=""><?= empty($games) ? "Add a game first" : "Choose game" ?></option>
                                    <?php foreach ($games as $game): ?>
                                        <option value="<?= (int) $game["game_id"] ?>"><?= h($game["game_name"]) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label" for="map-name">Map Name</label>
                                <input class="form-control" id="map-name" name="map_name" type="text" placeholder="King's Row" data-primary-field autocomplete="off" required>
                            </div>
                            <div class="col-12">
                                <div class="form-text">Games come directly from the <code>gamelist</code> table.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="modal-footer-note">This mirrors the <code>maps</code> table: <code>game_id</code> plus <code>map_name</code>.</div>
                        <button type="button" class="btn btn-soft-neutral" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-accent">Save Map</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addCharacterModal" tabindex="-1" aria-labelledby="addCharacterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content matchbook-modal">
                <div class="modal-header">
                    <div>
                        <span class="modal-kicker">
                            <i class="bi bi-person-bounding-box"></i>
                            New Character
                        </span>
                        <h2 class="modal-title h3 mt-3 mb-2" id="addCharacterModalLabel">Add a character profile</h2>
                        <p class="modal-subtitle">Set up a character, hero, or main so usage rates and matchup trends have a place to live.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="add-entry-form" data-entry-type="Character">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label" for="character-game">Game</label>
                                <select class="form-select" id="character-game" name="game_id" required>
                                    <option value=""><?= empty($games) ? "Add a game first" : "Choose game" ?></option>
                                    <?php foreach ($games as $game): ?>
                                        <option value="<?= (int) $game["game_id"] ?>"><?= h($game["game_name"]) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-7">
                                <label class="form-label" for="character-name">Character Name</label>
                                <input class="form-control" id="character-name" name="char_name" type="text" placeholder="Juri" data-primary-field autocomplete="off" required>
                            </div>
                            <div class="col-12">
                                <div class="form-text">Games come directly from the <code>gamelist</code> table.</div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="modal-footer-note">This mirrors the <code>characters</code> table: <code>game_id</code> plus <code>char_name</code>.</div>
                        <button type="button" class="btn btn-soft-neutral" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-accent">Save Character</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addMatchModal" tabindex="-1" aria-labelledby="addMatchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content matchbook-modal">
                <div class="modal-header">
                    <div>
                        <span class="modal-kicker">
                            <i class="bi bi-trophy"></i>
                            New Match
                        </span>
                        <h2 class="modal-title h3 mt-3 mb-2" id="addMatchModalLabel">Log a match</h2>
                        <p class="modal-subtitle">Capture the result, map, and character in one place so the dashboard can turn it into trends later.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form class="add-entry-form" data-entry-type="Match">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label" for="match-game-search">Game</label>
                                <input class="form-control" id="match-game-search" name="game_name" type="search" list="match-game-options" placeholder="<?= empty($games) ? "Add a game first" : "Search a game" ?>" data-primary-field autocomplete="off" required>
                                <input id="match-game-id" name="game_id" type="hidden">
                                <datalist id="match-game-options">
                                    <?php foreach ($games as $game): ?>
                                        <option value="<?= h($game["game_name"]) ?>"></option>
                                    <?php endforeach; ?>
                                </datalist>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="match-character-search">Character</label>
                                <input class="form-control" id="match-character-search" name="char_name" type="search" list="match-character-options" placeholder="<?= empty($games) ? "Add a game first" : "Select a game first" ?>" autocomplete="off" required>
                                <input id="match-character-id" name="char_id" type="hidden">
                                <datalist id="match-character-options"></datalist>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label" for="match-map-search">Map</label>
                                <input class="form-control" id="match-map-search" name="map_name" type="search" list="match-map-options" placeholder="<?= empty($games) ? "Add a game first" : "Select a game first" ?>" autocomplete="off" required>
                                <input id="match-map-id" name="map_id" type="hidden">
                                <datalist id="match-map-options"></datalist>
                            </div>
                            <div class="col-md-4" id="match-result-select-group">
                                <label class="form-label" for="match-result-select">Result</label>
                                <select class="form-select" id="match-result-select" name="result_select" required>
                                    <option value="">Choose result</option>
                                    <option>Win</option>
                                    <option>Loss</option>
                                    <option>Draw</option>
                                </select>
                            </div>
                            <div class="col-md-4" id="match-result-text-group" hidden>
                                <label class="form-label" for="match-result-text">Result</label>
                                <input class="form-control" id="match-result-text" name="result_text" type="text" placeholder="13-8 or 2-1" autocomplete="off">
                            </div>
                            <div class="col-12">
                                <div class="form-text" id="match-selection-status">
                                    <?= empty($games) ? "Add a game first to unlock character and map search." : "Pick a game to load its characters and maps." ?>
                                </div>
                                <div class="form-text" id="match-result-hint">
                                    Choose a game first. <code>score_type</code> 0 uses a result dropdown, and <code>score_type</code> 1 uses a freeform text field.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="modal-footer-note">Match options are filtered from the current <code>gamelist</code>, <code>characters</code>, and <code>maps</code> tables.</div>
                        <button type="button" class="btn btn-soft-neutral" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-accent">Save Match</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var root = document.documentElement;
        var themeToggle = document.getElementById("theme-toggle");
        var themeToggleLabel = themeToggle.querySelector(".theme-toggle-label");
        var themeToggleIcon = themeToggle.querySelector(".theme-toggle-icon");
        var feedback = document.getElementById("action-feedback");
        var modalElements = document.querySelectorAll(".modal");
        var matchbookData = <?= json_encode([
            "games" => $gameData,
            "maps" => $mapData,
            "characters" => $characterData,
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        var matchGameSearch = document.getElementById("match-game-search");
        var matchGameId = document.getElementById("match-game-id");
        var matchCharacterSearch = document.getElementById("match-character-search");
        var matchCharacterId = document.getElementById("match-character-id");
        var matchCharacterOptions = document.getElementById("match-character-options");
        var matchMapSearch = document.getElementById("match-map-search");
        var matchMapId = document.getElementById("match-map-id");
        var matchMapOptions = document.getElementById("match-map-options");
        var matchSelectionStatus = document.getElementById("match-selection-status");
        var matchResultHint = document.getElementById("match-result-hint");
        var matchResultSelectGroup = document.getElementById("match-result-select-group");
        var matchResultSelect = document.getElementById("match-result-select");
        var matchResultTextGroup = document.getElementById("match-result-text-group");
        var matchResultText = document.getElementById("match-result-text");
        var currentMatchCharacters = [];
        var currentMatchMaps = [];

        function applyTheme(theme) {
            var isDark = theme === "dark";

            root.setAttribute("data-theme", isDark ? "dark" : "light");
            themeToggle.setAttribute("aria-pressed", isDark ? "true" : "false");
            themeToggle.setAttribute("aria-label", isDark ? "Switch to light mode" : "Switch to dark mode");
            themeToggleLabel.textContent = isDark ? "Light mode" : "Dark mode";
            themeToggleIcon.className = isDark ? "bi bi-sun-fill theme-toggle-icon" : "bi bi-moon-stars-fill theme-toggle-icon";
            modalElements.forEach(function (modalElement) {
                modalElement.setAttribute("data-bs-theme", isDark ? "dark" : "light");
            });

            try {
                localStorage.setItem("matchbook-theme", isDark ? "dark" : "light");
            } catch (error) {
                /* Ignore storage failures and keep the toggle working for this session. */
            }
        }

        function normalizeLookup(value) {
            return value.trim().toLowerCase();
        }

        function findByName(items, value) {
            var normalizedValue = normalizeLookup(value || "");
            var index;

            if (!normalizedValue) {
                return null;
            }

            for (index = 0; index < items.length; index += 1) {
                if (normalizeLookup(items[index].name) === normalizedValue) {
                    return items[index];
                }
            }

            return null;
        }

        function setDatalistOptions(listElement, items) {
            listElement.innerHTML = "";

            items.forEach(function (item) {
                var option = document.createElement("option");

                option.value = item.name;
                listElement.appendChild(option);
            });
        }

        function syncSearchField(input, hiddenInput, items, invalidMessage) {
            var match = findByName(items, input.value);

            hiddenInput.value = match ? String(match.id) : "";

            if (input.disabled || !input.value.trim()) {
                input.setCustomValidity("");
            } else {
                input.setCustomValidity(match ? "" : invalidMessage);
            }

            return match;
        }

        function updateMatchResultField(scoreType) {
            var useTextField = Number(scoreType) === 1;

            matchResultSelectGroup.hidden = useTextField;
            matchResultTextGroup.hidden = !useTextField;
            matchResultSelect.disabled = useTextField;
            matchResultText.disabled = !useTextField;
            matchResultSelect.required = !useTextField;
            matchResultText.required = useTextField;

            if (useTextField) {
                matchResultSelect.value = "";
                matchResultHint.textContent = "This game uses score type 1, so result is a freeform text field.";
            } else if (scoreType === null) {
                matchResultText.value = "";
                matchResultHint.textContent = "Choose a game first. score_type 0 uses a result dropdown, and score_type 1 uses a freeform text field.";
            } else {
                matchResultText.value = "";
                matchResultHint.textContent = "This game uses score type 0, so result is a standard result dropdown.";
            }
        }

        function updateMatchSelectionStatus(selectedGame) {
            var characterCount = currentMatchCharacters.length;
            var mapCount = currentMatchMaps.length;

            if (!matchbookData.games.length) {
                matchSelectionStatus.textContent = "Add a game first to unlock character and map search.";
                return;
            }

            if (!selectedGame) {
                matchSelectionStatus.textContent = "Pick a game to load its characters and maps.";
                return;
            }

            matchSelectionStatus.textContent =
                "Loaded " +
                characterCount +
                " character" +
                (characterCount === 1 ? "" : "s") +
                " and " +
                mapCount +
                " map" +
                (mapCount === 1 ? "" : "s") +
                " for " +
                selectedGame.name +
                ".";
        }

        function updateMatchDependencies() {
            var selectedGame = syncSearchField(matchGameSearch, matchGameId, matchbookData.games, "Select a game from the list.");
            var hasGame = Boolean(selectedGame);

            currentMatchCharacters = hasGame
                ? matchbookData.characters.filter(function (character) {
                      return character.gameId === selectedGame.id;
                  })
                : [];

            currentMatchMaps = hasGame
                ? matchbookData.maps.filter(function (map) {
                      return map.gameId === selectedGame.id;
                  })
                : [];

            setDatalistOptions(matchCharacterOptions, currentMatchCharacters);
            setDatalistOptions(matchMapOptions, currentMatchMaps);

            if (!findByName(currentMatchCharacters, matchCharacterSearch.value)) {
                matchCharacterSearch.value = "";
                matchCharacterId.value = "";
            }

            if (!findByName(currentMatchMaps, matchMapSearch.value)) {
                matchMapSearch.value = "";
                matchMapId.value = "";
            }

            matchCharacterSearch.disabled = !hasGame;
            matchMapSearch.disabled = !hasGame;
            matchCharacterSearch.placeholder = !matchbookData.games.length
                ? "Add a game first"
                : !hasGame
                    ? "Select a game first"
                    : currentMatchCharacters.length
                        ? "Search a character"
                        : "No characters for this game";
            matchMapSearch.placeholder = !matchbookData.games.length
                ? "Add a game first"
                : !hasGame
                    ? "Select a game first"
                    : currentMatchMaps.length
                        ? "Search a map"
                        : "No maps for this game";

            syncSearchField(matchCharacterSearch, matchCharacterId, currentMatchCharacters, "Select a character from the selected game.");
            syncSearchField(matchMapSearch, matchMapId, currentMatchMaps, "Select a map from the selected game.");
            updateMatchSelectionStatus(selectedGame);
            updateMatchResultField(selectedGame ? selectedGame.scoreType : null);
        }

        function resetAddForm(modalElement) {
            var form = modalElement.querySelector(".add-entry-form");

            if (!form) {
                return;
            }

            form.reset();
            form.querySelectorAll("input[list]").forEach(function (input) {
                input.setCustomValidity("");
            });

            if (modalElement.id === "addMatchModal") {
                matchGameId.value = "";
                matchCharacterId.value = "";
                matchMapId.value = "";
                currentMatchCharacters = [];
                currentMatchMaps = [];
                setDatalistOptions(matchCharacterOptions, []);
                setDatalistOptions(matchMapOptions, []);
                updateMatchDependencies();
            }
        }

        themeToggle.addEventListener("click", function () {
            applyTheme(root.getAttribute("data-theme") === "dark" ? "light" : "dark");
        });

        applyTheme(root.getAttribute("data-theme") === "dark" ? "dark" : "light");
        updateMatchDependencies();

        matchGameSearch.addEventListener("input", updateMatchDependencies);
        matchGameSearch.addEventListener("change", updateMatchDependencies);
        matchCharacterSearch.addEventListener("input", function () {
            syncSearchField(matchCharacterSearch, matchCharacterId, currentMatchCharacters, "Select a character from the selected game.");
        });
        matchCharacterSearch.addEventListener("change", function () {
            syncSearchField(matchCharacterSearch, matchCharacterId, currentMatchCharacters, "Select a character from the selected game.");
        });
        matchMapSearch.addEventListener("input", function () {
            syncSearchField(matchMapSearch, matchMapId, currentMatchMaps, "Select a map from the selected game.");
        });
        matchMapSearch.addEventListener("change", function () {
            syncSearchField(matchMapSearch, matchMapId, currentMatchMaps, "Select a map from the selected game.");
        });

        document.querySelectorAll("[data-add-type]").forEach(function (button) {
            button.addEventListener("click", function () {
                var selectedType = button.getAttribute("data-add-type");

                feedback.textContent = "Opening the Add " + selectedType + " modal. Fill it out now, then we can wire the save action into the database next.";
            });
        });

        modalElements.forEach(function (modalElement) {
            modalElement.addEventListener("shown.bs.modal", function () {
                if (modalElement.id === "addMatchModal") {
                    updateMatchDependencies();
                }

                var primaryField = modalElement.querySelector("[data-primary-field]");

                if (primaryField) {
                    primaryField.focus();
                }
            });

            modalElement.addEventListener("hidden.bs.modal", function () {
                resetAddForm(modalElement);
            });
        });

        document.querySelectorAll(".add-entry-form").forEach(function (form) {
            form.addEventListener("submit", function (event) {
                var entryType = form.getAttribute("data-entry-type");
                var primaryField = form.querySelector("[data-primary-field]");
                var primaryValue = primaryField && primaryField.value.trim() ? primaryField.value.trim() : "";
                var modalElement = form.closest(".modal");
                var modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);

                event.preventDefault();

                if (entryType === "Match") {
                    updateMatchDependencies();
                }

                if (!form.reportValidity()) {
                    return;
                }

                feedback.textContent = primaryValue
                    ? entryType + " draft ready: " + primaryValue + ". The modal UI is live; the next step is wiring this form to save."
                    : entryType + " form submitted. The modal is ready, and we can connect it to persistence whenever you want.";

                modalInstance.hide();

                form.reset();
            });
        });
    </script>
</body>
</html>
