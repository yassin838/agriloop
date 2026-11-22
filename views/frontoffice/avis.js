const interactionKey = 'agriloopAvisInteractions';
const storageMessage = document.getElementById('storageMessage');
let interactions = { votes: {}, comments: {} };
const initialCommentsByPost = new Map();

function showStorageError() {
  storageMessage.textContent = 'Les votes et commentaires ne peuvent pas être enregistrés dans ce navigateur.';
}

function saveInteractions() {
  try {
    localStorage.setItem(interactionKey, JSON.stringify(interactions));
    return true;
  } catch (error) {
    console.error('Impossible d’enregistrer les interactions AgriLoop.', error);
    showStorageError();
    return false;
  }
}

try {
  const saved = localStorage.getItem(interactionKey);
  if (saved) {
    const parsed = JSON.parse(saved);
    if (parsed && typeof parsed === 'object') {
      interactions = {
        votes: parsed.votes && typeof parsed.votes === 'object' ? parsed.votes : {},
        comments: parsed.comments && typeof parsed.comments === 'object' ? parsed.comments : {},
      };
    }
  }
} catch (error) {
  console.error('Impossible de lire les interactions AgriLoop.', error);
  showStorageError();
}

function addComment(list, author, text) {
  const item = document.createElement('li');
  item.className = 'comment';

  const avatar = document.createElement('span');
  avatar.className = 'comment-avatar';
  avatar.setAttribute('aria-hidden', 'true');
  avatar.textContent = author.charAt(0).toUpperCase();

  const content = document.createElement('p');
  const name = document.createElement('strong');
  name.textContent = author;
  const message = document.createElement('span');
  message.textContent = text;

  content.append(name, message);
  item.append(avatar, content);
  list.append(item);
}

function renderPost(post) {
  const postId = post.dataset.postId;
  const initialComments = initialCommentsByPost.get(postId) || [];
  const savedComments = Array.isArray(interactions.comments[postId])
    ? interactions.comments[postId].filter((comment) =>
      comment && typeof comment.author === 'string' && typeof comment.text === 'string')
    : [];
  const comments = [...initialComments, ...savedComments];
  const savedVote = Number(interactions.votes[postId]);
  const vote = savedVote === 1 || savedVote === -1 ? savedVote : 0;
  const score = Number(post.dataset.baseScore) + vote;
  const scoreOutput = post.querySelector('.vote-score');
  scoreOutput.value = String(score);
  scoreOutput.textContent = String(score);

  post.querySelectorAll('.vote-button').forEach((button) => {
    const selected = Number(button.dataset.vote) === vote;
    button.classList.toggle('is-active', selected);
    button.setAttribute('aria-pressed', String(selected));
  });

  const commentList = post.querySelector('.comment-list');
  commentList.replaceChildren();
  initialComments.forEach((comment) => addComment(commentList, comment.author, comment.text));
  savedComments.forEach((comment) => addComment(commentList, comment.author, comment.text));

  const count = comments.length;
  post.querySelector('.comment-count').textContent = `${count} commentaire${count === 1 ? '' : 's'}`;
}

const posts = Array.from(document.querySelectorAll('.post-card'));
posts.forEach((post) => {
  const initialComments = Array.from(post.querySelectorAll('.comment-list .comment')).map((item) => ({
    author: item.dataset.author || item.querySelector('strong').textContent,
    text: item.querySelector('p span').textContent,
  }));
  initialCommentsByPost.set(post.dataset.postId, initialComments);
  renderPost(post);
});

document.addEventListener('click', (event) => {
  const voteButton = event.target.closest('.vote-button');
  if (voteButton) {
    const post = voteButton.closest('.post-card');
    const postId = post.dataset.postId;
    const desiredVote = Number(voteButton.dataset.vote);
    const storedVote = Number(interactions.votes[postId]);
    const currentVote = storedVote === 1 || storedVote === -1 ? storedVote : 0;
    interactions.votes[postId] = currentVote === desiredVote ? 0 : desiredVote;
    saveInteractions();
    renderPost(post);
    return;
  }

  const toggleButton = event.target.closest('.comment-toggle');
  if (toggleButton) {
    const panel = toggleButton.closest('.post-body').querySelector('.comments-panel');
    panel.hidden = !panel.hidden;
    toggleButton.setAttribute('aria-expanded', String(!panel.hidden));
    if (!panel.hidden) {
      panel.querySelector('input').focus();
    }
  }
});

document.addEventListener('submit', (event) => {
  const form = event.target.closest('.comment-form');
  if (!form) {
    return;
  }

  event.preventDefault();
  const post = form.closest('.post-card');
  const postId = post.dataset.postId;
  const text = form.elements.commentaire.value.trim();
  if (!text) {
    return;
  }

  interactions.comments[postId] = Array.isArray(interactions.comments[postId])
    ? interactions.comments[postId]
    : [];
  interactions.comments[postId].push({ author: 'Vous', text });
  saveInteractions();
  renderPost(post);
  post.querySelector('.comments-panel').hidden = false;
  post.querySelector('.comment-toggle').setAttribute('aria-expanded', 'true');
  form.reset();
  form.elements.commentaire.focus();
});
