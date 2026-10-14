const el = (id) => document.getElementById(id);
const screens = {
  start: el("start-screen"),
  quiz: el("quiz-screen"),
  result: el("result-screen"),
  auth: el("auth-screen"),
};
const optionsEl = el("options");

let round = [];
let current = 0;
let score = 0;
let user = null; // the logged-in player, or null
let authMode = "login"; // "login" or "register"
let returnTo = null; // "result" = go back to the results screen after logging in

// ---------- Helpers ----------
function show(name) {
  Object.entries(screens).forEach(([key, screen]) => (screen.hidden = key !== name));
  el("board").hidden = name === "quiz" || name === "auth";
}

function shuffle(array) {
  const copy = [...array];
  for (let i = copy.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [copy[i], copy[j]] = [copy[j], copy[i]];
  }
  return copy;
}

// Talk to the PHP files (which talk to the MySQL database)
async function api(url, options) {
  const res = await fetch(url, options);
  let data = null;
  try {
    data = await res.json();
  } catch (e) {
    // response was not JSON
  }
  if (!res.ok) {
    const err = new Error((data && data.error) || `Request failed: ${res.status}`);
    err.status = res.status;
    throw err;
  }
  return data;
}

function postJson(url, body) {
  return api(url, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  });
}

// ---------- Accounts ----------
function renderNav() {
  el("nav-guest").hidden = !!user;
  el("nav-member").hidden = !user;
  el("nav-name").textContent = user ? user.username : "";
  el("nav-admin").hidden = !(user && user.role === "admin");
}

async function loadUser() {
  try {
    user = (await api("auth_api.php?action=me")).user;
  } catch (e) {
    user = null;
  }
  renderNav();
}

function renderAuthMode() {
  const login = authMode === "login";
  el("auth-title").textContent = login ? "Log in" : "Create account";
  el("auth-submit").textContent = login ? "Log in" : "Sign up";
  el("auth-toggle").textContent = login ? "New here? Create an account" : "Already have an account? Log in";
  el("auth-password").autocomplete = login ? "current-password" : "new-password";
  el("auth-hint").hidden = login;
}

function openAuth(mode) {
  authMode = mode;
  el("auth-form").reset();
  el("auth-error").hidden = true;
  renderAuthMode();
  show("auth");
  setActiveNav("nav-login");
  el("auth-username").focus();
}

async function submitAuth(event) {
  event.preventDefault();
  const button = el("auth-submit");
  button.disabled = true;
  el("auth-error").hidden = true;
  try {
    const data = await postJson(`auth_api.php?action=${authMode}`, {
      username: el("auth-username").value.trim(),
      password: el("auth-password").value,
    });
    user = data.user;
    renderNav();
    el("auth-form").reset();
    if (returnTo === "result") {
      returnTo = null;
      showResults();
    } else {
      show("start");
      setActiveNav("nav-play");
    }
  } catch (e) {
    el("auth-error").textContent = e.message;
    el("auth-error").hidden = false;
  } finally {
    button.disabled = false;
  }
}

async function logout(event) {
  event.preventDefault();
  if (!leaveQuizOk()) return;
  try {
    await postJson("auth_api.php?action=logout", {});
  } catch (e) {
    // even if this fails, forget the user in the page
  }
  user = null;
  returnTo = null;
  renderNav();
  show("start");
  setActiveNav("nav-play");
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

async function saveScore() {
  if (!user) {
    returnTo = "result";
    openAuth("login");
    return;
  }
  const button = el("save-btn");
  button.disabled = true;
  try {
    await postJson("api.php", { score, total: round.length });
    el("save-area").hidden = true;
    el("saved-msg").hidden = false;
    loadLeaderboard();
  } catch (e) {
    button.disabled = false;
    if (e.status === 401) {
      // session expired
      user = null;
      renderNav();
      returnTo = "result";
      openAuth("login");
      return;
    }
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
  returnTo = null;
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
  el("save-btn").disabled = false;
  el("saved-msg").hidden = true;
  if (user) {
    el("save-note").textContent = `Saving as ${user.username}`;
    el("save-btn").textContent = "Save Score to Leaderboard";
  } else {
    el("save-note").textContent = "Log in to put your score on the leaderboard.";
    el("save-btn").textContent = "Log in / Sign up";
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
  returnTo = null;
  show("start");
  setActiveNav("nav-play");
  window.scrollTo({ top: 0, behavior: "smooth" });
}

function goLeaderboard(event) {
  event.preventDefault();
  if (!leaveQuizOk()) return;
  returnTo = null;
  if (!screens.quiz.hidden || !screens.auth.hidden) show("start"); // the leaderboard is hidden during a quiz and on the login screen
  setActiveNav("nav-board");
  loadLeaderboard();
  el("board").scrollIntoView({ behavior: "smooth" });
}

function goLogin(event) {
  event.preventDefault();
  if (!leaveQuizOk()) return;
  returnTo = null;
  openAuth("login");
}

// ---------- Wire up buttons ----------
el("start-btn").addEventListener("click", startQuiz);
el("next-btn").addEventListener("click", nextStep);
el("restart-btn").addEventListener("click", startQuiz);
el("save-btn").addEventListener("click", saveScore);
el("auth-form").addEventListener("submit", submitAuth);
el("auth-toggle").addEventListener("click", (event) => {
  event.preventDefault();
  openAuth(authMode === "login" ? "register" : "login");
});
el("nav-brand").addEventListener("click", goHome);
el("nav-play").addEventListener("click", goHome);
el("nav-board").addEventListener("click", goLeaderboard);
el("nav-login").addEventListener("click", goLogin);
el("nav-logout").addEventListener("click", logout);

show("start");
loadUser();
loadLeaderboard();
