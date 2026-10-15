const menuButton = document.querySelector('.menu-toggle');
const navigation = document.querySelector('.main-nav');

menuButton?.addEventListener('click', () => {
  const isOpen = menuButton.getAttribute('aria-expanded') === 'true';
  menuButton.setAttribute('aria-expanded', String(!isOpen));
  navigation.classList.toggle('nav-open', !isOpen);
});

document.querySelectorAll('.main-nav > a').forEach((link) => {
  link.addEventListener('click', () => {
    menuButton?.setAttribute('aria-expanded', 'false');
    navigation?.classList.remove('nav-open');
  });
});

// --- services dropdown ---
document.querySelectorAll('.nav-item.has-dropdown').forEach((item) => {
  const toggle = item.querySelector('.dropdown-toggle');
  const parentLink = item.querySelector(':scope > a');

  toggle?.addEventListener('click', (event) => {
    event.stopPropagation();
    const isOpen = item.classList.toggle('open');
    toggle.setAttribute('aria-expanded', String(isOpen));
  });

  item.querySelectorAll('.dropdown-menu a').forEach((link) => {
    link.addEventListener('click', () => {
      item.classList.remove('open');
      toggle?.setAttribute('aria-expanded', 'false');
      menuButton?.setAttribute('aria-expanded', 'false');
      navigation?.classList.remove('nav-open');
    });
  });

  parentLink?.addEventListener('click', () => {
    item.classList.remove('open');
    toggle?.setAttribute('aria-expanded', 'false');
    menuButton?.setAttribute('aria-expanded', 'false');
    navigation?.classList.remove('nav-open');
  });
});

document.addEventListener('click', (event) => {
  document.querySelectorAll('.nav-item.has-dropdown.open').forEach((item) => {
    if (!item.contains(event.target)) {
      item.classList.remove('open');
      item.querySelector('.dropdown-toggle')?.setAttribute('aria-expanded', 'false');
    }
  });
});

document.addEventListener('keydown', (event) => {
  if (event.key === 'Escape') {
    document.querySelectorAll('.nav-item.has-dropdown.open').forEach((item) => {
      item.classList.remove('open');
      item.querySelector('.dropdown-toggle')?.setAttribute('aria-expanded', 'false');
    });
  }
});

const revealObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      entry.target.classList.add('visible');
      revealObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.12 });

document.querySelectorAll('.reveal').forEach((element) => revealObserver.observe(element));

// --- animated stat counters ---
function animateCounter(el) {
  const target = parseFloat(el.dataset.target || '0');
  const decimals = parseInt(el.dataset.decimals || '0', 10);
  const suffix = el.dataset.suffix || '';
  const duration = 1400;
  const start = performance.now();

  function tick(now) {
    const progress = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    const value = target * eased;
    el.textContent = value.toFixed(decimals) + suffix;
    if (progress < 1) requestAnimationFrame(tick);
  }
  requestAnimationFrame(tick);
}

const counterObserver = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      animateCounter(entry.target);
      counterObserver.unobserve(entry.target);
    }
  });
}, { threshold: 0.4 });

document.querySelectorAll('.impact-number').forEach((el) => counterObserver.observe(el));

// --- testimonial slider ---
const track = document.getElementById('testimonial-track');
if (track) {
  const cards = Array.from(track.querySelectorAll('.testimonial-card'));
  const dotsWrap = document.getElementById('testimonial-dots');
  const prevBtn = document.querySelector('.slider-btn[data-dir="-1"]');
  const nextBtn = document.querySelector('.slider-btn[data-dir="1"]');
  let index = 0;
  let autoplayTimer = null;

  cards.forEach((_, i) => {
    const dot = document.createElement('button');
    dot.setAttribute('aria-label', `Go to testimonial ${i + 1}`);
    if (i === 0) dot.classList.add('active');
    dot.addEventListener('click', () => goTo(i));
    dotsWrap.appendChild(dot);
  });
  const dots = Array.from(dotsWrap.children);

  function goTo(i) {
    index = (i + cards.length) % cards.length;
    const card = cards[index];
    track.scrollTo({ left: card.offsetLeft - track.offsetLeft, behavior: 'smooth' });
    dots.forEach((d, di) => d.classList.toggle('active', di === index));
  }

  prevBtn?.addEventListener('click', () => { goTo(index - 1); restartAutoplay(); });
  nextBtn?.addEventListener('click', () => { goTo(index + 1); restartAutoplay(); });

  function startAutoplay() {
    autoplayTimer = setInterval(() => goTo(index + 1), 6000);
  }
  function restartAutoplay() {
    clearInterval(autoplayTimer);
    startAutoplay();
  }
  track.addEventListener('pointerenter', () => clearInterval(autoplayTimer));
  track.addEventListener('pointerleave', startAutoplay);
  startAutoplay();

  let scrollTimeout = null;
  track.addEventListener('scroll', () => {
    clearTimeout(scrollTimeout);
    scrollTimeout = setTimeout(() => {
      let closest = 0;
      let closestDist = Infinity;
      cards.forEach((card, i) => {
        const dist = Math.abs(card.offsetLeft - track.offsetLeft - track.scrollLeft);
        if (dist < closestDist) { closestDist = dist; closest = i; }
      });
      index = closest;
      dots.forEach((d, di) => d.classList.toggle('active', di === index));
    }, 120);
  });
}

// --- enhance the contact <select> into a branded, accessible listbox ---
document.querySelectorAll('.contact-form select').forEach((select) => {
  const field = select.closest('.field-select');
  const fieldLabel = field?.querySelector('.field-label');
  const opts = Array.from(select.options);
  const placeholder = opts.find((o) => o.disabled) || opts[0] || null;
  const choices = opts.filter((o) => o !== placeholder);
  if (!choices.length) return;

  const uid = 'cs-' + Math.random().toString(36).slice(2, 8);
  const wrapper = document.createElement('div');
  wrapper.className = 'custom-select';

  const trigger = document.createElement('button');
  trigger.type = 'button';
  trigger.id = uid + '-trigger';
  trigger.className = 'select-trigger';
  trigger.setAttribute('aria-haspopup', 'listbox');
  trigger.setAttribute('aria-expanded', 'false');
  trigger.setAttribute('aria-controls', uid + '-list');

  const valueEl = document.createElement('span');
  valueEl.id = uid + '-value';
  valueEl.className = 'select-value is-placeholder';
  valueEl.textContent = placeholder ? placeholder.textContent : 'Select…';
  trigger.setAttribute('aria-labelledby', `${fieldLabel?.id || ''} ${valueEl.id}`.trim());

  const arrow = document.createElement('span');
  arrow.className = 'select-arrow';
  arrow.setAttribute('aria-hidden', 'true');
  trigger.append(valueEl, arrow);

  const list = document.createElement('ul');
  list.id = uid + '-list';
  list.className = 'select-options';
  list.setAttribute('role', 'listbox');
  list.setAttribute('tabindex', '-1');
  if (fieldLabel?.id) list.setAttribute('aria-labelledby', fieldLabel.id);

  const optionEls = choices.map((opt, i) => {
    const li = document.createElement('li');
    li.id = `${uid}-opt-${i}`;
    li.className = 'select-option';
    li.setAttribute('role', 'option');
    li.setAttribute('aria-selected', 'false');
    li.dataset.value = opt.value || opt.textContent;
    li.textContent = opt.textContent;
    return li;
  });
  list.append(...optionEls);

  select.classList.add('is-enhanced');
  select.after(wrapper);
  wrapper.append(trigger, list);

  let open = false;
  let activeIndex = -1;

  const currentIndex = () =>
    choices.findIndex((o) => select.value !== '' && (o.value || o.textContent) === select.value);

  function setActive(i, scroll = true) {
    activeIndex = i;
    optionEls.forEach((el, idx) => el.classList.toggle('is-active', idx === i));
    if (i >= 0 && optionEls[i]) {
      list.setAttribute('aria-activedescendant', optionEls[i].id);
      if (scroll) optionEls[i].scrollIntoView({ block: 'nearest' });
    } else {
      list.removeAttribute('aria-activedescendant');
    }
  }

  function openList() {
    if (open) return;
    open = true;
    wrapper.classList.remove('has-error');
    wrapper.classList.add('is-open');
    trigger.setAttribute('aria-expanded', 'true');
    setActive(Math.max(currentIndex(), 0));
    list.focus();
  }

  function closeList(focusTrigger = true) {
    if (!open) return;
    open = false;
    wrapper.classList.remove('is-open');
    trigger.setAttribute('aria-expanded', 'false');
    if (focusTrigger) trigger.focus();
  }

  function commit(i) {
    const opt = choices[i];
    if (!opt) return;
    select.value = opt.value || opt.textContent;
    select.dispatchEvent(new Event('change', { bubbles: true }));
    closeList();
  }

  function sync() {
    const i = currentIndex();
    optionEls.forEach((el, idx) => {
      el.classList.toggle('is-selected', idx === i);
      el.setAttribute('aria-selected', String(idx === i));
    });
    if (i >= 0) {
      valueEl.textContent = choices[i].textContent;
      valueEl.classList.remove('is-placeholder');
      wrapper.classList.remove('has-error');
    } else {
      valueEl.textContent = placeholder ? placeholder.textContent : 'Select…';
      valueEl.classList.add('is-placeholder');
    }
  }

  trigger.addEventListener('click', () => (open ? closeList() : openList()));
  trigger.addEventListener('keydown', (e) => {
    if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(e.key)) {
      e.preventDefault();
      openList();
    }
  });

  optionEls.forEach((el, i) => {
    el.addEventListener('click', () => commit(i));
    el.addEventListener('mousemove', () => {
      if (activeIndex !== i) setActive(i, false);
    });
  });

  list.addEventListener('keydown', (e) => {
    switch (e.key) {
      case 'ArrowDown':
        e.preventDefault();
        setActive(Math.min(activeIndex + 1, optionEls.length - 1));
        break;
      case 'ArrowUp':
        e.preventDefault();
        setActive(Math.max(activeIndex - 1, 0));
        break;
      case 'Home':
        e.preventDefault();
        setActive(0);
        break;
      case 'End':
        e.preventDefault();
        setActive(optionEls.length - 1);
        break;
      case 'Enter':
      case ' ':
        e.preventDefault();
        commit(activeIndex);
        break;
      case 'Escape':
        e.preventDefault();
        closeList();
        break;
      case 'Tab':
        closeList(false);
        break;
      default:
        if (e.key.length === 1 && /\S/.test(e.key)) {
          const ch = e.key.toLowerCase();
          for (let n = 1; n <= optionEls.length; n++) {
            const idx = (activeIndex + n) % optionEls.length;
            if (choices[idx].textContent.trim().toLowerCase().startsWith(ch)) {
              setActive(idx);
              break;
            }
          }
        }
    }
  });

  list.addEventListener('focusout', (e) => {
    if (!wrapper.contains(e.relatedTarget)) closeList(false);
  });
  document.addEventListener('click', (e) => {
    if (open && !wrapper.contains(e.target)) closeList(false);
  });
  select.addEventListener('change', sync);
  select.form?.addEventListener('reset', () => setTimeout(sync, 0));

  sync();
});

// --- shared fetch-based form submit ---
async function submitForm(form, endpoint, { successField = null } = {}) {
  const status = form.querySelector('.form-status');
  const button = form.querySelector('button[type="submit"]');
  const originalHTML = button.innerHTML;

  status.textContent = '';
  status.classList.remove('is-success', 'is-error');
  button.classList.add('is-loading');
  button.innerHTML = 'Sending <span class="btn-spinner"></span>';
  button.disabled = true;

  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Accept': 'application/json' },
      body: new FormData(form),
    });
    const data = await response.json();

    if (data.success) {
      status.textContent = data.message;
      status.classList.add('is-success');
      button.innerHTML = 'Sent ✓';
      form.reset();
      setTimeout(() => {
        button.innerHTML = originalHTML;
        button.disabled = false;
        button.classList.remove('is-loading');
      }, 2600);
    } else {
      status.textContent = data.message || 'Something went wrong. Please try again.';
      status.classList.add('is-error');
      button.innerHTML = originalHTML;
      button.disabled = false;
      button.classList.remove('is-loading');
    }
  } catch (err) {
    status.textContent = 'Network hiccup — please try again in a moment.';
    status.classList.add('is-error');
    button.innerHTML = originalHTML;
    button.disabled = false;
    button.classList.remove('is-loading');
  }
}

document.querySelector('#contact-form')?.addEventListener('submit', (event) => {
  event.preventDefault();
  const form = event.currentTarget;
  const service = form.querySelector('select[name="service"]');

  if (service && !service.value) {
    const custom = form.querySelector('.custom-select');
    const status = form.querySelector('.form-status');
    status.textContent = 'Let us know what you need help with first.';
    status.classList.remove('is-success');
    status.classList.add('is-error');
    if (custom) {
      custom.classList.add('has-error');
      custom.querySelector('.select-trigger')?.focus();
    } else {
      service.focus();
    }
    return;
  }

  submitForm(form, 'php/contact.php');
});

document.querySelector('#newsletter-form')?.addEventListener('submit', (event) => {
  event.preventDefault();
  submitForm(event.currentTarget, 'php/subscribe.php');
});
