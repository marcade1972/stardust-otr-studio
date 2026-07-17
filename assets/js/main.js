document.addEventListener('DOMContentLoaded', function () {
  var menuButton = document.querySelector('.menu-toggle');
  var menu = document.getElementById('primary-menu');
  if (menuButton && menu) {
    menuButton.addEventListener('click', function () {
      var open = menuButton.getAttribute('aria-expanded') === 'true';
      menuButton.setAttribute('aria-expanded', String(!open));
      menu.classList.toggle('is-open', !open);
    });
    menu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.innerWidth <= 720) {
          menuButton.setAttribute('aria-expanded', 'false');
          menu.classList.remove('is-open');
        }
      });
    });
  }

  var form = document.getElementById('stardust-tune-form');
  if (!form || typeof stardustDial === 'undefined') return;

  var month = document.getElementById('stardust-dial-month');
  var day = document.getElementById('stardust-dial-day');
  var genre = document.getElementById('stardust-dial-genre');
  var knob = document.getElementById('stardust-gold-knob');
  var needle = document.getElementById('stardust-frequency-needle');
  var surprise = document.getElementById('stardust-surprise-button');
  var status = document.getElementById('stardust-dial-status');
  var results = document.getElementById('stardust-dial-results');

  function daysInMonth(monthNumber) {
    return new Date(2024, monthNumber, 0).getDate();
  }

  function updateDays() {
    var selected = parseInt(day.value || '1', 10);
    var max = daysInMonth(parseInt(month.value || '1', 10));
    day.innerHTML = '';
    for (var i = 1; i <= max; i += 1) {
      var option = document.createElement('option');
      option.value = String(i);
      option.textContent = String(i);
      if (i === Math.min(selected, max)) option.selected = true;
      day.appendChild(option);
    }
  }

  function animateDial(monthValue, dayValue) {
    var turn = ((parseInt(monthValue, 10) - 1) * 30) + (parseInt(dayValue, 10) / 31 * 26) - 145;
    knob.style.transform = 'rotate(' + turn + 'deg)';
    needle.style.transform = 'translateX(' + (((parseInt(monthValue, 10) - 1) / 11 * 70) - 35) + 'px)';
  }

  function playTuningSound() {
    try {
      var AudioContextClass = window.AudioContext || window.webkitAudioContext;
      if (!AudioContextClass) return;
      var ctx = new AudioContextClass();
      var duration = 0.65 + Math.random() * 0.35;
      var buffer = ctx.createBuffer(1, Math.floor(ctx.sampleRate * duration), ctx.sampleRate);
      var data = buffer.getChannelData(0);
      for (var i = 0; i < data.length; i += 1) {
        var fade = 1 - (i / data.length);
        data[i] = (Math.random() * 2 - 1) * fade * 0.22;
      }
      var source = ctx.createBufferSource();
      var filter = ctx.createBiquadFilter();
      filter.type = 'bandpass';
      filter.frequency.value = 900 + Math.random() * 1700;
      source.buffer = buffer;
      source.connect(filter).connect(ctx.destination);
      source.start();
      source.onended = function () { ctx.close(); };
    } catch (e) {}
  }

  function setBusy(isBusy) {
    form.classList.toggle('is-tuning', isBusy);
    Array.prototype.forEach.call(form.querySelectorAll('button,select'), function (control) {
      control.disabled = isBusy;
    });
    if (isBusy) { status.textContent = stardustDial.searching || stardustDial.loading; playTuningSound(); document.body.classList.add('station-is-tuning'); } else { document.body.classList.remove('station-is-tuning'); }
  }

  function showResponse(payload) {
    month.value = String(payload.month);
    updateDays();
    day.value = String(payload.day);
    animateDial(payload.month, payload.day);
    results.innerHTML = payload.html;
    results.hidden = false;
    var selectedGenre = genre && genre.options[genre.selectedIndex] ? genre.options[genre.selectedIndex].text : '';
    status.textContent = payload.count ? ((stardustDial.tunedPrefix || 'Now tuned to') + ' ' + (selectedGenre && selectedGenre !== 'All Genres' ? selectedGenre : 'WSTR 109') + ' — ' + payload.count + (payload.count === 1 ? ' broadcast found.' : ' broadcasts found.')) : 'Static on the airwaves — no broadcast found.';
    results.scrollIntoView({ behavior: 'smooth', block: 'start' });
  }

  function request(action) {
    var data = new FormData();
    data.append('action', action);
    data.append('nonce', stardustDial.nonce);
    data.append('month', month.value);
    data.append('day', day.value);
    data.append('genre', genre.value);
    setBusy(true);
    fetch(stardustDial.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data })
      .then(function (response) { return response.json(); })
      .then(function (json) {
        if (!json.success || !json.data) throw new Error('Invalid response');
        showResponse(json.data);
      })
      .catch(function () { status.textContent = stardustDial.error; })
      .finally(function () { setBusy(false); });
  }

  month.addEventListener('change', function () { updateDays(); animateDial(month.value, day.value); });
  day.addEventListener('change', function () { animateDial(month.value, day.value); });
  form.addEventListener('submit', function (event) { event.preventDefault(); request('stardust_tune_dial'); });
  surprise.addEventListener('click', function () { request('stardust_surprise_dial'); });
  updateDays();
  animateDial(month.value, day.value);
});


document.addEventListener('play', function (event) {
  if (event.target && event.target.tagName === 'AUDIO') document.body.classList.add('station-is-live');
}, true);
document.addEventListener('pause', function (event) {
  if (event.target && event.target.tagName === 'AUDIO') document.body.classList.remove('station-is-live');
}, true);
document.addEventListener('ended', function (event) {
  if (event.target && event.target.tagName === 'AUDIO') document.body.classList.remove('station-is-live');
}, true);

/* Listener episode thumbs-up / thumbs-down voting. */
document.addEventListener('DOMContentLoaded', function () {
  var rating = document.querySelector('.episode-rating[data-post-id]');
  if (!rating || typeof stardustRatings === 'undefined') return;

  var buttons = rating.querySelectorAll('.episode-vote-button');
  var status = rating.querySelector('.rating-status');

  function setBusy(busy) {
    Array.prototype.forEach.call(buttons, function (button) { button.disabled = busy; });
    rating.classList.toggle('is-saving', busy);
  }

  Array.prototype.forEach.call(buttons, function (button) {
    button.addEventListener('click', function () {
      var vote = button.getAttribute('data-vote');
      var data = new FormData();
      data.append('action', 'stardust_rate_episode');
      data.append('nonce', stardustRatings.nonce);
      data.append('post_id', rating.getAttribute('data-post-id'));
      data.append('vote', vote);

      setBusy(true);
      status.textContent = stardustRatings.saving;
      fetch(stardustRatings.ajaxUrl, { method: 'POST', credentials: 'same-origin', body: data })
        .then(function (response) { return response.json(); })
        .then(function (json) {
          if (!json.success || !json.data) throw new Error('Invalid response');
          var likes = rating.querySelector('.vote-likes');
          var dislikes = rating.querySelector('.vote-dislikes');
          if (likes) likes.textContent = Number(json.data.likes).toLocaleString();
          if (dislikes) dislikes.textContent = Number(json.data.dislikes).toLocaleString();
          Array.prototype.forEach.call(buttons, function (candidate) {
            var selected = candidate.getAttribute('data-vote') === String(json.data.vote);
            candidate.classList.toggle('is-selected', selected);
            candidate.setAttribute('aria-pressed', selected ? 'true' : 'false');
          });
          status.textContent = json.data.message;
        })
        .catch(function () { status.textContent = stardustRatings.error; })
        .finally(function () { setBusy(false); });
    });
  });
});


/* Stardust episode reactions: private, browser-local thumbs up/down. */
document.addEventListener('DOMContentLoaded', function () {
    var groups = Array.prototype.slice.call(document.querySelectorAll('.episode-reactions[data-reaction-key]'));
    if (!groups.length) return;
    var prefix = 'stardustEpisodeReaction:';
    function safeGet(key) { try { return window.localStorage.getItem(prefix + key) || ''; } catch (e) { return ''; } }
    function safeSet(key, value) { try { if (value) window.localStorage.setItem(prefix + key, value); else window.localStorage.removeItem(prefix + key); } catch (e) {} }
    groups.forEach(function (group) {
        var key = group.getAttribute('data-reaction-key') || '';
        var buttons = Array.prototype.slice.call(group.querySelectorAll('.episode-reaction'));
        function paint(value) {
            buttons.forEach(function (button) {
                var active = button.getAttribute('data-reaction') === value;
                button.classList.toggle('is-selected', active);
                button.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
        }
        paint(safeGet(key));
        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                var choice = button.getAttribute('data-reaction') || '';
                var next = safeGet(key) === choice ? '' : choice;
                safeSet(key, next);
                paint(next);
            });
        });
    });
});
