const el = (id) => document.getElementById(id);
const screens = {
  start: el("start-screen"),
  quiz: el("quiz-screen"),
  result: el("result-screen"),
};
const optionsEl = el("options");

const SCORES_KEY = "mlbb_scores";
const NAME_KEY = "mlbb_name";
const ROUND_SIZE = 10;

let allQuestions = null;
let round = [];
let current = 0;
let score = 0;

// ---------- Helpers ----------
function show(name) {
  Object.entries(screens).forEach(([key, screen]) => (screen.hidden = key !== name));
  el("board").hidden = name === "quiz";
}

function shuffle(array) {
  const copy = [...array];
  for (let i = copy.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [copy[i], copy[j]] = [copy[j], copy[i]];
  }
  return copy;
}

// ---------- Leaderboard (stored in this browser only) ----------
function readScores() {
  try {
    return JSON.parse(localStorage.getItem(SCORES_KEY)) || [];
  } catch (e) {
    return [];
  }
}

function loadLeaderboard() {
  const list = el("leaderboard");
  list.innerHTML = "";
  const rows = readScores()
    .sort((a, b) => b.score / b.total - a.score / a.total || a.time - b.time)
    .slice(0, 10);
  if (!rows.length) {
    list.innerHTML = "<li>No scores yet. Be the first!</li>";
    return;
  }
  rows.forEach((r) => {
    const li = document.createElement("li");
    li.textContent = `${r.name} – ${r.score}/${r.total}`; // textContent keeps names from running as HTML
    list.appendChild(li);
  });
}

function saveScore(event) {
  event.preventDefault();
  const name = el("name-input").value.trim().slice(0, 20);
  if (!name) return;
  const scores = readScores();
  scores.push({ name, score, total: round.length, time: Date.now() });
  try {
    localStorage.setItem(SCORES_KEY, JSON.stringify(scores.slice(-200)));
    localStorage.setItem(NAME_KEY, name);
  } catch (e) {
    alert("Could not save your score in this browser.");
    return;
  }
  el("save-area").hidden = true;
  el("saved-msg").hidden = false;
  loadLeaderboard();
}

// ---------- Game flow ----------
async function startQuiz() {
  try {
    if (!allQuestions) {
      const res = await fetch("questions.json");
      if (!res.ok) throw new Error("HTTP " + res.status);
      allQuestions = await res.json();
    }
    if (!allQuestions.length) throw new Error("No questions");
  } catch (e) {
    alert("Could not load questions.json.");
    return;
  }
  // Pick 10 random questions, shuffle each one's options, remember the right answer by its text
  round = shuffle(allQuestions)
    .slice(0, ROUND_SIZE)
    .map((q) => ({ ...q, correctText: q.options[q.answer], options: shuffle(q.options) }));
  current = 0;
  score = 0;
  show("quiz");
  setActiveNav("nav-play");
  showQuestion();
}

function showQuestion() {
  const q = round[current];

  el("progress").textContent = `Question ${current + 1} / ${round.length}`;
  el("progress-fill").style.width = `${(current / round.length) * 100}%`;
  el("score").textContent = `Score: ${score}`;
  el("category").textContent = q.category;
  el("question").textContent = q.question;

  // Optional picture for this question (file lives in the images/ folder)
  const img = el("question-img");
  img.onerror = () => (img.hidden = true); // missing file: just show no picture
  if (q.image) {
    img.src = `images/${encodeURIComponent(q.image)}`;
    img.hidden = false;
  } else {
    img.hidden = true;
    img.removeAttribute("src");
  }

  el("next-btn").hidden = true;
  optionsEl.innerHTML = "";

  q.options.forEach((text) => {
    const btn = document.createElement("button");
    btn.className = "option";
    btn.textContent = text;
    btn.addEventListener("click", () => chooseAnswer(btn, text));
    optionsEl.appendChild(btn);
  });
}

function chooseAnswer(clickedBtn, chosenText) {
  const q = round[current];

  optionsEl.querySelectorAll(".option").forEach((btn) => {
    btn.disabled = true;
    if (btn.textContent === q.correctText) btn.classList.add("correct");
  });

  if (chosenText === q.correctText) {
    score++;
    el("score").textContent = `Score: ${score}`;
  } else {
    clickedBtn.classList.add("wrong");
  }

  el("next-btn").textContent = current === round.length - 1 ? "See Results" : "Next";
  el("next-btn").hidden = false;
}

function nextStep() {
  current++;
  if (current < round.length) showQuestion();
  else showResults();
}

function getRank(percent) {
  if (percent === 100) return { title: "Mythic", msg: "Perfect score! You know this game inside out." };
  if (percent >= 80) return { title: "Legend", msg: "Amazing! You're a true veteran." };
  if (percent >= 60) return { title: "Epic", msg: "Solid knowledge. A few more matches and you'll be Legend." };
  if (percent >= 40) return { title: "Grandmaster", msg: "Not bad! Keep climbing." };
  if (percent >= 20) return { title: "Elite", msg: "You're getting there. Try again!" };
  return { title: "Warrior", msg: "Everyone starts somewhere. Play again!" };
}

function showResults() {
  const rank = getRank((score / round.length) * 100);

  el("final-score").textContent = score;
  el("total").textContent = round.length;
  el("rank-title").textContent = rank.title;
  el("rank-message").textContent = rank.msg;

  el("save-area").hidden = false;
  el("saved-msg").hidden = true;
  try {
    el("name-input").value = localStorage.getItem(NAME_KEY) || "";
  } catch (e) {
    // storage unavailable
  }

  show("result");
}

// ---------- Navigation bar ----------
function setActiveNav(id) {
  document.querySelectorAll(".nav-link").forEach((link) => link.classList.toggle("active", link.id === id));
}

// Leaving a quiz in progress throws away the round, so ask first
function leaveQuizOk() {
  if (screens.quiz.hidden) return true;
  return confirm("Leave the quiz? Your progress will be lost.");
}

function goHome(event) {
  event.preventDefault();
  if (!leaveQuizOk()) return;
  show("start");
  setActiveNav("nav-play");
  window.scrollTo({ top: 0, behavior: "smooth" });
}

function goLeaderboard(event) {
  event.preventDefault();
  if (!leaveQuizOk()) return;
  if (!screens.quiz.hidden) show("start"); // the leaderboard is hidden during a quiz
  setActiveNav("nav-board");
  loadLeaderboard();
  el("board").scrollIntoView({ behavior: "smooth" });
}

// ---------- Wire up buttons ----------
el("start-btn").addEventListener("click", startQuiz);
el("next-btn").addEventListener("click", nextStep);
el("restart-btn").addEventListener("click", startQuiz);
el("save-area").addEventListener("submit", saveScore);
el("nav-brand").addEventListener("click", goHome);
el("nav-play").addEventListener("click", goHome);
el("nav-board").addEventListener("click", goLeaderboard);

show("start");
loadLeaderboard();