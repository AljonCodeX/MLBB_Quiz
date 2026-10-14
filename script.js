const el = (id) => document.getElementById(id);
const screens = { start: el("start-screen"), quiz: el("quiz-screen"), result: el("result-screen") };
const optionsEl = el("options");
const saveForm = el("save-form");

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

// Talk to api.php (which talks to the MySQL database)
async function api(url, options) {
  const res = await fetch(url, options);
  if (!res.ok) throw new Error(`Request failed: ${res.status}`);
  return res.json();
}

// ---------- Leaderboard ----------
async function loadLeaderboard() {
  const list = el("leaderboard");
  list.innerHTML = "";
  let rows;
  try {
    rows = await api("api.php?action=scores");
  } catch (e) {
    list.innerHTML = "<li>Leaderboard unavailable</li>";
    return;
  }
  if (!rows.length) {
    list.innerHTML = "<li>No scores yet. Be the first!</li>";
    return;
  }
  rows.forEach((r) => {
    const li = document.createElement("li");
    li.textContent = `${r.name} – ${r.score}/${r.total}`; // textContent keeps player names from running as HTML
    list.appendChild(li);
  });
}

async function saveScore(event) {
  event.preventDefault();
  const button = saveForm.querySelector("button");
  button.disabled = true;
  try {
    await api("api.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ name: el("name").value.trim(), score, total: round.length }),
    });
    saveForm.hidden = true;
    el("saved-msg").hidden = false;
    loadLeaderboard();
  } catch (e) {
    button.disabled = false;
    alert("Could not save your score. Please try again.");
  }
}

// ---------- Game flow ----------
async function startQuiz() {
  try {
    const questions = await api("api.php?action=questions");
    if (!questions.length) throw new Error("No questions in the database");
    // Shuffle each question's options and remember the right one by its text
    round = questions.map((q) => ({ ...q, correctText: q.options[q.answer], options: shuffle(q.options) }));
  } catch (e) {
    alert("Could not load questions. Check that Apache and MySQL are running and database.sql was imported.");
    return;
  }
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

  saveForm.hidden = false;
  saveForm.querySelector("button").disabled = false;
  el("saved-msg").hidden = true;

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
saveForm.addEventListener("submit", saveScore);
el("nav-brand").addEventListener("click", goHome);
el("nav-play").addEventListener("click", goHome);
el("nav-board").addEventListener("click", goLeaderboard);

show("start");
loadLeaderboard();