<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Live Match Scoreboard</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: Arial, sans-serif;
}

body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
}

.scoreboard {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(15px);
    padding: 30px;
    border-radius: 20px;
    width: 90%;
    max-width: 450px;
    text-align: center;
    color: white;
    box-shadow: 0 10px 25px rgba(0,0,0,0.4);
    animation: fadeIn 1s ease-in-out;
}

h1 {
    margin-bottom: 20px;
}

.teams {
    display: flex;
    justify-content: space-between;
    gap: 20px;
}

.team {
    flex: 1;
    padding: 15px;
    border-radius: 15px;
    background: rgba(255,255,255,0.08);
    transition: transform 0.3s;
}

.team:hover {
    transform: translateY(-5px);
}

.score {
    font-size: 2.5rem;
    margin: 10px 0;
    transition: transform 0.3s ease;
}

button {
    margin: 5px;
    padding: 8px 12px;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

button:hover {
    transform: scale(1.1);
    opacity: 0.9;
}

.controls {
    margin-top: 20px;
}

.reset {
    background: crimson;
    color: white;
}

.winner {
    margin-top: 15px;
    font-size: 1.2rem;
    font-weight: bold;
    color: gold;
    animation: blink 1s infinite alternate;
}

@keyframes fadeIn {
    from {opacity: 0; transform: translateY(20px);}
    to {opacity: 1; transform: translateY(0);}
}

@keyframes blink {
    from {opacity: 1;}
    to {opacity: 0.4;}
}

/* Responsive Design */
@media (max-width: 600px) {
    .teams {
        flex-direction: column;
    }
}
</style>
</head>

<body>

<div class="scoreboard">
    <h1>🏆 Live Match Score</h1>

    <div class="teams">
        <div class="team">
            <h2>Team A</h2>
            <div class="score" id="scoreA">0</div>
            <button onclick="updateScore('A',1)">+1</button>
            <button onclick="updateScore('A',2)">+2</button>
            <button onclick="updateScore('A',3)">+3</button>
        </div>

        <div class="team">
            <h2>Team B</h2>
            <div class="score" id="scoreB">0</div>
            <button onclick="updateScore('B',1)">+1</button>
            <button onclick="updateScore('B',2)">+2</button>
            <button onclick="updateScore('B',3)">+3</button>
        </div>
    </div>

    <div class="controls">
        <button class="reset" onclick="resetMatch()">Reset Match</button>
        <div class="winner" id="winner"></div>
    </div>
</div>

<script>
// ===============================
// STATE MANAGEMENT (ES6)
// ===============================

const matchState = {
    teamA: 0,
    teamB: 0,
    winningScore: 15
};

const scoreAElement = document.getElementById("scoreA");
const scoreBElement = document.getElementById("scoreB");
const winnerElement = document.getElementById("winner");

// Update Score
function updateScore(team, points) {

    if (checkWinner()) return;

    if (team === 'A') {
        let newScore = matchState.teamA + points;
        matchState.teamA = newScore;
    } else {
        let newScore = matchState.teamB + points;
        matchState.teamB = newScore;
    }

    render();
    checkWinner();
}

// Render UI
function render() {
    scoreAElement.textContent = matchState.teamA;
    scoreBElement.textContent = matchState.teamB;
}

// Check Winner
function checkWinner() {
    if (matchState.teamA >= matchState.winningScore) {
        winnerElement.textContent = "🎉 Team A Wins!";
        return true;
    }
    if (matchState.teamB >= matchState.winningScore) {
        winnerElement.textContent = "🎉 Team B Wins!";
        return true;
    }
    return false;
}

// Reset Match
function resetMatch() {
    matchState.teamA = 0;
    matchState.teamB = 0;
    winnerElement.textContent = "";
    render();
}
</script>

</body>
</html>